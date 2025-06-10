<?php

namespace Innoboxrr\VideoProcessor\Http\Requests\Vtt;

use App\Models\Language;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class UploadTranslatedVttRequest extends FormRequest
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
            'file' => 'required|file|mimetypes:text/vtt,text/plain',
            'language_id' => 'required|exists:languages,id', // Add 'language_id' => 'required|exists:languages,id
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
        Storage::disk('s3')->delete($this->video->s3_vtts_path . '/' . $this->language->code . '.vtt');
        $this->file('file')->storeAs($this->video->s3_vtts_path, $this->language->code . '.vtt', 's3');

        $this->video->subtitles()->firstOrCreate([
            'language_id' => $this->language->id,
            'type' => 'manual',
        ]);

        return $this->video;
    }
}
