<?php

namespace Innoboxrr\VideoProcessor\Services;

use Innoboxrr\VideoProcessor\Contracts\Abstracts\AbstractVideoService;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use FFMpeg\Format\Video\X264;
use Innoboxrr\VideoProcessor\Support\Classes\GenerateSubtitles as GenerateSubtitlesClass;

class VideoService extends AbstractVideoService
{

    public function processVideo($videoId)
    {   
        $video = Video::findOrFail($videoId);

        // Eliminar directorios en S3 (no importa si no existen)
        Storage::disk('s3')->deleteDirectory($video->s3_hls_path);
        Storage::disk('s3')->deleteDirectory($video->s3_keys_path);

        $video->update([
            'status' => 'processing_started',
        ]);

        $formats = config('videoprocessor.formats');
        $enabledFormats = config('videoprocessor.enabled_formats');

        // Exporta el video a HLS.
        $conv = FFMpeg::fromDisk('s3')
            ->open($video->s3_original_path)
            ->exportForHLS()
            ->onProgress(function ($percentage) use ($video) {
                DB::table('videos')->where('id', $video->id)->update([
                    'progress' => $percentage,
                    'status' => $percentage == 100 ? 'processing_completed' : 'processing'
                ]);
            })
            ->setSegmentLength(10) 
            ->setKeyFrameInterval(48) 
            ->withRotatingEncryptionKey(function ($filename, $contents) use ($video) {
                Storage::disk('s3')->put($video->s3_keys_path . DIRECTORY_SEPARATOR . $filename, $contents);
            });

        // Añadir los formatos habilitados
        foreach ($enabledFormats as $format) {
            if (isset($formats[$format])) {
                $bitrate = (new X264)->setKiloBitrate($formats[$format]['bitrate']);
                $scale = $formats[$format]['scale'];
                $conv->addFormat($bitrate, function($media) use ($scale) {
                    $media->scale($scale[0], $scale[1]);
                });
            }
        }

        $conv->save($video->s3_hls_master);

        FFMpeg::cleanupTemporaryFiles();

        DB::table('videos')->where('id', $video->id)->update([
            'status' => 'available_for_viewing'
        ]);

        return 0;
    }

    public function generateSubtitles($videoId)
    {
        $video = Video::findOrFail($videoId);
        $subtitlesGenerator = new GenerateSubtitlesClass($video);
        $subtitlesGenerator->generate();
    }

    public function translateSubtitles($videoId, $sourceLanguage, $targetLanguage)
    {
        $video = Video::findOrFail($videoId);
        $subtitlesGenerator = new GenerateSubtitlesClass($video);
        $subtitlesGenerator->translate($sourceLanguage, $targetLanguage);
    }

    public function playerResponse($code, $filename) 
    {
        $video = $this->getVideoByCode($code);

        $path = $video->s3_hls_path . '/' . $filename;

        // Si el archivo no existe, devolver 404
        if (!Storage::disk('s3')->exists($path)) {
            abort(404);
        }

        return FFMpeg::dynamicHLSPlaylist()
            ->fromDisk('s3')
            ->open($path)
            ->setKeyUrlResolver(function ($key) use ($video) {
                return route('videoprocessor.key', [
                    'code' => $video->code,
                    'key' => $key,
                    'guest_token' => request()->guest_token
                ]);
            })
            ->setMediaUrlResolver(function ($mediaFilename) use ($video) {
                $path = $video->s3_hls_path . '/' . $mediaFilename;
                return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(5));
            })
            ->setPlaylistUrlResolver(function ($playlistFilename) use ($video) {
                return route('videoprocessor.playlist', [
                    'code' => $video->code,
                    'filename' => $playlistFilename,
                    'guest_token' => request()->guest_token
                ]);
            });
    }

    public function keyResponse($code, $key) 
    {
        $video = $this->getVideoByCode($code);
        $path = $video->s3_keys_path . '/' . $key;
        return Storage::disk('s3')->download($path);
    }

}
