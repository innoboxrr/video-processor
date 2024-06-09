<?php

namespace Innoboxrr\VideoProcessor\Http\Requests\Vtt;

use Innoboxrr\VideoProcessor\Services\VideoService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class GetOriginalVttRequest extends FormRequest
{

    protected $videoService;

    public function __construct(VideoService $videoService)
    {
        $this->videoService = $videoService;
    }

    protected function prepareForValidation()
    {
        //
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            //
        ];
    }

    public function messages()
    {
        return [
            //
        ];
    }

    public function attributes()
    {
        return [
            //
        ];
    }

    protected function passedValidation()
    {
        //
    }

    public function handle()
    {
        $video = $this->videoService->getVideoByCode($this->code);
        $path = $video->s3_original_vtt_path;
        $mimeType = Storage::disk('s3')->mimeType($path);
        $stream = Storage::disk('s3')->readStream($path);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'max-age=26280000',
        ]);
    }
}
