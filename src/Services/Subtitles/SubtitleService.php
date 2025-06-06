<?php

namespace Innoboxrr\VideoProcessor\Services\Subtitles;

use Innoboxrr\VideoProcessor\Services\Subtitles\Pipeline\AudioExtractor;
use Innoboxrr\VideoProcessor\Services\Subtitles\Pipeline\SubtitleTranscriber;
use Innoboxrr\VideoProcessor\Services\Subtitles\Pipeline\SubtitleTranslator;
use Innoboxrr\VideoProcessor\Services\Subtitles\Pipeline\SubtitleUploader;
use Innoboxrr\VideoProcessor\Services\Subtitles\Pipeline\TempFileManager;
use Innoboxrr\VideoProcessor\Services\Subtitles\Pipeline\VideoDownloader;


class SubtitleService
{
    public function generate(object $video): void
    {
        $tempPaths = TempFileManager::create($video);
        VideoDownloader::download($video, $tempPaths->video);
        AudioExtractor::extract($tempPaths->video, $tempPaths->audio);
        SubtitleTranscriber::transcribe($tempPaths->audio, $tempPaths->vtt);
        SubtitleUploader::upload($video, $tempPaths->vtt);
        TempFileManager::cleanup($tempPaths);
    }

    public function translate(object $video, string $sourceLanguage, string $targetLanguage): void
    {
        SubtitleTranslator::translate($video, $sourceLanguage, $targetLanguage);
    }
}
