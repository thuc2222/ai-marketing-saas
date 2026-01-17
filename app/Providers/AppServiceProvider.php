<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use App\Settings\GeneralSettings;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Ép buộc sử dụng HTTPS trên môi trường Production (Quan trọng cho SaaS)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // 2. Tối ưu hóa Database (Mặc định string length để tránh lỗi MySQL cũ)
        Schema::defaultStringLength(191);

        // 3. Nạp cấu hình động từ Database (System Settings)
        // Dùng try-catch để tránh lỗi khi chạy migration lần đầu (khi bảng settings chưa tồn tại)
        try {
            // Kiểm tra xem class Settings có tồn tại và kết nối DB ok không
            if (class_exists(GeneralSettings::class) && Schema::hasTable('settings')) {
                
                $settings = app(GeneralSettings::class);

                // --- A. CẤU HÌNH MAIL (SMTP) ---
                // Nếu Admin đã nhập Host trong trang cài đặt, ta ưu tiên dùng nó
                if (!empty($settings->smtp_host)) {
                    Config::set('mail.mailers.smtp.host', $settings->smtp_host);
                    Config::set('mail.mailers.smtp.port', $settings->smtp_port);
                    Config::set('mail.mailers.smtp.username', $settings->smtp_username);
                    Config::set('mail.mailers.smtp.password', $settings->smtp_password);
                    
                    // Thiết lập người gửi mặc định
                    if (!empty($settings->smtp_sender_email)) {
                        Config::set('mail.from.address', $settings->smtp_sender_email);
                        Config::set('mail.from.name', $settings->smtp_sender_name ?? 'SaaS Support');
                    }
                }

                // --- B. CẤU HÌNH API KEYS ---
                
                // OpenAI Key
                if (!empty($settings->openai_api_key)) {
                    // Cập nhật cho config mặc định của OpenAI Client
                    Config::set('openai.api_key', $settings->openai_api_key); 
                    Config::set('services.openai.secret', $settings->openai_api_key);
                }

                // TikTok Credentials
                if (!empty($settings->tiktok_client_key)) {
                    Config::set('services.tiktok.client_id', $settings->tiktok_client_key);
                    Config::set('services.tiktok.client_secret', $settings->tiktok_client_secret);
                    // Nếu dùng Socialite
                    Config::set('services.tiktok.redirect', url('/auth/tiktok/callback'));
                }

                // --- C. CẤU HÌNH NGÔN NGỮ & TIỀN TỆ ---
                if (!empty($settings->default_language)) {
                    Config::set('app.locale', $settings->default_language);
                    Config::set('app.fallback_locale', 'en');
                    \Carbon\Carbon::setLocale($settings->default_language);
                }

                // Lưu currency vào config để các nơi khác tiện lấy ra dùng
                if (!empty($settings->currency_code)) {
                    Config::set('app.currency', $settings->currency_code);
                }
            }
        } catch (\Exception $e) {
            // Nếu có lỗi (VD: Chưa chạy migration, mất kết nối DB), hệ thống sẽ bỏ qua
            // và sử dụng cấu hình mặc định trong file .env
            // Không làm sập app.
        }
    }
}