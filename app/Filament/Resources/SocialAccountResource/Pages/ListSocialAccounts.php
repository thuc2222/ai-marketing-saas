<?php

namespace App\Filament\Resources\SocialAccountResource\Pages;

use App\Filament\Resources\SocialAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSocialAccounts extends ListRecords
{
    protected static string $resource = SocialAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Nút kết nối Facebook cũ (nếu có)
            Actions\Action::make('connect_facebook')
                ->label('Connect Facebook')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('info')
                ->url(route('social.redirect', ['provider' => 'facebook'])),

            // --- NÚT KẾT NỐI TIKTOK MỚI ---
            Actions\Action::make('connect_tiktok')
                ->label('Connect TikTok')
                ->icon('heroicon-o-video-camera')
                ->color('gray') // Màu tối đặc trưng của TikTok
                ->url(route('social.redirect', ['provider' => 'tiktok'])),
        ];
    }
}