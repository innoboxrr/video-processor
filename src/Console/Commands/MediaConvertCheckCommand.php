<?php

namespace Innoboxrr\VideoProcessor\Console\Commands;

use Illuminate\Console\Command;
use Innoboxrr\VideoProcessor\Jobs\MediaConvertCheckJob;

class MediaConvertCheckCommand extends Command
{
    protected $signature = 'video:media-convert-check';

    protected $description = 'Check MediaConvert jobs and process them if necessary';

    public function handle()
    {
        MediaConvertCheckJob::dispatchSync();
    }
}