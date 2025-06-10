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
        $videoId = $event->videoId;
        ProcessVideoJob::dispatch($videoId);
    }
}
