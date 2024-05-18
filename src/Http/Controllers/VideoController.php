<?php

namespace Innoboxrr\VideoProcessor\Http\Controllers;

use App\Models\Video;
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
        return view('videoprocessor::player', [
            'video' => $this->videoService->getVideoByCode($code)
        ]);
    }

    public function playlist($code, $filename)
    {
        return $this->videoService->playerResponse($code, $filename);
    }

    public function key($code, $key)
    {
        return $this->videoService->keyResponse($code, $key);
    }

}
