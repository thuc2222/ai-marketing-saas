<?php

namespace App\Filament\Resources\MarketingPlanResource\Pages;

use App\Filament\Resources\MarketingPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListMarketingPlans extends ListRecords
{
    protected static string $resource = MarketingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    // Ensure users only see their own plans
    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()->where('user_id', Auth::id());
    }
}