<?php

namespace Innoboxrr\VideoProcessor\Services\Conversion;

use Aws\MediaConvert\MediaConvertClient;
use Illuminate\Support\Facades\Storage;
use Innoboxrr\VideoProcessor\Support\Helpers\VideoHelper;

class MediaConvertVideoProcessor
{
    protected MediaConvertClient $client;

    public function __construct()
    {
        $this->setClient();
    }

    public function setClient(): void
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
        $this->cleanupPreviousOutput($video);

        $inputPath = $this->buildInputPath($video);
        $outputPath = $this->buildOutputPath($video);
        $outputs = $this->buildOutputs();

        $jobSettings = $this->buildJobSettings(
            $video, 
            $inputPath, 
            $outputPath, 
            $outputs
        );

        $this->client->createJob([
            'Role' => config('videoprocessor.mediaconvert.role_arn'),
            'Settings' => $jobSettings,
            'UserMetadata' => [
                'Customer' => 'Profemx',
                'VideoCode' => $video->code,
                'VideoId' => $video->id,
            ],
            'Notifications' => [
                'Progressing' => config('videoprocessor.mediaconvert.notification_topic_arn'),
                'Complete' => config('videoprocessor.mediaconvert.notification_topic_arn'),
                'Error' => config('videoprocessor.mediaconvert.notification_topic_arn'),
            ],
        ]);

        $this->markVideoAsProcessing($video);
    }

    /**
     * Elimina el contenido previo en S3 del video procesado y llaves.
     */
    protected function cleanupPreviousOutput(object $video): void
    {
        Storage::disk('s3')->deleteDirectory($video->s3_hls_path);
        Storage::disk('s3')->deleteDirectory($video->s3_keys_path);
    }

    /**
     * Construye la ruta de entrada para MediaConvert (archivo original en S3).
     */
    protected function buildInputPath(object $video): string
    {
        return "s3://{$video->s3_bucket}/{$video->s3_original_path}";
    }

    /**
     * Construye la ruta de salida donde se colocarán los segmentos y playlists.
     */
    protected function buildOutputPath(object $video): string
    {
        $basePath = dirname($video->s3_original_path);
        return "s3://{$video->s3_bucket}/{$basePath}/hls/";
    }

    /**
     * Construye dinámicamente los outputs según las resoluciones habilitadas.
     */
    protected function buildOutputs(): array
    {
        $enabledFormats = config('videoprocessor.enabled_formats', []);
        $allFormats = config('videoprocessor.formats', []);
        $outputs = [];

        foreach ($enabledFormats as $key) {
            if (!isset($allFormats[$key])) {
                continue;
            }

            $format = $allFormats[$key];
            $scale = $format['scale'];

            $outputs[] = [
                'NameModifier' => "_{$key}",
                'ContainerSettings' => [
                    'Container' => 'M3U8',
                ],
                'VideoDescription' => [
                    'Width' => $scale[0],
                    'Height' => $scale[1],
                    'CodecSettings' => [
                        'Codec' => 'H_264',
                        'H264Settings' => [
                            'Bitrate' => $format['bitrate'] * 1000,
                            'RateControlMode' => 'CBR',
                            'GopSize' => 2,
                            'GopSizeUnits' => 'SECONDS',
                            'GopBReference' => 'DISABLED',
                            'GopClosedCadence' => 1,
                            'InterlaceMode' => 'PROGRESSIVE',
                            'ParControl' => 'INITIALIZE_FROM_SOURCE',
                            'NumberBFramesBetweenReferenceFrames' => 2,
                        ],
                    ],
                ],
                'AudioDescriptions' => [[
                    'CodecSettings' => [
                        'Codec' => 'AAC',
                        'AacSettings' => [
                            'Bitrate' => $format['audio_bitrate'] * 1000,
                            'CodingMode' => 'CODING_MODE_2_0',
                            'SampleRate' => 48000,
                        ],
                    ],
                ]],
            ];
        }

        return $outputs;
    }

    /**
     * Arma el arreglo completo que se enviará como `Settings` a MediaConvert.
     */
    protected function buildJobSettings(object $video, string $inputPath, string $outputPath, array $outputs): array
    {
        return [
            'OutputGroups' => [[
                'Name' => 'HLS Group',
                'OutputGroupSettings' => [
                    'Type' => 'HLS_GROUP_SETTINGS',
                    'HlsGroupSettings' => [
                        'Destination' => $outputPath,
                        'SegmentLength' => 10,
                        'MinSegmentLength' => 0,
                        'ManifestDurationFormat' => 'INTEGER',
                        'OutputSelection' => 'MANIFESTS_AND_SEGMENTS',
                        'ProgramDateTime' => 'EXCLUDE',
                        'SegmentControl' => 'SEGMENTED_FILES',
                        'IndexNSegments' => 3,
                        'DirectoryStructure' => 'SINGLE_DIRECTORY',
                        'ManifestCompression' => 'NONE',
                        'ClientCache' => 'ENABLED',
                        'Encryption' => [
                            'Type' => 'STATIC_KEY',
                            'EncryptionMethod' => 'AES128',
                            'StaticKeyProvider' => [
                                'StaticKeyValue' => VideoHelper::getEncryptionKey($video->code),
                                'Url' => VideoHelper::getEncryptionKey($video->code),
                            ],
                        ],
                    ],
                ],
                'Outputs' => $outputs,
            ]],
            'Inputs' => [[
                'FileInput' => $inputPath,
                'AudioSelectors' => [
                    'Audio Selector 1' => [
                        'DefaultSelection' => 'DEFAULT',
                    ],
                ],
            ]],
        ];
    }

    /**
     * Marca el video como "en procesamiento" en la base de datos.
     */
    protected function markVideoAsProcessing(object $video): void
    {
        $video->update([
            'status' => 'processing_in_mediaconvert',
            'progress' => 0,
        ]);
    }
}
