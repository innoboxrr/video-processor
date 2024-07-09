<?php

namespace Innoboxrr\VideoProcessor\Jobs;

use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Innoboxrr\VideoProcessor\Services\VideoService;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ProcessVideoJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $videoId;

    public $uniqueFor = 3600;

    public $queue = 'video_processor';

    public function __construct($videoId)
    {
        $this->videoId = $videoId;
    }

    public function uniqueId(): string
    {
        return $this->videoId;
    }

    public function handle(VideoService $videoService)
    {
        $video = Video::findOrFail($this->videoId);

        $video->update([
            'cloud' => 'aws',
            'status' => 'queue_for_processing',
            'progress' => 0,
        ]);

        $videoService->processVideo($video->id);
    }
}
