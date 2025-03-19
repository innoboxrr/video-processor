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

class TranslateSubtitlesJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $videoId;

    public $uniqueFor = 3600;

    protected $sourceLanguage;
    
    protected $targetLanguage;

    public function __construct($videoId, $sourceLanguage, $targetLanguage)
    {
        $this->videoId = $videoId;
        $this->sourceLanguage = $sourceLanguage;
        $this->targetLanguage = $targetLanguage;
        $this->onQueue('video_processor');
    }

    public function uniqueId(): string
    {
        return 'translate-subtitles-' . $this->videoId . '-' . $this->sourceLanguage . '-' . $this->targetLanguage;
    }

    public function handle()
    {
        $video = Video::findOrFail($this->videoId);

        Artisan::call('video:translate-subtitles', [
            'videoId' => $video->id,
            'sourceLanguage' => $this->sourceLanguage,
            'targetLanguage' => $this->targetLanguage,
        ]);
    }
}
