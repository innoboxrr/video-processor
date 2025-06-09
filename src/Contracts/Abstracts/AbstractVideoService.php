<?php

namespace Innoboxrr\VideoProcessor\Contracts\Abstracts;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

abstract class AbstractVideoService
{
    protected $ffmpegPath;
    protected $ffprobePath;
    protected $cloudfrontUrl;
    protected $videoIdentifier;
    protected $s3BasePath;

    public function __construct()
    {
        $this->configureFFmpeg();
        $this->cloudfrontUrl = config('videoprocessor.cloudfront_url');
        $this->checkDependencies();
    }

    protected function configureFFmpeg(): void
    {
        $this->ffmpegPath = config('videoprocessor.ffmpeg_path');
        $this->ffprobePath = config('videoprocessor.ffprobe_path');

        config([
            'laravel-ffmpeg.ffmpeg.binaries' => $this->ffmpegPath,
            'laravel-ffmpeg.ffprobe.binaries' => $this->ffprobePath,
            'laravel-ffmpeg.log_channel' => 'stack',
        ]);
    }

    public static function authorization(): bool
    {
        if (method_exists(static::class, 'customAuthorization')) {
            return static::customAuthorization();
        }

        if (auth()->check()) {
            return true;
        }

        if (request()->has('guest_token') && static::validateGuestToken(request()->get('guest_token'))) {
            return true;
        }

        throw new \Exception('Not authorized.');
    }

    public static function getHashSecret(): string
    {
        $expiration = now()->addMinutes(30)->timestamp;
        $secret = config('videoprocessor.guest_token_secret');
        $hashSecret = Hash::make($secret);

        return encrypt("{$expiration}|{$hashSecret}");
    }

    public static function validateGuestToken($token): bool
    {
        try {
            $token = decrypt($token);
            [$expiration, $hashSecret] = explode('|', $token);
            if ($expiration < now()->timestamp) {
                return false;
            }
            $secret = config('videoprocessor.guest_token_secret');
            return Hash::check($secret, $hashSecret);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkDependencies(): void
    {
        if (!file_exists($this->ffmpegPath) || !is_executable($this->ffmpegPath)) {
            throw new \Exception('FFmpeg no está instalado o no es ejecutable.');
        }

        if (!file_exists($this->ffprobePath) || !is_executable($this->ffprobePath)) {
            throw new \Exception('FFprobe no está instalado o no es ejecutable.');
        }
    }

    protected function tempOriginUrl(string $path): string
    {
        return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(5));
    }

    public function getVideoByCode(string $code): object
    {
        $videoModel = config('videoprocessor.video_class', 'App\\Models\\Video');

        if (!class_exists($videoModel)) {
            throw new \Exception("Model class for 'video' is not defined or does not exist.");
        }

        return $videoModel::where('code', $code)->firstOrFail();
    }

    public function getVideoById(int $id): object
    {
        $videoModel = config('videoprocessor.video_class', 'App\\Models\\Video');

        if (!class_exists($videoModel)) {
            throw new \Exception("Model class for 'video' is not defined or does not exist.");
        }

        return $videoModel::findOrFail($id);
    }
}
