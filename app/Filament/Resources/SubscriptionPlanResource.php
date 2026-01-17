<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionPlanResource\Pages;
use App\Models\SubscriptionPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Account Setup';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Chi tiết Gói cước')
                    ->description('Cấu hình giá và số lượng Credits tặng kèm.')
                    ->schema([
                        // 1. Tên gói
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                $operation === 'create' ? $set('slug', Str::slug($state)) : null
                            ),

                        // 2. Slug (URL)
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->unique(SubscriptionPlan::class, 'slug', ignoreRecord: true),

                        // 3. Giá tiền ($)
                        Forms\Components\TextInput::make('price')
                            ->label('Giá tiền ($)')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->helperText('Nhập 0 nếu là gói Free.'),

                        // 4. Credits (Quan trọng nhất)
                        Forms\Components\TextInput::make('monthly_credits')
                            ->label('Credits tặng mỗi tháng')
                            ->numeric()
                            ->default(100)
                            ->required()
                            ->helperText('Ví dụ: 100 Credits = 100 bài viết AI.'),

                        // 5. Stripe ID (Cho tương lai)
                        Forms\Components\TextInput::make('stripe_price_id')
                            ->label('Stripe Price ID')
                            ->placeholder('price_1Nx...')
                            ->helperText('Dùng để kết nối thanh toán Stripe sau này.'),

                        // 6. Trạng thái
                        Forms\Components\Toggle::make('is_active')
                            ->label('Đang mở bán')
                            ->default(true)
                            ->columnSpanFull(),
                    ])->columns(2),

                // 7. Danh sách tính năng (JSON Key-Value)
                Forms\Components\Section::make('Quyền lợi hiển thị')
                    ->schema([
                        Forms\Components\KeyValue::make('features')
                            ->label('Danh sách tính năng')
                            ->keyLabel('Tên tính năng')
                            ->valueLabel('Giá trị (VD: Có, Không, Vô hạn)')
                            ->addActionLabel('Thêm dòng')
                            ->reorderable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('monthly_credits')
                    ->label('Credits')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'edit' => Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
{
    return auth()->user()->role === 'admin';
}
}