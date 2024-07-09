<?php

namespace Innoboxrr\VideoProcessor\Jobs;

use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class GenerateSubtitlesJob implements ShouldQueue, ShouldBeUnique
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
        return 'generate-subtitles-' . $this->videoId;
    }

    public function handle()
    {
        $video = Video::findOrFail($this->videoId);

        Artisan::call('video:generate-subtitles', [
            'videoId' => $video->id
        ]);
    }
}
