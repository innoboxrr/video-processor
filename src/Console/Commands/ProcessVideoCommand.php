<?php

namespace Innoboxrr\VideoProcessor\Console\Commands;

use Illuminate\Console\Command;
use Innoboxrr\VideoProcessor\Services\VideoService;

class ProcessVideoCommand extends Command
{
    
    protected $signature = 'video:process {videoId}';
    protected $description = 'Procesa un video utilizando VideoService';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(VideoService $videoService)
    {
        $videoId = $this->argument('videoId');

        $result = $videoService->processVideo($videoId);

        $this->info('Resultado del procesamiento del video: ');
    }

}
