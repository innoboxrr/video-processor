<?php

namespace Innoboxrr\VideoProcessor\Http\Requests\Vtt;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Innoboxrr\VideoProcessor\Services\VideoService;

class GetTranslatedVttRequest extends FormRequest
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
        $filename = $this->filename;
        $path = $video->s3_vtts_path . '/' . $filename;
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
