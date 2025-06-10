<?php

namespace Innoboxrr\VideoProcessor\Http\Requests\Vtt;

use App\Models\Language;
use Innoboxrr\VideoProcessor\Jobs\TranslateSubtitlesJob;
use Illuminate\Foundation\Http\FormRequest;

class AutoGenerateTranslatedVttRequest extends FormRequest
{

    protected $video;

    protected $language;

    protected function prepareForValidation()
    {
        //
    }

    public function authorize()
    {
        $this->video = app(config('videoprocessor.video_class'))::findOrFail($this->video_id);
        $this->language = Language::findOrFail($this->language_id);
        return $this->user()->can('update', $this->video) && $this->video->language !== null;
    }

    public function rules()
    {
        return [
            'video_id' => 'required|exists:videos,id',
            'language_id' => 'required|exists:languages,id',
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
        TranslateSubtitlesJob::dispatch($this->video->id, $this->video->language->code, $this->language->code);
        
        $this->video->subtitles()->firstOrCreate([
            'language_id' => $this->language->id,
            'type' => 'auto',
        ]);
    }
}
