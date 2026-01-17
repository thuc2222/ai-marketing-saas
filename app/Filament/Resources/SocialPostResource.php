<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialPostResource\Pages;
use App\Models\SocialPost;
use App\Models\MarketingPlan;
use App\Settings\VideoSettings; // THÊM DÒNG NÀY
use App\Services\VideoFactoryService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\HtmlString;

class SocialPostResource extends Resource
{
    protected static ?string $model = SocialPost::class;
    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    protected static ?string $navigationGroup = 'Marketing Operations';
    protected static ?string $navigationLabel = 'Social Posts';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Post Details'))
                            ->schema([
                                Forms\Components\Select::make('marketing_plan_id')
                                    ->relationship('marketingPlan', 'name')
                                    ->label(__('Campaign / Plan'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('topic')
                                        ->label(__('Topic / Theme'))
                                        ->required()
                                        ->maxLength(255),
                                    
                                    Forms\Components\Select::make('platform')
                                        ->label(__('Platform / Channel'))
                                        ->options([
                                            'facebook' => __('Facebook'),
                                            'instagram' => __('Instagram'),
                                            'tiktok' => __('TikTok (Video)'),
                                            'youtube_shorts' => __('YouTube Shorts'),
                                            'linkedin' => __('LinkedIn'),
                                            'website' => __('Website / Blog'),
                                            'email' => __('Email Newsletter'),
                                        ])
                                        ->required()
                                        ->native(false),
                                ]),

                                Forms\Components\MarkdownEditor::make('content')
                                    ->label(__('Content / Script'))
                                    ->columnSpanFull()
                                    ->required()
                                    ->hintAction(
                                        // === AI AUTO WRITE ACTION ===
                                        Forms\Components\Actions\Action::make('ai_write')
                                            ->label(__('✨ Auto-Write with AI'))
                                            ->requiresConfirmation()
                                            ->action(function (Forms\Get $get, Forms\Set $set) {
                                                $topic = $get('topic');
                                                $platform = $get('platform');
                                                $planId = $get('marketing_plan_id');
                                                
                                                if (!$topic || !$planId) {
                                                    Notification::make()->title(__('Missing Topic or Campaign!'))->warning()->send();
                                                    return;
                                                }

                                                $plan = MarketingPlan::find($planId);
                                                $brandVoice = $plan->brand_voice ?? 'Professional';
                                                
                                                try {
                                                    $response = OpenAI::chat()->create([
                                                        'model' => 'gpt-4o-mini',
                                                        'messages' => [
                                                            ['role' => 'system', 'content' => "Write content for {$platform} about {$topic}. Tone: {$brandVoice}. Language: Vietnamese."],
                                                        ],
                                                    ]);
                                                    $set('content', $response->choices[0]->message->content);
                                                    Notification::make()->title(__('AI Generated!'))->success()->send();
                                                } catch (\Exception $e) {
                                                    Notification::make()->title(__('Error: {message}', ['message' => $e->getMessage()]))->danger()->send();
                                                }
                                            })
                                    ),
                            ]),
                    ])->columnSpan(2),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Status'))
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'draft' => __('Draft'),
                                        'scheduled' => __('Scheduled'),
                                        'published' => __('Published'),
                                    ])->default('draft'),
                            ]),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('marketingPlan.name')->label(__('Campaign')),
                Tables\Columns\TextColumn::make('topic')->weight('bold'),
                Tables\Columns\TextColumn::make('platform')->badge(),
                // HIỂN THỊ TRẠNG THÁI VIDEO ĐỂ USER THEO DÕI
                Tables\Columns\TextColumn::make('video_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ready' => 'success',
                        'rendering' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // === ACTION BUILD VIDEO ĐÃ FIX LỖI ===
                Tables\Actions\Action::make('build_video')
                    ->label(__('Build AI Video'))
                    ->icon('heroicon-o-video-camera')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('video_type')
                            ->label(__('Video Package'))
                            ->options([
                                'social_short' => __('Social Short (Budget)'),
                                'cinematic_pro' => __('Cinematic Pro (Premium)'),
                            ])->required(),
                    ])
                    ->action(function (SocialPost $record, array $data, VideoFactoryService $service) {
                        $settings = app(VideoSettings::class); // Lấy đối tượng Settings
                        $user = auth()->user();
                        $price = (int) $settings->{"price_{$data['video_type']}"}; // Lấy giá từ Settings

                        if ($user->credits < $price) {
                            Notification::make()->title(__('Insufficient Credits!'))->danger()->send();
                            return;
                        }

                        // 1. Trừ tiền
                        $user->decrement('credits', $price);

                        // 2. Đánh dấu đang render
                        $record->update(['video_status' => 'rendering']);

                        // 3. GỌI SERVICE (Fix lỗi Type Error: Truyền $settings thay vì $price)
                        $service->produceVideo($record, $data['video_type'], $settings);

                        Notification::make()
                            ->title(__('Video production started!'))
                            ->success()
                            ->send();
                    })
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSocialPosts::route('/'),
            'create' => Pages\CreateSocialPost::route('/create'),
            'edit' => Pages\EditSocialPost::route('/{record}/edit'),
        ];
    }
}