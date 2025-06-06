<?php

namespace Innoboxrr\VideoProcessor\Services\Subtitles\Pipeline;

use Illuminate\Support\Facades\Storage;

class VideoDownloader
{
    public static function download(object $video, string $destination): void
    {
        $content = Storage::disk('s3')->get($video->s3_original_path);
        file_put_contents($destination, $content);
    }
}
