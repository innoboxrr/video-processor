<?php

namespace Innoboxrr\VideoProcessor\Services;

use Innoboxrr\VideoProcessor\Contracts\Abstracts\AbstractVideoService;
use Innoboxrr\VideoProcessor\Services\Conversion\MediaConvertVideoProcessor;
use Innoboxrr\VideoProcessor\Services\Delivery\CloudFrontService;
use Innoboxrr\VideoProcessor\Services\Subtitles\SubtitleService;
use Innoboxrr\VideoProcessor\Support\Helpers\VideoHelper;
use Illuminate\Support\Str;

class VideoService extends AbstractVideoService
{
    public function processVideo(object $video)
    {
        app(MediaConvertVideoProcessor::class)->process($video);
        return;
    }

    public function generateSubtitles($video)
    {
        app(SubtitleService::class)->generate($video);
    }

    public function translateSubtitles(object $video, $sourceLanguage, $targetLanguage)
    {
        app(SubtitleService::class)->translate($video, $sourceLanguage, $targetLanguage);
    }

    public function playerResponse($code, $filename = 'index.m3u8')
    {
        $video = $this->getVideoByCode($code);

        if (Str::endsWith($filename, '.m3u8')) {
            return app(CloudFrontService::class)->processAndSignPlaylist($video->s3_hls_path, $filename, $code);
        }

        $url = app(CloudFrontService::class)->playback($video->s3_hls_path, $filename);
        return redirect()->away($url);
    }


    public function keyResponse($code)
    {
        return response(hex2bin(VideoHelper::getEncryptionKey($code)), 200, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }
}
