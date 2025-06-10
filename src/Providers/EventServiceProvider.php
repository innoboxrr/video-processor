<?php

namespace Innoboxrr\VideoProcessor\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        'Innoboxrr\VideoProcessor\Events\VideoUploadSuccessful' => [
            'Innoboxrr\VideoProcessor\Listeners\ProcessVideoHLS',
        ],
    ];
}
