<?php

namespace Innoboxrr\VideoProcessor\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{

    public function map()
    {

        $this->mapRoutes();      

    }

    protected function mapRoutes()
    {

        Route::middleware('api')
            ->prefix('video')
            ->as('videoprocessor.')
            ->middleware('web')
            ->namespace('Innoboxrr\VideoProcessor\Http\Controllers')
            ->group(__DIR__ . '/../../routes/web.php');

    }

}
