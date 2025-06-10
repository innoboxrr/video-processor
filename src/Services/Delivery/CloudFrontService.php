<?php

namespace Innoboxrr\VideoProcessor\Services\Delivery;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CloudFrontService
{
    protected string $cloudfrontDomain;
    protected string $publicKeyId;
    protected string $privateKeyPath;
    protected int $urlExpirationMinutes;

    public function __construct()
    {
        $this->cloudfrontDomain = config('videoprocessor.cloudfront.domain');
        $this->publicKeyId = config('videoprocessor.cloudfront.public_key_id');
        $this->privateKeyPath = config('videoprocessor.cloudfront.private_key_path');
        $this->urlExpirationMinutes = config('videoprocessor.cloudfront.url_expiration', 240);
    }

    public function playback(string $path, string $filename)
    {
        $basePath = trim($path, '/');
        return $this->generateSignedPlaylist($basePath, $filename);
    }

    protected function generateSignedPlaylist(string $basePath, string $filename)
    {
        $url = "{$this->cloudfrontDomain}/{$basePath}/{$filename}";

        return $this->generateSignedUrl($url);
    }

    protected function generateSignedUrl(string $url): string
    {
        $expiresAt = Carbon::now()->addMinutes($this->urlExpirationMinutes)->timestamp;

        $policy = json_encode([
            'Statement' => [[
                'Resource' => $url,
                'Condition' => [
                    'DateLessThan' => ['AWS:EpochTime' => $expiresAt],
                ],
            ]],
        ]);

        $signature = $this->rsaSha1Sign($policy);

        return $url
            . '?Policy=' . $this->urlSafe(base64_encode($policy))
            . '&Signature=' . $this->urlSafe(base64_encode($signature))
            . '&Key-Pair-Id=' . $this->publicKeyId;
    }

    public function generateSignedCookies(string $resource): array
    {
        $expiresAt = Carbon::now()->addMinutes($this->urlExpirationMinutes)->timestamp;

        $policy = json_encode([
            'Statement' => [[
                'Resource' => $resource,
                'Condition' => [
                    'DateLessThan' => ['AWS:EpochTime' => $expiresAt],
                ],
            ]],
        ]);

        $signature = $this->rsaSha1Sign($policy);

        return [
            'CloudFront-Policy' => $this->urlSafe(base64_encode($policy)),
            'CloudFront-Signature' => $this->urlSafe(base64_encode($signature)),
            'CloudFront-Key-Pair-Id' => $this->publicKeyId,
        ];
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

        return $signature;
    }


    protected function urlSafe(string $value): string
    {
        return strtr($value, ['+' => '-', '=' => '_', '/' => '~']);
    }

    public function processAndSignPlaylist(string $basePath, string $filename, string $code)
    {
        $s3Path = trim("{$basePath}/{$filename}", '/');

        // Leer archivo del disco S3
        try {
            $contents = Storage::disk('s3')->get($s3Path);
        } catch (\Throwable $e) {
            abort(404, 'Playlist not found on S3');
        }

        $lines = preg_split("/\r\n|\n|\r/", $contents); // soporta cualquier tipo de salto de línea
        $processed = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                $processed[] = '';
                continue;
            }

            // Si es otro .m3u8 (rendition), usar ruta de Laravel
            if (Str::endsWith($line, '.m3u8')) {
                $route = route('videoprocessor.playlist', [
                    'code' => $code,
                    'filename' => $line,
                ]);
                $processed[] = $route;
            }
            // Si es fragmento TS, firmar con CloudFront
            elseif (Str::endsWith($line, '.ts')) {
                $tsPath = trim("{$basePath}/{$line}", '/');
                $signed = $this->generateSignedUrl("{$this->cloudfrontDomain}/{$tsPath}");
                $processed[] = $signed;
            }
            // Lo demás (headers #EXT-X...), dejar igual
            else {
                $processed[] = $line;
            }
        }

        return response(implode("\r\n", $processed), 200)
            ->header('Content-Type', 'application/vnd.apple.mpegurl')
            ->header('Cache-Control', 'no-store');
    }


}
