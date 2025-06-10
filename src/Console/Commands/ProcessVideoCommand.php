<?php

namespace Innoboxrr\VideoProcessor\Console\Commands;

use Illuminate\Console\Command;
use Innoboxrr\VideoProcessor\Jobs\ProcessVideoJob;

class ProcessVideoCommand extends Command
{
    protected $signature = 'video:process {videoId}';

    protected $description = 'Commd description';

    public function handle()
    {
        ProcessVideoJob::dispatchSync($this->argument('videoId'));
    }
}