<?php

namespace Innoboxrr\VideoProcessor\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{

    /**
     * The model observers for your application.
     *
     * @var array
     */
    protected $observers = [
        // Model::class => [ModelObserver::class],
    ];

    protected $listen = [
        'Innoboxrr\VideoProcessor\Events\VideoUploadSuccessful' => [
            'Innoboxrr\VideoProcessor\Listeners\ProcessVideoHLS',
        ],
    ];

    public function boot()
    {

        // $this->registerModelEvents();

    }

    private function registerModelEvents()
    {
        
        $basePath = realpath(__DIR__ . '/../Http/Events');

        $namespace = 'Innoboxrr\VideoProcessor\Http\Events\\';

        $models = glob("{$basePath}/*", GLOB_ONLYDIR);

        foreach ($models as $modelPath) {
        
            $model = basename($modelPath);
        
            $events = glob("{$modelPath}/Events/*.php", GLOB_BRACE);

            foreach ($events as $eventPath) {
        
                $eventName = pathinfo($eventPath, PATHINFO_FILENAME);
        
                $listeners = glob("{$modelPath}/Listeners/{$eventName}/*.php", GLOB_BRACE);

                foreach ($listeners as $listenerPath) {
        
                    $listenerName = pathinfo($listenerPath, PATHINFO_FILENAME);
        
                    $event = "{$namespace}{$model}\\Events\\{$eventName}";
        
                    $listener = "{$namespace}{$model}\\Listeners\\{$eventName}\\{$listenerName}";

                    Event::listen($event, $listener);

                }

            }

        }

    }


}
