<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;

class ManageSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'General Settings';
    protected static ?string $title = 'System';
    protected static ?string $navigationGroup = 'System Management';
    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(GeneralSettings::class);

        // Nạp dữ liệu vào form
        $this->form->fill([
            // 1. Branding
            'site_name' => $settings->site_name,
            'site_logo' => $settings->site_logo,
            'brand_color' => $settings->brand_color,
            
            // 2. Localization
            'available_languages' => $settings->available_languages ?? [['code' => 'vi', 'flag_icon' => 'vn.svg']],
            'default_language' => $settings->default_language ?? 'vi',
            
            // 3. Currency
            'supported_currencies' => $settings->supported_currencies ?? [['code' => 'VND', 'symbol' => '₫', 'rate_per_credit' => 1000]],

            // 4. API Keys (Đã bổ sung đầy đủ)
            'openai_api_key' => $settings->openai_api_key,
            
            // Video AI
            'replicate_api_token' => $settings->replicate_api_token,
            'kling_access_key' => $settings->kling_access_key,
            'kling_secret_key' => $settings->kling_secret_key,
            
            // Social
            'tiktok_client_key' => $settings->tiktok_client_key,
            'tiktok_client_secret' => $settings->tiktok_client_secret,
            'facebook_app_id' => $settings->facebook_app_id,
            'facebook_app_secret' => $settings->facebook_app_secret,

            // Payment
            'stripe_public_key' => $settings->stripe_public_key,
            'stripe_secret_key' => $settings->stripe_secret_key,

            // 5. SMTP
            'smtp_host' => $settings->smtp_host,
            'smtp_port' => $settings->smtp_port,
            'smtp_username' => $settings->smtp_username,
            'smtp_password' => $settings->smtp_password,
            'smtp_sender_email' => $settings->smtp_sender_email,
            'smtp_sender_name' => $settings->smtp_sender_name,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        // TAB 1: THÔNG TIN CHUNG
                        Forms\Components\Tabs\Tab::make('General Info')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                TextInput::make('site_name')
                                    ->label(__('Website Name'))
                                    ->required(),
                                Grid::make(2)->schema([
                                    FileUpload::make('site_logo')
                                        ->label(__('Brand Logo'))
                                        ->image()
                                        ->directory('settings')
                                        ->visibility('public')
                                        ->columnSpan(1),
                                    ColorPicker::make('brand_color')
                                        ->label(__('Primary Color'))
                                        ->columnSpan(1),
                                ]),
                            ]),

                        // TAB 2: QUẢN LÝ NGÔN NGỮ
                        Forms\Components\Tabs\Tab::make('Localization')
                            ->icon('heroicon-o-language')
                            ->schema([
                                Select::make('default_language')
                                    ->label(__('Default Language'))
                                    ->options([
                                        'en' => __('English'), 
                                        'vi' => __('Vietnamese')
                                    ]),
                                    
                                Repeater::make('available_languages')
                                    ->label(__('Supported Languages'))
                                    ->schema([
                                        Select::make('code')
                                            ->label(__('Language'))
                                            ->options([
                                                'en' => __('English (US)'),
                                                'vi' => __('Vietnamese'),
                                                'ja' => __('Japanese'),
                                                'ko' => __('Korean'),
                                            ])
                                            ->required(),
                                        TextInput::make('flag_icon')
                                            ->label(__('Flag File (e.g., vn.svg)'))
                                            ->required(),
                                    ])
                                    ->grid(2)
                                    ->addActionLabel(__('Add Language'))
                                    ->defaultItems(1),
                            ]),

                        // TAB 3: TIỀN TỆ
                        Forms\Components\Tabs\Tab::make('Currency & Rates')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Repeater::make('supported_currencies')
                                    ->label(__('Currency Units'))
                                    ->schema([
                                        TextInput::make('code')
                                            ->label(__('Code (USD, VND)'))
                                            ->required()
                                            ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                            ->dehydrateStateUsing(fn (string $state): string => strtoupper($state)),
                                            
                                        TextInput::make('symbol')
                                            ->label(__('Symbol ($, ₫)'))
                                            ->required(),
                                        TextInput::make('rate_per_credit')
                                            ->label(__('Value per Credit'))
                                            ->numeric()
                                            ->prefix('1 Credit =')
                                            ->required(),
                                    ])
                                    ->columns(3)
                                    ->addActionLabel(__('Add Currency'))
                                    ->defaultItems(1),
                            ]),

                        // TAB 4: API INTEGRATIONS (ĐÃ CẬP NHẬT ĐẦY ĐỦ)
                        Forms\Components\Tabs\Tab::make('API Integrations')
                            ->icon('heroicon-o-key')
                            ->schema([
                                // 1. AI Core
                                Section::make(__('Core AI'))
                                    ->description(__('Configure OpenAI for script and content generation.'))
                                    ->schema([
                                        TextInput::make('openai_api_key')
                                            ->label(__('OpenAI Secret Key'))
                                            ->password()
                                            ->revealable()
                                            ->required(),
                                    ]),

                                // 2. Video Generation AI
                                Section::make(__('Video Generators'))
                                    ->description(__('API for Replicate (Luma/Sora) and Kling AI.'))
                                    ->schema([
                                        TextInput::make('replicate_api_token')
                                            ->label(__('Replicate API Token'))
                                            ->password()
                                            ->revealable(),
                                        Grid::make(2)->schema([
                                            TextInput::make('kling_access_key')
                                                ->label(__('Kling Access Key')),
                                            TextInput::make('kling_secret_key')
                                                ->label(__('Kling Secret Key'))
                                                ->password()
                                                ->revealable(),
                                        ]),
                                    ])->collapsed(),

                                // 3. Social Media
                                Section::make(__('Social Media Apps'))
                                    ->description(__('Connect TikTok, Facebook for automatic posting.'))
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('tiktok_client_key')->label(__('TikTok Client Key')),
                                            TextInput::make('tiktok_client_secret')->label(__('TikTok Secret'))->password(),
                                        ]),
                                        Grid::make(2)->schema([
                                            TextInput::make('facebook_app_id')->label(__('Facebook App ID')),
                                            TextInput::make('facebook_app_secret')->label(__('Facebook App Secret'))->password(),
                                        ]),
                                    ])->collapsed(),

                                // 4. Payment Gateway
                                Section::make(__('Payment Gateway (Stripe)'))
                                    ->description(__('Configure payment for Credits top-up.'))
                                    ->schema([
                                        TextInput::make('stripe_public_key')->label(__('Stripe Public Key')),
                                        TextInput::make('stripe_secret_key')->label(__('Stripe Secret Key'))->password()->revealable(),
                                    ])->collapsed(),
                            ]),

                        // TAB 5: EMAIL SMTP
                        Forms\Components\Tabs\Tab::make('Email System')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('smtp_host'),
                                    TextInput::make('smtp_port')->numeric(),
                                    TextInput::make('smtp_username'),
                                    TextInput::make('smtp_password')->password()->revealable(),
                                ]),
                                Grid::make(2)->schema([
                                    TextInput::make('smtp_sender_email')->email(),
                                    TextInput::make('smtp_sender_name'),
                                ]),
                            ]),
                    ])->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = app(GeneralSettings::class);

        // Branding
        $settings->site_name = $data['site_name'];
        $settings->site_logo = $data['site_logo'];
        $settings->brand_color = $data['brand_color'];

        // Localization
        $settings->available_languages = $data['available_languages'];
        $settings->default_language = $data['default_language'];
        $settings->supported_currencies = $data['supported_currencies'];

        // API Keys (Cập nhật mới)
        $settings->openai_api_key = $data['openai_api_key'];
        
        $settings->replicate_api_token = $data['replicate_api_token'];
        $settings->kling_access_key = $data['kling_access_key'];
        $settings->kling_secret_key = $data['kling_secret_key'];
        
        $settings->tiktok_client_key = $data['tiktok_client_key'];
        $settings->tiktok_client_secret = $data['tiktok_client_secret'];
        $settings->facebook_app_id = $data['facebook_app_id'];
        $settings->facebook_app_secret = $data['facebook_app_secret'];

        $settings->stripe_public_key = $data['stripe_public_key'];
        $settings->stripe_secret_key = $data['stripe_secret_key'];

        // SMTP
        $settings->smtp_host = $data['smtp_host'];
        $settings->smtp_port = $data['smtp_port'];
        $settings->smtp_username = $data['smtp_username'];
        $settings->smtp_password = $data['smtp_password'];
        $settings->smtp_sender_email = $data['smtp_sender_email'];
        $settings->smtp_sender_name = $data['smtp_sender_name'];

        $settings->save();

        Notification::make() 
            ->title(__('System configuration saved!'))
            ->success()
            ->send();
    }
}