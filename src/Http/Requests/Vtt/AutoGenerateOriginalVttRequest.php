<?php

namespace Innoboxrr\VideoProcessor\Http\Requests\Vtt;

use Innoboxrr\VideoProcessor\Jobs\GenerateSubtitlesJob;
use Illuminate\Foundation\Http\FormRequest;

class AutoGenerateOriginalVttRequest extends FormRequest
{

    protected $video;

    protected function prepareForValidation()
    {
        //
    }

    public function authorize()
    {
        $this->video = app(config('videoprocessor.video_class'))::findOrFail($this->video_id);
        return $this->user()->can('update', $this->video) && $this->video->language !== null;
    }

    public function rules()
    {
        return [
            'video_id' => 'required|exists:videos,id',
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
        GenerateSubtitlesJob::dispatch($this->video_id);
    }
}
