<?php

return [

	'formats' => [
        'low' => [
            'bitrate' => 500,
            'scale' => [480, 360]
        ],
        'mid' => [
            'bitrate' => 1000,
            'scale' => [960, 720]
        ],
        'high' => [
            'bitrate' => 2000,
            'scale' => [1920, 1080]
        ],
        'super' => [
            'bitrate' => 3000,
            'scale' => [2560, 1920]
        ],
    ],
	
    'enabled_formats' => ['low', 'mid', 'high'],

	'ffmpeg_path' => env('FFMPEG_PATH', 'C:/xampp/htdocs/github/innoboxrr/video-processor/bin/ffmpeg/ffmpeg.exe'),

	'ffprobe_path' => env('FPROBE_PATH', 'C:/xampp/htdocs/github/innoboxrr/video-processor/bin/ffmpeg/ffprobe.exe'),

	's3_url' => env('S3_URL', 'https://innoboxrr.s3.amazonaws.com'),

    'cloudfront_url' => env('CLOUDFRONT_URL', 'd1st6n2eacne1j.cloudfront.net'),

    'video_path' => env('VIDEO_PATH', 'videos'),

	'guest_token_secret' => env('VIDEOPROCESSOR_GUEST_TOKEN_SECRET', 'secret'),

	// Player settings

	'video_watermark' => env('VIDEO_WATERMARK'),

	'video_icon' => env('VIDEO_ICON'),

	'video_allow_embed' => env('VIDEO_ALLOW_EMBED', 0),

	'video_show_watermark' => env('VIDEO_SHOW_WATERMARK', 0),
	
];