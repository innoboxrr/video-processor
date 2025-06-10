<?php

namespace Innoboxrr\VideoProcessor\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;

class MediaConvertCallback implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    public array $message;

    public function __construct(array $message)
    {
        $this->message = $message;

    }

    public function handle(): void
    {
        $status = $this->message['status'] ?? null;
        $jobId = $this->message['jobId'] ?? null;
        $metadata = $this->message['userMetadata'] ?? [];

        $videoId = $this->message['userMetadata']['VideoId'] ?? null;
        if (!$videoId) {
            return;
        }
        
        $video = app(config('videoprocessor.video_class'))::find($videoId);

        if ($status === 'COMPLETE') {
            $video->update([
                'status' => 'available_for_viewing',
                'progress' => 100,
            ]);
        } elseif ($status === 'ERROR') {
            $video->update([
                'status' => 'processing_error',
            ]);
        }

    }
}
