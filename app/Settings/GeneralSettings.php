<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    // Branding
    public string $site_name;
    public ?string $site_logo;
    public string $brand_color; // Hex code
    
    // Localization
    public string $default_language;
    public string $currency_code;

    // API Keys (Lưu trong DB tiện hơn .env cho SaaS)
    public ?string $openai_api_key;
    public ?string $tiktok_client_key;
    public ?string $tiktok_client_secret;

    // Video AI
    public ?string $replicate_api_token;
    public ?string $kling_access_key;
    public ?string $kling_secret_key;

    // Social
    public ?string $facebook_app_id;
    public ?string $facebook_app_secret;
    
    // Payment
    public ?string $stripe_public_key;
    public ?string $stripe_secret_key;
    
    // SMTP (Email)
    public ?string $smtp_host;
    public ?string $smtp_port;
    public ?string $smtp_username;
    public ?string $smtp_password;
    public ?string $smtp_sender_name;
    public ?string $smtp_sender_email;
    public array $available_languages;
    public array $supported_currencies;

    public static function group(): string
    {
        return 'general';
    }
}