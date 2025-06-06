<?php

namespace Innoboxrr\VideoProcessor\Services\Subtitles\Pipeline;

class TempFileManager
{
    protected string $basePath;

    protected function __construct(protected object $video)
    {
        $this->basePath = storage_path("app/temp/subtitles/{$this->video->id}");
    }

    public static function create(object $video): object
    {
        $instance = new self($video);
        return (object) [
            'dir'   => $instance->basePath,
            'video' => "{$instance->basePath}/video.mp4",
            'audio' => "{$instance->basePath}/audio.mp3",
            'vtt'   => "{$instance->basePath}/subtitles.vtt",
        ];
    }

    public static function cleanup(object $paths): void
    {
        foreach (['video', 'audio', 'vtt'] as $key) {
            if (!empty($paths->$key) && file_exists($paths->$key)) {
                @unlink($paths->$key);
            }
        }

        if (!empty($paths->dir) && is_dir($paths->dir)) {
            @rmdir($paths->dir);
        }
    }
}
