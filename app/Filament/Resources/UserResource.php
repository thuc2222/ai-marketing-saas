<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'System Management'; // Gom nhóm quản lý
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                        
                        // Phân quyền
                        Forms\Components\Select::make('role')
                            ->options([
                                'user' => 'Customer',
                                'admin' => 'Administrator',
                            ])
                            ->default('user')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Subscription & Credits')
                    ->schema([
                        // Chọn gói cước
                        Forms\Components\Select::make('subscription_plan_id')
                            ->relationship('subscriptionPlan', 'name')
                            ->label('Current Plan')
                            ->searchable()
                            ->preload(),

                        // Chỉnh sửa Credits trực tiếp
                        Forms\Components\TextInput::make('credits')
                            ->numeric()
                            ->default(0)
                            ->label('Available Credits')
                            ->helperText('Credits will be deducted when generating AI content.'),
                            
                        Forms\Components\DateTimePicker::make('subscription_expires_at')
                            ->label('Plan Expiry Date'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Avatar + Tên
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => $record->getFilamentAvatarUrl()), // Dùng hàm avatar custom

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (User $record) => $record->email),

                // Hiển thị Credits
                Tables\Columns\TextColumn::make('credits')
                    ->badge()
                    ->color(fn (int $state): string => $state > 10 ? 'success' : 'danger')
                    ->sortable(),

                // Hiển thị Gói cước
                Tables\Columns\TextColumn::make('subscriptionPlan.name')
                    ->label('Plan')
                    ->badge()
                    ->color('info')
                    ->placeholder('Free User'),

                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'admin' ? 'danger' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Lọc theo gói cước
                Tables\Filters\SelectFilter::make('subscription_plan_id')
                    ->label('Filter by Plan')
                    ->relationship('subscriptionPlan', 'name'),
                    
                // Lọc Admin/User
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'user' => 'Customer',
                        'admin' => 'Administrator',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // ACTION: CỘNG TIỀN NHANH (Add Credits)
                Action::make('add_credits')
                    ->label('Add Credits')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount to add')
                            ->numeric()
                            ->required()
                            ->default(10)
                            ->suffix('credits'),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->increment('credits', $data['amount']);
                        
                        Notification::make()
                            ->title('Credits Added')
                            ->body("Added {$data['amount']} credits to {$record->name}")
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}