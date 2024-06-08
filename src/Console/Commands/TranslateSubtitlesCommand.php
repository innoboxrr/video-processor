<?php

namespace Innoboxrr\VideoProcessor\Console\Commands;

use Illuminate\Console\Command;
use Innoboxrr\VideoProcessor\Services\VideoService;

class TranslateSubtitlesCommand extends Command
{
    
    protected $signature = 'video:translate-subtitles {videoId} {sourceLanguage=en} {targetLanguage=es}';
    protected $description = 'Genera y traduce los subtítulos de un video';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(VideoService $videoService)
    {
        $videoId = $this->argument('videoId');
        $sourceLanguage = $this->argument('sourceLanguage');
        $targetLanguage = $this->argument('targetLanguage');

        $videoService->translateSubtitles($videoId, $sourceLanguage, $targetLanguage);

        $this->info('Subtitulos generados correctamente');
    }

}
