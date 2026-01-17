<?php

namespace App\Filament\Widgets;

use App\Models\SocialPost;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DashboardStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Nếu là Admin thì xem toàn bộ, User thì chỉ xem của mình
        $query = SocialPost::query();
        
        if (Auth::user()->role !== 'admin') {
            $query->whereHas('marketingPlan', function ($q) {
                $q->where('user_id', Auth::id());
            });
        }

        return [
            Stat::make(__('Total Posts'), $query->count())
                ->description(__('AI Generated Posts'))
                ->descriptionIcon('heroicon-m-document-text')
                ->chart([7, 3, 10, 5, 15, 8, 20]) // Chart giả lập cho đẹp
                ->color('primary'),

            Stat::make(__('Total Views'), $query->sum('views_count'))
                ->description(__('Across all platforms'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),

            Stat::make(__('Total Engagement'), $query->sum('likes_count') + $query->sum('comments_count'))
                ->description(__('Likes + Comments'))
                ->descriptionIcon('heroicon-m-heart')
                ->color('danger'),
        ];
    }
}