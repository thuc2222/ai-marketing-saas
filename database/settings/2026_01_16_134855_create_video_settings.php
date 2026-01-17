<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateVideoSettings extends SettingsMigration
{
    public function up(): void
    {
        // Nhóm 'video' phải khớp với group() trong App\Settings\VideoSettings
        $this->migrator->add('video.price_social_short', 50);
        $this->migrator->add('video.price_pro_hd', 150);
        $this->migrator->add('video.price_avatar_selling', 300);
        $this->migrator->add('video.scripting_model', 'gpt-4o');
        $this->migrator->add('video.video_provider', 'luma');
    }

    public function down(): void
    {
        $this->migrator->delete('video.price_social_short');
        $this->migrator->delete('video.price_pro_hd');
        $this->migrator->delete('video.price_avatar_selling');
        $this->migrator->delete('video.scripting_model');
        $this->migrator->delete('video.video_provider');
    }
}