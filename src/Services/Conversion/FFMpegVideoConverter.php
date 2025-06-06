<?php

namespace Innoboxrr\VideoProcessor\Services\Conversion;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use FFMpeg\Format\Video\X264;

class FFMpegVideoConverter
{
    public function process(object $video): void
    {
        // Elimina directorios previos (si existen)
        Storage::disk('s3')->deleteDirectory($video->s3_hls_path);
        Storage::disk('s3')->deleteDirectory($video->s3_keys_path);

        $video->update([
            'status' => 'processing_started',
            'progress' => 0,
        ]);

        $formats = config('videoprocessor.formats');
        $enabledFormats = config('videoprocessor.enabled_formats');

        $exporter = FFMpeg::fromDisk('s3')
            ->open($video->s3_original_path)
            ->exportForHLS()
            ->onProgress(function ($percentage) use ($video) {
                DB::table('videos')->where('id', $video->id)->update([
                    'progress' => $percentage,
                    'status' => $percentage == 100 ? 'processing_completed' : 'processing',
                ]);
            })
            ->setSegmentLength(10)
            ->setKeyFrameInterval(48)
            ->withRotatingEncryptionKey(function ($filename, $contents) use ($video) {
                Storage::disk('s3')->put(
                    $video->s3_keys_path . DIRECTORY_SEPARATOR . $filename,
                    $contents
                );
            });

        foreach ($enabledFormats as $format) {
            if (isset($formats[$format])) {
                $bitrate = (new X264)->setKiloBitrate($formats[$format]['bitrate']);
                $scale = $formats[$format]['scale'];
                $exporter->addFormat($bitrate, function ($media) use ($scale) {
                    $media->scale($scale[0], $scale[1], 'keep_aspect_ratio');
                });
            }
        }

        $exporter->save($video->s3_hls_master);

        FFMpeg::cleanupTemporaryFiles();

        DB::table('videos')->where('id', $video->id)->update([
            'status' => 'available_for_viewing',
        ]);
    }

    public function playback(object $video, string $filename)
    {
        $path = $video->s3_hls_path . '/' . $filename;

        return FFMpeg::dynamicHLSPlaylist()
            ->fromDisk('s3')
            ->open($path)
            ->setKeyUrlResolver(fn($key) => route('videoprocessor.key', [
                'code' => $video->code,
                'key' => $key,
                'guest_token' => request()->guest_token,
            ]))
            ->setMediaUrlResolver(fn($mediaFilename) => Storage::disk('s3')
                ->temporaryUrl($video->s3_hls_path . '/' . $mediaFilename, now()->addMinutes(240)))
            ->setPlaylistUrlResolver(fn($playlistFilename) => route('videoprocessor.playlist', [
                'code' => $video->code,
                'filename' => $playlistFilename,
                'guest_token' => request()->guest_token,
            ]));
    }
}
