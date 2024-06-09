<?php

namespace Innoboxrr\VideoProcessor\Http\Controllers;

use Innoboxrr\VideoProcessor\Http\Requests\Vtt\{
    GetOriginalVttRequest,
    AutoGenerateOriginalVttRequest,
    UploadOriginalVttRequest,
    DeleteOriginalVttRequest,
    GetTranslatedVttRequest,
    AutoGenerateTranslatedVttRequest,
    UploadTranslatedVttRequest,
    DeleteTranslatedVttRequest
};

class VttController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Original Vtt files

    public function getOriginalVtt(GetOriginalVttRequest $request)
    {
        return $request->handle();
    }

    public function autoGenerateOriginalVtt(AutoGenerateOriginalVttRequest $request)
    {
        return $request->handle();
    }

    public function uploadOriginalVtt(UploadOriginalVttRequest $request)
    {
        return $request->handle();
    }

    public function deleteOriginalVtt(DeleteOriginalVttRequest $request)
    {
        return $request->handle();
    }

    // Translated Vtt files

    public function getTranslatedVtt(GetTranslatedVttRequest $request)
    {
        return $request->handle();
    }

    public function autoGenerateTranslatedVtt(AutoGenerateTranslatedVttRequest $request)
    {
        return $request->handle();
    }

    public function uploadTranslatedVtt(UploadTranslatedVttRequest $request)
    {
        return $request->handle();
    }

    public function deleteTranslatedVtt(DeleteTranslatedVttRequest $request)
    {
        return $request->handle();
    }

}