<?php

namespace Innoboxrr\VideoProcessor\Http\Requests\MediaConvert;

use Illuminate\Foundation\Http\FormRequest;
use Innoboxrr\VideoProcessor\Jobs\ProcessMediaConvertCallback;
use Illuminate\Support\Facades\Log;


class CallbackRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        // Log the incoming request for debugging purposes
        Log::info('MediaConvert Callback Request', [
            'headers' => $this->headers->all(),
            'body' => $this->all(),
        ]);
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
        $type = $this->header('x-amz-sns-message-type');

        if ($type === 'SubscriptionConfirmation') {
            $url = $this->json('SubscribeURL');
            file_get_contents($url);
            return response('Subscription confirmed', 200);
        }

        if ($type === 'Notification') {
            $message = json_decode($this->json('Message'), true);
            ProcessMediaConvertCallback::dispatch($message);
            return response('Notification received', 200);
        }
        return response('Unhandled message type', 400);
    }
}
