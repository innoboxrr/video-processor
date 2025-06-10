<?php

namespace Innoboxrr\VideoProcessor\Listeners;

use Innoboxrr\VideoProcessor\Jobs\ProcessVideoJob;
use Innoboxrr\VideoProcessor\Events\VideoUploadSuccessful;

class ProcessVideoHLS
{
    /**
     * Handle the event.
     *
     * @param  VideoUploadSuccessful  $event
     * @return void
     */
    public function handle(VideoUploadSuccessful $event)
    {
        ProcessVideoJob::dispatch($event->videoId)
            ->onQueue(config('video-processor.queue', 'default'));
    }
}
