<?php

namespace Innoboxrr\VideoProcessor\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Video;
use Innoboxrr\VideoProcessor\Jobs\ProcessVideoJob;

class ProcessVideoCommand extends Command
{
    protected $signature = 'video:process {videoId}
                            {--random= : Optional random string to ensure uniqueness}';

    protected $description = 'Commd description';

    public function handle()
    {
        ProcessVideoJob::dispatch($this->argument('videoId'), $this->option('random'))
            ->onQueue('video_processor');
    }
}