<?php

namespace Innoboxrr\VideoProcessor\Support\Classes;

use App\Models\Video;
use Illuminate\Support\Facades\Storage;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;


class GenerateSubtitles
{   
    protected Video $video;

    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    public function generate()
    {
        set_time_limit(600);

        $tempDir = storage_path('app/temp/subtitles') . '/' . $this->video->id;
        $tempVideoPath = $tempDir . '/video.mp4';
        $tempAudioPath = $tempDir . '/audio.mp3';
        $vttPath = $tempDir . '/subtitles.vtt';

        // Crear directorios si no existen
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        // Descargar el video desde S3 a una carpeta temporal
        $videoContent = Storage::disk('s3')->get($this->video->s3_original_path);
        file_put_contents($tempVideoPath, $videoContent);

        // Extraer el audio del video
        // Inicializar FFMpeg
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => config('videoprocessor.ffmpeg_path'), // Ruta del binario FFmpeg (opcional
            'ffprobe.binaries' => config('videoprocessor.ffprobe_path'), // Ruta del binario FFprobe (opcional)
            'timeout'          => 3600, // Opcional, tiempo máximo en segundos para ejecutar FFmpeg
            'ffmpeg.threads'   => config('videoprocessor.ffmpeg_threads'), // Opcional, número de hilos para FFmpeg
        ]);

        // Cargar el video
        $video = $ffmpeg->open($tempVideoPath);

        // Extraer el audio y guardarlo como MP3
        $audioFormat = new Mp3();
        $video->save($audioFormat, $tempAudioPath);

        // Llamar a la API de OpenAI para generar subtítulos
        $apiKey = config('services.openai.api_key');
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])
        ->timeout(600)
        ->attach( 'file', file_get_contents($tempAudioPath), 'audio.mp3' )
        ->post('https://api.openai.com/v1/audio/transcriptions', [
            'model' => 'whisper-1',
            'response_format' => 'vtt',
            'prompt' => 'Transcribe the following audio file',
        ]);

        if ($response->successful()) {
            // Guardar el archivo VTT generado
            file_put_contents($vttPath, $response->body());

            // Elimianr archivos actuales
            Storage::disk('s3')->delete($this->video->s3_original_vtt_path);
            Storage::disk('s3')->delete($this->video->s3_vtts_path . '/' . $this->video->language->code . '.vtt');

            // Subir el archivo VTT a S3
            Storage::disk('s3')->put($this->video->s3_original_vtt_path, file_get_contents($vttPath));
            Storage::disk('s3')->put($this->video->s3_vtts_path . '/' . $this->video->language->code . '.vtt' , file_get_contents($vttPath));

            // Registrar subt´pitulo
            $this->video->subtitles()->firstOrCreate([
                'language_id' => $this->video->language->id,
                'type' => 'auto',
            ]);

        } else {
            throw new \Exception('Error al generar subtítulos: ' . $response->body());
        }

        // Verificar que el archivo VTT ha sido creado
        if (!file_exists($vttPath)) {
            throw new \Exception('El archivo de subtítulos no se generó correctamente.');
        }

        // Eliminar archivos temporales
        unlink($tempVideoPath);
        unlink($tempAudioPath);
        unlink($vttPath);
        rmdir($tempDir);
    }

    public function translate($sourceLanguage, $targetLanguage)
    {
        if (!Storage::disk('s3')->exists($this->video->s3_original_vtt_path)) {
            $this->generate();
        }

        $vttContent = Storage::disk('s3')->get($this->video->s3_original_vtt_path);
        $vttLines = explode("\n", $vttContent);
        $translatedLines = [];
        $apiKey = config('services.google_translate.api_key');
        $client = new Client();

        foreach ($vttLines as $line) {
            if ($this->isSubtitleText($line)) {
                $response = $client->post('https://translation.googleapis.com/language/translate/v2', [
                    'query' => [
                        'key' => $apiKey,
                        'q' => $line,
                        'source' => $sourceLanguage,
                        'target' => $targetLanguage,
                        'format' => 'text',
                    ],
                ]);

                $responseData = json_decode($response->getBody(), true);

                if (isset($responseData['data']['translations'][0]['translatedText'])) {
                    $translatedLines[] = $responseData['data']['translations'][0]['translatedText'];
                } else {
                    throw new \Exception('Error al traducir subtítulos: ' . json_encode($responseData));
                }
            } else {
                $translatedLines[] = $line;
            }
        }

        $translatedVttContent = implode("\n", $translatedLines);
        Storage::disk('s3')->put($this->video->s3_vtts_path . '/' . $targetLanguage . '.vtt', $translatedVttContent);
    }

    private function isSubtitleText($line)
    {
        // Determina si una línea es texto de subtítulo (excluyendo las líneas de tiempo y encabezados)
        // Puedes ajustar esta lógica según el formato específico de tu VTT
        return !empty($line) && !preg_match('/^\d{2}:\d{2}:\d{2}\.\d{3} --> \d{2}:\d{2}:\d{2}\.\d{3}$/', $line) && !preg_match('/^WEBVTT$/', $line);
    }
}
