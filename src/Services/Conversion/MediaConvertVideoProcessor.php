<?php

namespace Innoboxrr\VideoProcessor\Services\Conversion;

use Aws\MediaConvert\MediaConvertClient;
use Illuminate\Support\Facades\Storage;

class MediaConvertVideoProcessor
{
    protected MediaConvertClient $client;

    public function __construct()
    {
        $this->client = new MediaConvertClient([
            'version' => 'latest',
            'region' => config('videoprocessor.mediaconvert.region'),
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
            'endpoint' => config('videoprocessor.mediaconvert.endpoint'),
        ]);
    }

    public function process(object $video): void
    {
        // ✅ Limpieza previa
        Storage::disk('s3')->deleteDirectory($video->s3_hls_path);
        Storage::disk('s3')->deleteDirectory($video->s3_keys_path);

        $inputPath = "s3://{$video->s3_bucket}/{$video->s3_original_path}";
        $outputPath = "s3://" . config('videoprocessor.mediaconvert.output_bucket') . "/videos/{$video->code}/hls/";

        $this->client->createJob([
            'Role' => config('videoprocessor.mediaconvert.role_arn'),
            'Settings' => [
                'OutputGroups' => [[
                    'Name' => 'HLS Group',
                    'OutputGroupSettings' => [
                        'Type' => 'HLS_GROUP_SETTINGS',
                        'HlsGroupSettings' => [
                            'Destination' => $outputPath,
                            'Encryption' => [
                                'EncryptionMethod' => 'AES128',
                                'StaticKeySettings' => [
                                    'KeyProviderServer' => [
                                        'Uri' => config('videoprocessor.mediaconvert.key_uri'),
                                    ],
                                    'StaticKeyValue' => config('videoprocessor.mediaconvert.encryption_key'),
                                ],
                            ],
                        ],
                    ],
                    'Outputs' => [[
                        'VideoDescription' => [
                            'CodecSettings' => [
                                'Codec' => 'H_264',
                            ],
                        ],
                        'ContainerSettings' => [
                            'Container' => 'M3U8',
                        ],
                    ]],
                ]],
                'Inputs' => [[
                    'FileInput' => $inputPath,
                ]],
            ],
        ]);

        $video->update([
            'status' => 'processing_in_mediaconvert',
            'progress' => 0,
        ]);
    }
}
