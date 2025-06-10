<?php

namespace Innoboxrr\VideoProcessor\Http\Controllers;

use Innoboxrr\VideoProcessor\Services\VideoService;

class VideoController extends Controller
{

    protected $videoService;
    
    public function __construct(VideoService $videoService)
    {
        $this->middleware('auth:sanctum')->except(['player', 'playlist', 'key']);
        $this->videoService = $videoService;
    }

    public function player($code)
    {
        $this->videoService->authorization();

        $video = $this->videoService->getVideoByCode($code);

        $resourcePath = "https://{$this->videoService->getCloudfrontDomain()}/videos/{$video->uuid}/*";

        $cookies = app(CloudFrontService::class)->generateSignedCookies($resourcePath);

        $response = response()->view('videoprocessor::player', [
            'video' => $video,
        ]);

        foreach ($cookies as $name => $value) {
            $response->withCookie(
                cookie($name, $value, 240, '/', config('videoprocessor.cookie_domain'), true, true, false, 'Strict')
            );
        }

        return $response;
    }


    public function playlist($code, $filename)
    {
        $url = $this->videoService->playerResponse($code, $filename);
        return redirect()->away($url);
    }

    public function key($code, $key)
    {
        return $this->videoService->keyResponse($code, $key);
    }

}
