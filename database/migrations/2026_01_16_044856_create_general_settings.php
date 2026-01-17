<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', 'AI Marketing SaaS');
        $this->migrator->add('general.site_logo', null);
        $this->migrator->add('general.brand_color', '#F59E0B'); // Màu Amber mặc định
        $this->migrator->add('general.default_language', 'en');
        $this->migrator->add('general.currency_code', 'USD');
        
        $this->migrator->add('general.openai_api_key', null);
        $this->migrator->add('general.tiktok_client_key', null);
        $this->migrator->add('general.tiktok_client_secret', null);
        
        // SMTP defaults
        $this->migrator->add('general.smtp_host', 'smtp.mailgun.org');
        $this->migrator->add('general.smtp_port', '587');
        $this->migrator->add('general.smtp_username', null);
        $this->migrator->add('general.smtp_password', null);
        $this->migrator->add('general.smtp_sender_name', 'Support Team');
        $this->migrator->add('general.smtp_sender_email', 'noreply@example.com');
    }
}