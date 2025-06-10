<?php

namespace Innoboxrr\VideoProcessor\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Innoboxrr\VideoProcessor\Services\VideoService;

class GenerateSubtitlesJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $videoId;

    public $uniqueFor = 3600;

    public function __construct($videoId)
    {
        $this->videoId = $videoId;
    }

    public function uniqueId(): string
    {
        return 'generate-subtitles-' . $this->videoId;
    }

    public function handle(VideoService $videoService)
    {
        $video = app(config('videoprocessor.video_class'))::findOrFail($this->videoId);
        $videoService->generateSubtitles($video);
    }
}
