<?php

namespace App\Filament\Resources\MarketingPlanResource\Pages;

use App\Filament\Resources\MarketingPlanResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMarketingPlan extends CreateRecord
{
    protected static string $resource = MarketingPlanResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Redirect to Edit page immediately so user can generate content
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}