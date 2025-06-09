<?php

namespace Innoboxrr\VideoProcessor\Support\Processors;

use Illuminate\Support\Facades\URL;

class VideoHelper
{
    /**
     * Genera una clave de 32 caracteres basada en el UUID
     * Esta clave es hex, por lo tanto representa 16 bytes reales (AES128)
     */
    public static function getEncryptionKey(string $uuid): string
    {
        return substr(hash('sha256', $uuid), 0, 32);
    }

    /**
     * Genera la URL para obtener la clave asociada al UUID
     */
    public static function getEncryptionUrl(string $uuid): string
    {
        return URL::route('video.encryption.key', ['code' => $uuid]);
    }
}
