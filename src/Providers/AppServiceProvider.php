<?php

namespace Innoboxrr\VideoProcessor\Providers;

use Illuminate\Support\ServiceProvider;
use Innoboxrr\VideoProcessor\Services\VideoService;
use Innoboxrr\VideoProcessor\Console\Commands\{
    ProcessVideoCommand,
    GenerateSubtitlesCommand,
    TranslateSubtitlesCommand,
    MediaConvertCheckCommand
};

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/videoprocessor.php', 'videoprocessor');
        $this->app->singleton(VideoService::class, function ($app) {
            return new VideoService();
        });
    }

    public function boot()
    {
        // $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'videoprocessor');
        if ($this->app->runningInConsole()) {
            // $this->publishes([__DIR__.'/../../resources/views' => resource_path('views/vendor/videoprocessor'),], 'views');
            $this->publishes([__DIR__.'/../../config/videoprocessor.php' => config_path('videoprocessor.php')], 'config');
        }

        $this->commands([
            ProcessVideoCommand::class,
            GenerateSubtitlesCommand::class,
            TranslateSubtitlesCommand::class,
            MediaConvertCheckCommand::class,
        ]);
    }   
}