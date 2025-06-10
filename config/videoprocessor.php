<?php

return [

    'video_class' => env('VIDEOPROCESSOR_VIDEO_CLASS', 'App\Models\Video'),

    'queue' => env('VIDEOPROCESSOR_QUEUE', 'default'),

    'mediaconvert' => [
        'role_arn' => env('MEDIACONVERT_ROLE_ARN'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'output_bucket' => env('AWS_MEDIACONVERT_OUTPUT_BUCKET'),
        'notification_topic_arn' => env('MEDIACONVERT_NOTIFICATION_TOPIC_ARN'),
    ],

    'cloudfront' => [
        'domain' => env('CLOUDFRONT_DOMAIN'),
        'public_key_id' => env('CLOUDFRONT_PUBLIC_KEY_ID'),
        'private_key_path' => storage_path(env('CLOUDFRONT_PRIVATE_KEY_PATH', "app/cloudfront/private_key.pem")),
        'url_expiration' => env('CLOUDFRONT_URL_EXPIRATION', 240),
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