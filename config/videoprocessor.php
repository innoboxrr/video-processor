<?php

return [

    'video_class' => env('VIDEOPROCESSOR_VIDEO_CLASS', 'App\Models\Video'),

    'process_with_mediaconvert' => env('VIDEOPROCESSOR_PROCESS_WITH_MEDIACONVERT', false),

    'mediaconvert' => [
        'role_arn' => env('MEDIACONVERT_ROLE_ARN'),
        'endpoint' => env('MEDIACONVERT_ENDPOINT'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'output_bucket' => env('AWS_MEDIACONVERT_OUTPUT_BUCKET'),
        'encryption_key' => env('HLS_STATIC_KEY'), // en hex
        'key_uri' => env('HLS_KEY_URI'), // URL pública a servir la key
    ],

    'cloudfront' => [
        'domain' => env('CLOUDFRONT_DOMAIN'), // e.g. https://d123.cloudfront.net
        'key_pair_id' => env('CLOUDFRONT_KEY_PAIR_ID'),
        'private_key_path' => storage_path('app/cloudfront/private_key.pem'),
        'url_expiration' => 240, // minutos
    ],


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

	'ffmpeg_path' => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
	'ffprobe_path' => env('FPROBE_PATH', '/usr/bin/ffprobe'),
    'ffmpeg_threads' => env('FFMPEG_THREADS', 12),

	's3_url' => env('S3_URL', 'https://innoboxrr.s3.amazonaws.com'),
    'cloudfront_url' => env('CLOUDFRONT_URL', 'd1st6n2eacne1j.cloudfront.net'),
    'video_path' => env('VIDEO_PATH', 'videos'),

	'guest_token_secret' => env('VIDEOPROCESSOR_GUEST_TOKEN_SECRET', 'secret'),

    // VTT
    'vtt' => [
        'auto-generate' => env('VTT_AUTO_GENERATE', 1),
    ],

	// Player settings
	'video_watermark' => env('VIDEO_WATERMARK'),
	'video_icon' => env('VIDEO_ICON'),
	'video_allow_embed' => env('VIDEO_ALLOW_EMBED', 0),
	'video_show_watermark' => env('VIDEO_SHOW_WATERMARK', 0),
];