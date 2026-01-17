<?php

namespace App\Filament\Resources\MarketingPlanResource\Pages;

use App\Filament\Resources\MarketingPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarketingPlan extends EditRecord
{
    protected static string $resource = MarketingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}