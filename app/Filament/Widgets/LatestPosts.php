<?php

namespace App\Filament\Widgets;

use App\Models\SocialPost;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class LatestPosts extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SocialPost::query()
                    ->whereHas('marketingPlan', fn($q) => $q->where('user_id', Auth::id()))
                    ->latest('scheduled_at')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('content')
                    ->limit(50)
                    ->label('Post Content'),
                Tables\Columns\TextColumn::make('platform')
                    ->badge(),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->label('Schedule'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'scheduled',
                        'success' => 'published',
                        'danger' => 'failed',
                    ]),
            ]);
    }
}