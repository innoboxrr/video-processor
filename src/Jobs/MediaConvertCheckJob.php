<?php

namespace Innoboxrr\VideoProcessor\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Innoboxrr\VideoProcessor\Services\VideoService;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class MediaConvertCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function __construct()
    {
        // 
    }

    public function handle(VideoService $videoService)
    {
        $videoService->mediaConvertCheck();
    }
}
