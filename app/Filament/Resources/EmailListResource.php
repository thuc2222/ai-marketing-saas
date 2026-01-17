<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailListResource\Pages;
use App\Models\EmailList;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailListResource extends Resource
{
    protected static ?string $model = EmailList::class;
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationGroup = 'Marketing Operations';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                // Quản lý Subscribers ngay trong trang Edit List
                Forms\Components\Repeater::make('subscribers')
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('email')->email()->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'unsubscribed' => 'Unsubscribed',
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columnSpanFull()
                    ->grid(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('subscribers_count')
                    ->counts('subscribers')
                    ->label('Total Subscribers'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailLists::route('/'),
            'create' => Pages\CreateEmailList::route('/create'),
            'edit' => Pages\EditEmailList::route('/{record}/edit'),
        ];
    }
}