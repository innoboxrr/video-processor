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
        // Accede al ID del video desde el evento
        $videoId = $event->videoId;

        // Llamada al comando
        ProcessVideoJob::dispatch($videoId);
    }
}
