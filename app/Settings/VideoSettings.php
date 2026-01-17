<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class VideoSettings extends Settings
{
    public int $price_social_short;
    public int $price_pro_hd;
    public int $price_avatar_selling;
    public string $scripting_model;
    public string $video_provider;

    public static function group(): string
    {
        return 'video';
    }
}