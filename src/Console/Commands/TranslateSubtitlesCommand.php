<?php

namespace Innoboxrr\VideoProcessor\Console\Commands;

use Illuminate\Console\Command;
use Innoboxrr\VideoProcessor\Jobs\TranslateSubtitlesJob;

class TranslateSubtitlesCommand extends Command
{
    protected $signature = 'video:translate-subtitles {videoId} {sourceLanguage=en} {targetLanguage=es}';

    protected $description = 'Genera y traduce los subtítulos de un video';

    public function handle()
    {
        TranslateSubtitlesJob::dispatchSync(
            $this->argument('videoId'),
            $this->argument('sourceLanguage'),
            $this->argument('targetLanguage')
        );
        $this->info('Subtitulos generados correctamente');
    }
}