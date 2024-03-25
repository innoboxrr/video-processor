<?php

return [

	'ffmpeg_path' => env('FFMPEG_PATH', 'C:/xampp/htdocs/github/innoboxrr/video-processor/bin/ffmpeg/ffmpeg.exe'),

	'ffprobe_path' => env('FPROBE_PATH', 'C:/xampp/htdocs/github/innoboxrr/video-processor/bin/ffmpeg/ffprobe.exe'),

	's3_url' => env('S3_URL', 'https://innoboxrr.s3.amazonaws.com'),

    'cloudfront_url' => env('CLOUDFRONT_URL', 'd1st6n2eacne1j.cloudfront.net'),

    'video_path' => env('VIDEO_PATH', 'videos'),

	// Player settings

	'video_watermark' => env('VIDEO_WATERMARK'),

	'video_icon' => env('VIDEO_ICON'),

	'video_allow_embed' => env('VIDEO_ALLOW_EMBED', 0),

	'video_show_watermark' => env('VIDEO_SHOW_WATERMARK', 0),
	
];