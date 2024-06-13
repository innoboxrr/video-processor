<?php

namespace Innoboxrr\VideoProcessor\Jobs;

use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Innoboxrr\VideoProcessor\Services\VideoService;

class ProcessVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $video;

    public function __construct($videoId)
    {
        $this->video = Video::findOrFail($videoId);
    }

    public function handle(VideoService $videoService)
    {

        $this->video->update([
            'cloud' => 'aws',
            'status' => 'queue_for_processing',
        ]);

        $videoService->processVideo($this->video->id);
    }
}
