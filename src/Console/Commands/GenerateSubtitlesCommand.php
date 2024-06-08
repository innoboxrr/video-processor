<?php

namespace Innoboxrr\VideoProcessor\Console\Commands;

use Illuminate\Console\Command;
use Innoboxrr\VideoProcessor\Services\VideoService;

class GenerateSubtitlesCommand extends Command
{
    
    protected $signature = 'video:generate-subtitles {videoId}';
    protected $description = 'Genera los subtitulos de un video';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(VideoService $videoService)
    {
        $videoId = $this->argument('videoId');

        $videoService->generateSubtitles($videoId);

        $this->info('Subtitulos generados correctamente');
    }

}
