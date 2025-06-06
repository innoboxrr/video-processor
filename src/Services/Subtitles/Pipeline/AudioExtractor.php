<?php

namespace Innoboxrr\VideoProcessor\Services\Subtitles\Pipeline;

use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;

class AudioExtractor
{
    public static function extract(string $videoPath, string $audioPath): void
    {
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => config('videoprocessor.ffmpeg_path'),
            'ffprobe.binaries' => config('videoprocessor.ffprobe_path'),
            'timeout'          => 3600,
            'ffmpeg.threads'   => config('videoprocessor.ffmpeg_threads'),
        ]);

        $video = $ffmpeg->open($videoPath);
        $video->save(new Mp3(), $audioPath);
    }
}
