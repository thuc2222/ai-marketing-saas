<?php

namespace App\Filament\Resources\MarketingPlanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Models\SocialAccount;
use App\Jobs\PublishSocialPostJob; // <--- QUAN TRỌNG: Import Job xử lý ngầm
use Filament\Forms\Get;
use Filament\Forms\Set;

class PostsRelationManager extends RelationManager
{
    protected static string $relationship = 'posts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // 1. CHỌN PLATFORM
                Forms\Components\Select::make('platform')
                    ->label('Platform')
                    ->options([
                        'facebook' => 'Facebook',
                        'tiktok' => 'TikTok',
                    ])
                    ->default('facebook')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('social_account_id', null);
                    })
                    ->columnSpanFull(),

                // 2. CHỌN TÀI KHOẢN
                Forms\Components\Select::make('social_account_id')
                    ->label(fn (Get $get) => $get('platform') === 'tiktok' ? 'TikTok Channel' : 'Fanpage')
                    ->options(function (Get $get) {
                        $platform = $get('platform') ?? 'facebook';
                        return SocialAccount::where('user_id', auth()->id())
                            ->where('provider', $platform)
                            ->where('is_active', true)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->columnSpanFull(),

                // 3. NỘI DUNG
                Forms\Components\Textarea::make('content')
                    ->label('Content / Script')
                    ->required()
                    ->rows(6)
                    ->columnSpanFull(),

                // 4. ẢNH (Facebook)
                Forms\Components\FileUpload::make('image_url')
                    ->label('Images (Facebook)')
                    ->multiple()
                    ->image()
                    ->reorderable()
                    ->maxFiles(10)
                    ->disk('public')
                    ->directory('post-images')
                    ->visible(fn (Get $get) => $get('platform') !== 'tiktok')
                    ->columnSpanFull(),

                // 5. VIDEO (TikTok)
                Forms\Components\FileUpload::make('video_url')
                    ->label('Video MP4 (TikTok)')
                    ->disk('public')
                    ->directory('post-videos')
                    ->acceptedFileTypes(['video/mp4', 'video/quicktime'])
                    ->maxSize(50 * 1024) // 50MB
                    ->preserveFilenames()
                    ->visible(fn (Get $get) => $get('platform') === 'tiktok')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                // CỘT PLATFORM
                Tables\Columns\TextColumn::make('platform')
                    ->badge()
                    ->colors([
                        'primary' => 'facebook',
                        'dark' => 'tiktok',
                    ]),

                // CỘT MEDIA
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Media')
                    ->disk('public')
                    ->checkFileExistence(false)
                    ->width(50)
                    ->square()
                    ->stacked()
                    ->limit(3),

                // CỘT NỘI DUNG
                Tables\Columns\TextColumn::make('content')
                    ->limit(50)
                    ->wrap(),

                // CỘT TÀI KHOẢN
                Tables\Columns\TextColumn::make('socialAccount.name')
                    ->icon('heroicon-m-user-circle')
                    ->limit(15),

                // CỘT TRẠNG THÁI (Thêm trạng thái processing)
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'processing', // Màu xanh dương cho trạng thái đang xử lý
                        'warning' => 'scheduled',
                        'success' => 'published',
                        'danger' => 'failed',
                    ]),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->dateTime('d/m H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // ACTION 1: REWRITE (AI)
                Tables\Actions\Action::make('regenerate')
                    ->label('Rewrite')
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        Notification::make()->title('Feature coming soon')->info()->send();
                    })
                    ->hidden(fn ($record) => $record->status === 'published' || $record->status === 'processing'),

                Tables\Actions\EditAction::make(),

                // ACTION 2: APPROVE
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('warning')
                    ->action(function ($record) {
                        $record->update(['status' => 'scheduled']);
                        Notification::make()->title('Approved!')->success()->send();
                    })
                    ->hidden(fn ($record) => $record->status !== 'draft'),

                // --- ACTION 3: PUBLISH NOW (DÙNG QUEUE) ---
                Tables\Actions\Action::make('publish')
                    ->label('Publish Now')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // 1. Chuyển trạng thái sang "Đang xử lý" để user biết
                        $record->update(['status' => 'processing']);

                        // 2. Đẩy việc vào hàng đợi (Queue)
                        // User sẽ không bị treo trình duyệt nữa
                        PublishSocialPostJob::dispatch($record, auth()->id());

                        // 3. Thông báo ngay lập tức
                        Notification::make()
                            ->title('Processing in background...')
                            ->body('System is uploading your post. You will be notified when finished.')
                            ->info()
                            ->send();
                    })
                    // Ẩn nút nếu đang xử lý hoặc đã đăng xong
                    ->hidden(fn ($record) => in_array($record->status, ['published', 'processing'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each->update(['status' => 'scheduled']);
                            Notification::make()->title('Approved All')->success()->send();
                        }),
                ]),
            ]);
    }
}