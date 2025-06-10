<?php

namespace Innoboxrr\VideoProcessor\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Innoboxrr\VideoProcessor\Services\VideoService;
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
    }

    public function uniqueId(): string
    {
        return 'translate-subtitles-' . $this->videoId . '-' . $this->sourceLanguage . '-' . $this->targetLanguage;
    }

    public function handle(VideoService $videoService)
    {
        $video = app(config('videoprocessor.video_class'))::findOrFail($this->videoId);
        $videoService->translateSubtitles(
            $video,
            $this->sourceLanguage, 
            $this->targetLanguage
        );
    }
}
