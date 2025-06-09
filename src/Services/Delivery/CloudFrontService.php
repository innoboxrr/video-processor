<?php

namespace Innoboxrr\VideoProcessor\Services\Delivery;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CloudFrontService
{
    protected string $cloudfrontDomain;
    protected string $keyPairId;
    protected string $privateKeyPath;
    protected int $urlExpirationMinutes;

    public function __construct()
    {
        $this->cloudfrontDomain = config('videoprocessor.cloudfront.domain');
        $this->keyPairId = config('videoprocessor.cloudfront.key_pair_id');
        $this->privateKeyPath = config('videoprocessor.cloudfront.private_key_path');
        $this->urlExpirationMinutes = config('videoprocessor.cloudfront.url_expiration', 240);
    }

    public function playback(object $video, string $filename)
    {
        $basePath = trim($video->s3_hls_path, '/');

        return $this->generateSignedPlaylist($basePath, $filename, $video);
    }

    protected function generateSignedPlaylist(string $basePath, string $filename, object $video)
    {
        $url = "{$this->cloudfrontDomain}/{$basePath}/{$filename}";

        return $this->generateSignedUrl($url);
    }

    protected function generateSignedUrl(string $url): string
    {
        $expiresAt = Carbon::now()->addMinutes($this->urlExpirationMinutes)->timestamp;

        $customPolicy = json_encode([
            'Statement' => [[
                'Resource' => $url,
                'Condition' => [
                    'DateLessThan' => ['AWS:EpochTime' => $expiresAt],
                ],
            ]],
        ]);

        $signature = $this->rsaSha1Sign($customPolicy);

        return $url
            . '?Policy=' . base64_encode($customPolicy)
            . '&Signature=' . str_replace(['+', '=', '/'], ['-', '_', '~'], base64_encode($signature))
            . '&Key-Pair-Id=' . $this->keyPairId;
    }

    protected function rsaSha1Sign(string $policy): string
    {
        $privateKey = file_get_contents($this->privateKeyPath);

        if (!$privateKey) {
            throw new \Exception('CloudFront private key not found or unreadable.');
        }

        $pkeyId = openssl_get_privatekey($privateKey);
        if (!$pkeyId) {
            throw new \Exception('Invalid private key for CloudFront.');
        }

        openssl_sign($policy, $signature, $pkeyId, OPENSSL_ALGO_SHA1);
        openssl_free_key($pkeyId);

        return $signature;
    }
}
