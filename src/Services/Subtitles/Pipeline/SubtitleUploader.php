<?php

namespace Innoboxrr\VideoProcessor\Services\Subtitles\Pipeline;

use Illuminate\Support\Facades\Storage;

class SubtitleUploader
{
    public static function upload(object $video, string $vttPath): void
    {
        $languageCode = $video->language->code;

        Storage::disk('s3')->delete($video->s3_original_vtt_path);
        Storage::disk('s3')->delete($video->s3_vtts_path . "/{$languageCode}.vtt");

        $content = file_get_contents($vttPath);

        Storage::disk('s3')->put($video->s3_original_vtt_path, $content);
        Storage::disk('s3')->put($video->s3_vtts_path . "/{$languageCode}.vtt", $content);

        $video->subtitles()->firstOrCreate([
            'language_id' => $video->language->id,
            'type' => 'auto',
        ]);
    }
}
