<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pages\Auth\EditProfile;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Storage;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // 1. Khởi tạo settings để lấy dữ liệu động
        $settings = app(GeneralSettings::class);

        return $panel
            ->navigationGroups([
            'Marketing Operations',
            'Account Setup',
            'System Management',
            ])
            ->default()
            ->id('admin')
            ->path('admin')

            // --- BRANDING ĐỘNG ---
            ->brandName($settings->site_name ?? 'AI Marketing')
            ->brandLogo($settings->site_logo ? Storage::url($settings->site_logo) : null)
            ->brandLogoHeight('3rem')

            // --- MÀU SẮC THƯƠNG HIỆU ---
            ->colors([
                'primary' => $settings->brand_color ?? Color::Emerald,
                'danger' => Color::Rose,
                'gray' => Color::Slate,
                'info' => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])

            // --- GIAO DIỆN & UX ---
            ->font('Montserrat')
            ->sidebarCollapsibleOnDesktop()
            // Sắp xếp thứ tự Menu trái để không bị lộn xộn
            ->navigationGroups([
                '1. Chiến lược & Kế hoạch',
                '2. Sản xuất Nội dung',
                '3. Hệ thống',
                'Hệ thống', // Fallback cho các mục mặc định
            ])

            // --- AUTHENTICATION ---
            ->login()
            ->registration()
            ->passwordReset()
            ->emailVerification()
            ->profile(EditProfile::class)

            // --- NOTIFICATIONS ---
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')

            // --- RESOURCES & PAGES ---
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])

            // --- TOPBAR COMPONENTS (FIX LỖI CÚ PHÁP TẠI ĐÂY) ---
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => Blade::render('
                    <div class="flex items-center gap-x-3">
                        {{-- 1. Currency Switcher (Livewire Component) --}}
                        @livewire(\'currency-switcher\')

                        {{-- 2. Credit Badge (View Blade thông thường) --}}
                        {{-- Sử dụng @include vì đây là file view, không phải Livewire Class --}}
                        @include(\'filament.components.credit-badge\')
                    </div>
                ')
            )

            // --- MIDDLEWARE ---
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function boot(): void
    {
        // Cấu hình Language Switcher Động
        LanguageSwitch::configureUsing(function ($switch) {
            $settings = app(GeneralSettings::class);
            $languages = $settings->available_languages ?? [];

            $locales = [];
            $flags = [];

            foreach ($languages as $lang) {
                if (isset($lang['code'])) {
                    $code = $lang['code'];
                    $locales[] = $code;
                    $flags[$code] = asset('flags/' . ($lang['flag_icon'] ?? 'us.svg'));
                }
            }

            // Fallback nếu chưa cấu hình gì
            if (empty($locales)) {
                $locales = ['en', 'vi'];
                $flags = [
                    'en' => asset('flags/us.svg'),
                    'vi' => asset('flags/vn.svg'),
                ];
            }

            $switch
                ->locales($locales)
                ->visible(outsidePanels: true)
                ->flags($flags)
                ->circular();
        });
    }
}