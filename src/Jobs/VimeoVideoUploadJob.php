<?php

namespace Innoboxrr\VideoProcessor\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class VimeoVideoUploadJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $videoId;

    public $uniqueFor = 3600;

    public function __construct($videoId)
    {
        $this->videoId = $videoId;
        $this->onQueue('video_processor');
    }

    public function uniqueId(): string
    {
        return 'vimeo-video-upload-' . $this->videoId;
    }

    public function handle()
    {
        // Llamada al comando
        Artisan::call('vimeo:upload', [
            'videoId' => $this->videoId
        ]);
    }
}
