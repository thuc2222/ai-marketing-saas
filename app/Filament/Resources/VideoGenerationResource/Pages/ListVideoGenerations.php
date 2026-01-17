<?php

namespace App\Filament\Resources\VideoGenerationResource\Pages;

use App\Filament\Resources\VideoGenerationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVideoGenerations extends ListRecords
{
    protected static string $resource = VideoGenerationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
