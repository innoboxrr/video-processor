<?php

namespace Innoboxrr\VideoProcessor\Console\Commands;

use Illuminate\Console\Command;
use Innoboxrr\VideoProcessor\Jobs\GenerateSubtitlesJob;

class GenerateSubtitlesCommand extends Command
{
    protected $signature = 'video:generate-subtitles {videoId}';
    
    protected $description = 'Genera los subtitulos de un video';

    public function handle()
    {
        GenerateSubtitlesJob::dispatchSync($this->argument('videoId'));
        $this->info('Subtitulos generados correctamente');
    }

}
