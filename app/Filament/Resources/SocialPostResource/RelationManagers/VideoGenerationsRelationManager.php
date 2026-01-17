<?php

namespace App\Filament\Resources\SocialPostResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VideoGenerationsRelationManager extends RelationManager
{
    protected static string $relationship = 'videoGenerations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('result_url')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('video_type')->badge(),
            Tables\Columns\TextColumn::make('status')
                ->color(fn (string $state): string => match ($state) {
                    'completed' => 'success',
                    'rendering' => 'warning',
                    'failed' => 'danger',
                    default => 'gray',
                }),
            Tables\Columns\TextColumn::make('result_url')
                ->label('Download')
                ->formatStateUsing(fn ($state) => $state ? '🔗 View Video' : 'Processing...')
                ->url(fn ($state) => $state, true),
        ]);
    }
}
