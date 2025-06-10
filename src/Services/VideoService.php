<?php

namespace Innoboxrr\VideoProcessor\Services;

use Innoboxrr\VideoProcessor\Contracts\Abstracts\AbstractVideoService;
use Innoboxrr\VideoProcessor\Services\Conversion\FFMpegVideoConverter;
use Innoboxrr\VideoProcessor\Services\Conversion\MediaConvertVideoProcessor;
use Innoboxrr\VideoProcessor\Services\Delivery\CloudFrontService;
use Innoboxrr\VideoProcessor\Services\Subtitles\SubtitleService;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class VideoService extends AbstractVideoService
{
    public function processVideo($videoId)
    {
        $video = $this->getVideoById($videoId);

        if (config('videoprocessor.process_with_mediaconvert')) {
            app(MediaConvertVideoProcessor::class)->process($video);
            return;
        }
        // app(FFMpegVideoConverter::class)->process($video);
    }

    public function generateSubtitles($videoId)
    {
        $video = $this->getVideoByCode($videoId);
        app(SubtitleService::class)->generate($video);
    }

    public function translateSubtitles($videoId, $sourceLanguage, $targetLanguage)
    {
        $video = $this->getVideoByCode($videoId);
        app(SubtitleService::class)->translate($video, $sourceLanguage, $targetLanguage);
    }

    public function playerResponse($code, $filename = 'index.m3u8')
    {
        $video = $this->getVideoByCode($code);

        if ($video->status !== 'available_for_viewing') {
            abort(404);
        }

        return $this->resolvePlaybackMethod($video, $filename);
    }

    protected function resolvePlaybackMethod(object $video, string $filename)
    {
        if (config('videoprocessor.process_with_mediaconvert')) {
            return app(CloudFrontService::class)->playback($video, $filename);
        }
        //return app(FFMpegVideoConverter::class)->playback($video, $filename);
    }

    public function keyResponse($code, $key)
    {
        $video = $this->getVideoByCode($code);

        if ($video->status !== 'available_for_viewing') {
            abort(404);
        }

        $path = $video->s3_keys_path . '/' . $key;

        return Storage::disk('s3')->download($path);
    }
}
