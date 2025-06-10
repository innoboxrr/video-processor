<?php

namespace Innoboxrr\VideoProcessor\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Innoboxrr\VideoProcessor\Services\VideoService;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ProcessVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $videoId;

    public function __construct($videoId)
    {
        $this->videoId = $videoId;
    }

    public function uniqueId(): string
    {
        return 'process-video-' . $this->videoId;
    }

    public function handle(VideoService $videoService)
    {
        $video = app(config('videoprocessor.video_class'))::findOrFail($this->videoId);
        $video->update([
            'cloud' => 'aws',
            'status' => 'queue_for_processing',
            'progress' => 0,
        ]);
        $videoService->processVideo($video);
    }
}
