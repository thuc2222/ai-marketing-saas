<?php

namespace App\Filament\Pages;

use App\Settings\VideoSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageVideoSettings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'System Management';
    protected static ?string $navigationLabel = 'AI Video Settings';

    // Kết nối với class VideoSettings ở Bước 1
    protected static string $settings = VideoSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Video Model Pricing'))
                    ->description(__('Set credit costs for different video tiers'))
                    ->schema([
                        TextInput::make('price_social_short')
                            ->label(__('Social Short Cost'))
                            ->numeric()
                            ->default(50)
                            ->suffix(__('Credits')),
                        TextInput::make('price_pro_hd')
                            ->label(__('Pro HD Cost'))
                            ->numeric()
                            ->default(150)
                            ->suffix(__('Credits')),
                        TextInput::make('price_avatar_selling')
                            ->label(__('Avatar Selling Cost'))
                            ->numeric()
                            ->default(300)
                            ->suffix(__('Credits')),
                    ])->columns(3),

                Section::make(__('Preferred Providers'))
                    ->description(__('Choose which AI models handle logic and production'))
                    ->schema([
                        Select::make('scripting_model')
                            ->options([
                                'gpt-4o' => __('GPT-4o (High Quality)'),
                                'gpt-4o-mini' => __('GPT-4o Mini (Fast & Cheap)'),
                            ])
                            ->default('gpt-4o')
                            ->required()
                            ->native(false),
                        Select::make('video_provider')
                            ->options([
                                'kling' => __('Kling AI'),
                                'luma' => __('Luma Dream Machine'),
                                'runway' => __('Runway Gen-3'),
                            ])
                            ->required()
                            ->native(false),
                    ])->columns(2),
            ]);
    }
}