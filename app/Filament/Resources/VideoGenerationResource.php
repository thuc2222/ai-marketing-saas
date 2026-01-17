<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoGenerationResource\Pages;
use App\Filament\Resources\VideoGenerationResource\RelationManagers;
use App\Models\VideoGeneration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VideoGenerationResource extends Resource
{
    protected static ?string $model = VideoGeneration::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Marketing Operations';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('socialPost.topic')->label('Chủ đề'),
            Tables\Columns\TextColumn::make('provider')->badge(),
            // Hiển thị trực tiếp trình phát video nhỏ ở danh sách
            Tables\Columns\ViewColumn::make('result_url')
                ->label('Xem trước')
                ->view('filament.tables.columns.video-player'), 
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('created_at')->dateTime(),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVideoGenerations::route('/'),
            'create' => Pages\CreateVideoGeneration::route('/create'),
            'view' => Pages\ViewVideoGeneration::route('/{record}'),
            'edit' => Pages\EditVideoGeneration::route('/{record}/edit'),
        ];
    }
}
