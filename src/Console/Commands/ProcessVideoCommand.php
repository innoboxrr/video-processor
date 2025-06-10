<?php

namespace Innoboxrr\VideoProcessor\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Video;
use Innoboxrr\VideoProcessor\Jobs\ProcessVideoJob;

class ProcessVideoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:process-video-job {videoId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Recuperar el video por su id
        $video = Video::findOrFail($this->argument('videoId'));

        // Despachar el trabajo para procesar el video
        ProcessVideoJob::dispatch($video->id);
    }
}

