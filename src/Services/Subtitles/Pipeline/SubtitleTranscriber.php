<?php

namespace Innoboxrr\VideoProcessor\Services\Subtitles\Pipeline;

use Illuminate\Support\Facades\Http;

class SubtitleTranscriber
{
    public static function transcribe(string $audioPath, string $vttPath): void
    {
        $apiKey = config('services.openai.api_key');

        $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])
            ->timeout(600)
            ->attach('file', file_get_contents($audioPath), 'audio.mp3')
            ->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => 'whisper-1',
                'response_format' => 'vtt',
                'prompt' => 'Transcribe the following audio file',
            ]);

        if (!$response->successful()) {
            throw new \Exception('Error al generar subtítulos: ' . $response->body());
        }

        file_put_contents($vttPath, $response->body());
    }
}
