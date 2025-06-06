<?php

namespace Innoboxrr\VideoProcessor\Services\Subtitles\Pipeline;

use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;

class SubtitleTranslator
{
    public static function translate(object $video, string $sourceLanguage, string $targetLanguage): void
    {
        if (!Storage::disk('s3')->exists($video->s3_original_vtt_path)) {
            app(SubtitleService::class)->generate($video);
        }

        $vttContent = Storage::disk('s3')->get($video->s3_original_vtt_path);
        $vttLines = explode("\n", $vttContent);
        $translatedLines = [];

        $client = new Client();
        $apiKey = config('services.google_translate.api_key');

        foreach ($vttLines as $line) {
            if (self::isSubtitleText($line)) {
                $response = $client->post('https://translation.googleapis.com/language/translate/v2', [
                    'query' => [
                        'key' => $apiKey,
                        'q' => $line,
                        'source' => $sourceLanguage,
                        'target' => $targetLanguage,
                        'format' => 'text',
                    ],
                ]);

                $data = json_decode($response->getBody(), true);
                $translatedLines[] = $data['data']['translations'][0]['translatedText'] ?? $line;
            } else {
                $translatedLines[] = $line;
            }
        }

        $translatedVtt = implode("\n", $translatedLines);
        Storage::disk('s3')->put($video->s3_vtts_path . "/{$targetLanguage}.vtt", $translatedVtt);
    }

    protected static function isSubtitleText(string $line): bool
    {
        return !empty($line)
            && !preg_match('/^\d{2}:\d{2}:\d{2}\.\d{3} --> \d{2}:\d{2}:\d{2}\.\d{3}$/', $line)
            && !preg_match('/^WEBVTT$/', $line);
    }
}
