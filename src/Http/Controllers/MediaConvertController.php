<?php

namespace Innoboxrr\VideoProcessor\Http\Controllers;

use Innoboxrr\VideoProcessor\Http\Requests\MediaConvert\{
    CallbackRequest
};

class MediaConvertController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function callback(CallbackRequest $request)
    {
        return $request->handle();
    }
}