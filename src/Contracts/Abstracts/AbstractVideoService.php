<?php

namespace Innoboxrr\VideoProcessor\Contracts\Abstracts;

use App\Models\Video;
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
        $this->ffmpegPath = config('videoprocessor.ffmpeg_path'); 

        config(['laravel-ffmpeg.ffmpeg.binaries' => $this->ffmpegPath]);

        $this->ffprobePath = config('videoprocessor.ffprobe_path');

        config(['laravel-ffmpeg.ffprobe.binaries' => $this->ffprobePath]);

        config(['laravel-ffmpeg.log_channel' => 'stack']);

        $this->cloudfrontUrl = config('videoprocessor.cloudfront_url');

        $this->checkDependencies();
    }

    public function authorization()
    {
        if(auth()->check()) return true;

        if(request()->has('guest_token') && $this->validateGuestToken(request()->get('guest_token'))) {
            return true;
        }

        throw new \Exception('Not authorized.');
    }

    public static function getHashSecret()
    {
        $expiration = now()->addMinutes(30)->timestamp;
        $secret = config('videoprocessor.guest_token_secret');
        $hashSecret = Hash::make($secret);

        return encrypt("{$expiration}|{$hashSecret}");
    }

    public function validateGuestToken($token)
    {
        try {
            $token = decrypt($token);
            [$expiration, $hashSecret] = explode('|', $token);
            if($expiration < now()->timestamp) {
                return false;
            }
            $secret = config('videoprocessor.guest_token_secret');
            if(Hash::check($secret, $hashSecret)) {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkDependencies()
    {
        if (!$this->isFFmpegAvailable() || !$this->isFFprobeAvailable()) {
            throw new \Exception('FFmpeg 0 FFprobe no está instalado o no es accesible.');
        }
    }

    private function isFFmpegAvailable()
    {
        return file_exists($this->ffmpegPath) && is_executable($this->ffmpegPath);
    }

    private function isFFprobeAvailable()
    {
        return file_exists($this->ffprobePath) && is_executable($this->ffprobePath);
    }

    protected function tempOriginUrl($path) 
    {
        return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(5));
    }

    public function getVideoByCode($code)
    {
        // Ver si combine con cache
        /*
        return cache()->remember("video_by_code_{$code}", now()->addMinutes(5), function() use ($code) {
            return Video::where('code', $code)->firstOrFail();
        });
        */
        return Video::where('code', $code)->firstOrFail();
    }

}