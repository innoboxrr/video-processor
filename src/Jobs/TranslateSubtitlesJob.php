<?php

namespace Innoboxrr\VideoProcessor\Jobs;

use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class TranslateSubtitlesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $video;

    public function __construct($videoId)
    {
        $this->video = Video::findOrFail($videoId);
    }

    public function handle()
    {
        Artisan::call('video:translate-subtitles', [
            'videoId' => $this->video->id,
            'sourceLanguage' => 'en',
            'targetLanguage' => 'es',
        ]);
    }
}
