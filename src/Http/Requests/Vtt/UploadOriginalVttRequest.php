<?php

namespace Innoboxrr\VideoProcessor\Http\Requests\Vtt;

use App\Models\Video;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class UploadOriginalVttRequest extends FormRequest
{

    protected $video;

    protected function prepareForValidation()
    {
        //
    }

    public function authorize()
    {
        $this->video = Video::findOrFail($this->video_id);
        return $this->user()->can('update', $this->video) && $this->video->language !== null;
    }

    public function rules()
    {
        return [
            'file' => 'required|file|mimetypes:text/vtt,text/plain',
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
        // Eliminate S3 file
        Storage::disk('s3')->delete($this->video->s3_original_vtt_path);
        Storage::disk('s3')->delete($this->video->s3_vtts_path . '/' . $this->video->language->code . '.vtt');

        // Store new file
        $this->file('file')->storeAs($this->video->s3_path, 'original.vtt', 's3');
        $this->file('file')->storeAs($this->video->s3_vtts_path, $this->video->language->code . '.vtt', 's3');

        $this->video->subtitles()->firstOrCreate([
            'language_id' => $this->video->language->id,
            'type' => 'manual',
        ]);

        return $this->video;
    }
}
