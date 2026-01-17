<?php

namespace App\Filament\Widgets;

use App\Models\SocialPost;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Posts', SocialPost::count())
                ->description('All time posts')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Pending Posts', SocialPost::where('status', 'scheduled')->count())
                ->description('Ready to publish')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Engagement', SocialPost::sum('likes_count') + SocialPost::sum('comments_count'))
                ->description('Likes + Comments')
                ->descriptionIcon('heroicon-m-heart')
                ->chart([7, 2, 10, 3, 15, 4, 17]) // Fake chart cho đẹp (sau này query thật)
                ->color('success'),
        ];
    }
}