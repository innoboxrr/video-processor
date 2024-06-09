<?php

namespace Innoboxrr\VideoProcessor\Http\Requests\Vtt;

use App\Models\Subtitle;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class DeleteTranslatedVttRequest extends FormRequest
{

    protected $video;

    protected $subtitle;

    protected function prepareForValidation()
    {
        //
    }

    public function authorize()
    {
        $this->subtitle = Subtitle::find($this->subtitle_id);
        $this->video = $this->subtitle->video;
        return $this->user()->can('update', $this->video);
    }

    public function rules()
    {
        return [
            'subtitle_id' => 'required|integer',
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
        Storage::disk('s3')->delete($this->video->s3_vtts_path . '/' . $this->subtitle->language->code . '.vtt');
        $this->video->subtitles()->where('id', $this->subtitle_id)->delete();
    }
}
