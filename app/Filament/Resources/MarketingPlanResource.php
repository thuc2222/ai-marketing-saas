<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketingPlanResource\Pages;
use App\Filament\Resources\MarketingPlanResource\RelationManagers;
use App\Models\MarketingPlan;
use App\Models\SocialPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Actions\Action; 
use Carbon\Carbon;
use OpenAI\Laravel\Facades\OpenAI;

class MarketingPlanResource extends Resource
{
    protected static ?string $model = MarketingPlan::class;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Marketing Strategy';
    protected static ?string $navigationGroup = 'Marketing Operations';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // BƯỚC 1: ĐỊNH VỊ
                    Wizard\Step::make('Brand & Strategy')
                        ->label(__('Brand & Strategy'))
                        ->icon('heroicon-o-flag')
                        ->schema([
                            Forms\Components\TextInput::make('name')->required()->columnSpanFull(),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('campaign_goal')
                                    ->options(['awareness'=>'Awareness','conversion'=>'Sales','traffic'=>'Traffic'])
                                    ->required(),
                                Forms\Components\TextInput::make('brand_voice'),
                            ]),
                            Forms\Components\Textarea::make('target_audience')->rows(3)->columnSpanFull(),
                        ]),

                    // BƯỚC 2: PILLARS
                    Wizard\Step::make('Content Pillars')
                        ->label(__('Content Pillars'))
                        ->icon('heroicon-o-rectangle-stack')
                        ->schema([
                            Forms\Components\Repeater::make('content_pillars')->schema([
                                Forms\Components\TextInput::make('theme')->required(),
                                Forms\Components\TextInput::make('percent')->numeric()->default(30)->suffix('%'),
                                Forms\Components\Textarea::make('key_message')->columnSpanFull(),
                            ])->columns(2)->defaultItems(3),
                        ]),
                    
                    // BƯỚC 3: OMNI-CHANNEL (MỚI)
                    Wizard\Step::make('Channels & KPIs')
                        ->label(__('Omni-channel'))
                        ->icon('heroicon-o-share')
                        ->schema([
                            Forms\Components\Section::make('Channel Mix')
                                ->schema([
                                    Forms\Components\CheckboxList::make('channels')
                                        ->label(__('Select Channels'))
                                        ->options([
                                            'facebook' => 'Facebook / Meta',
                                            'tiktok' => 'TikTok (Video)',
                                            'website' => 'Website Blog (SEO)',
                                            'email' => 'Email Newsletter',
                                            'offline' => 'Offline / POSM',
                                        ])
                                        ->columns(3)
                                        ->required(),
                                ]),
                            Forms\Components\Section::make('Success Metrics')
                                ->schema([
                                    Forms\Components\KeyValue::make('kpi_targets')
                                        ->label(__('KPI Targets'))
                                        ->keyLabel('Metric (e.g., Reach)')
                                        ->valueLabel('Target (e.g., 10,000)')
                                        ->default(['Reach' => '10000', 'Leads' => '50']),
                                ]),
                        ]),

                    // BƯỚC 4: TÀI CHÍNH
                    Wizard\Step::make('Financials & ROI')
                        ->label(__('Financials'))
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                             Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('budget')->numeric()->live(onBlur: true),
                                Forms\Components\TextInput::make('expected_revenue')->numeric()->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Get $get, Set $set) => $set('estimated_roi', $get('budget') > 0 ? (($state - $get('budget')) / $get('budget') * 100) : 0)),
                                Forms\Components\TextInput::make('estimated_roi')->disabled()->suffix('%')->dehydrated(),
                             ]),
                             Forms\Components\Grid::make(2)->schema([
                                Forms\Components\DatePicker::make('start_date')->required(),
                                Forms\Components\DatePicker::make('end_date')->required(),
                             ]),
                        ]),
                ])->columnSpanFull()->skippable(false)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('channels')
                    ->badge()
                    ->separator(',')
                    ->limitList(3),

                Tables\Columns\TextColumn::make('budget')
                    ->money('USD'),

                Tables\Columns\TextColumn::make('estimated_roi')
                    ->suffix('%')
                    // SỬA LẠI DÒNG NÀY: Đổi $s thành $state
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // 1. REPORTING & ANALYTICS (REAL DATA)
                Section::make(__('Performance Report'))
                    ->icon('heroicon-o-chart-bar')
                    ->columns(4)
                    ->schema([
                        // NGÂN SÁCH ĐÃ CHI
                        TextEntry::make('budget_spent')
                            ->label(__('Spend'))
                            ->getStateUsing(function (MarketingPlan $record) {
                                // Logic: Cộng tổng chi phí từ bảng ads_insights (sau này sẽ làm)
                                // Hiện tại trả về 0
                                return '$0.00'; 
                            })
                            ->color('gray'),
                        
                        // LƯỢT TIẾP CẬN THỰC
                        TextEntry::make('actual_reach')
                            ->label(__('Real Reach'))
                            ->getStateUsing(fn() => '0') // Mặc định là 0
                            ->icon('heroicon-m-eye')
                            ->color('gray'),

                        // KHÁCH HÀNG TIỀM NĂNG (LEADS)
                        TextEntry::make('leads_generated')
                            ->label(__('Leads'))
                            ->getStateUsing(fn() => '0')
                            ->weight('bold'),

                        // ROI THỰC TẾ
                        TextEntry::make('roi_real')
                            ->label(__('Real ROI'))
                            ->getStateUsing(fn() => 'N/A') // Chưa có số liệu
                            ->badge()
                            ->color('gray'),
                    ]),

                // 2. AI STRATEGY
                Section::make(__('AI Strategic Roadmap'))
                    ->headerActions([
                        Action::make('generate_strategy')
                        ->label(__('Generate Roadmap'))
                        ->icon('heroicon-m-bolt')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('AI Strategic Analysis')
                        ->modalDescription('Hệ thống sẽ gửi toàn bộ dữ liệu Plan sang OpenAI (GPT-4) để phân tích. Quá trình này có thể mất 10-20 giây.')
                        ->action(function (MarketingPlan $record) {
                            // 1. Kiểm tra dữ liệu đầu vào
                            if (empty($record->content_pillars)) {
                                Notification::make()->title('Vui lòng nhập Content Pillars trước!')->warning()->send();
                                return;
                            }

                            try {
                                // 2. Xây dựng Prompt (Kịch bản) cho AI
                                // Biến mảng JSON thành chuỗi dễ đọc
                                $pillarsText = collect($record->content_pillars)
                                    ->map(fn($p) => "- {$p['theme']} ({$p['percent']}%): {$p['key_message']}")
                                    ->implode("\n");
                                
                                $channelsText = is_array($record->channels) ? implode(', ', $record->channels) : 'All channels';
                                
                                $systemPrompt = "You are a world-class Chief Marketing Officer (CMO). 
                                Your task is to create a strategic execution roadmap for a marketing campaign.
                                Output format: Markdown (Use H3, H4, Bullet points, Bold text for emphasis).
                                Language: Vietnamese (Tiếng Việt).";

                                $userPrompt = "
                                CAMPAIGN DETAILS:
                                - Name: {$record->name}
                                - Goal: {$record->campaign_goal}
                                - Budget: {$record->budget} USD
                                - Channels: {$channelsText}
                                - Target Audience: {$record->target_audience}
                                - Brand Voice: {$record->brand_voice}
                                
                                CONTENT PILLARS:
                                {$pillarsText}

                                REQUEST:
                                1. **Executive Summary**: Đánh giá ngắn gọn về tiềm năng chiến dịch.
                                2. **Phased Roadmap**: Lập lộ trình 3 giai đoạn (GĐ1: Trigger/Awareness, GĐ2: Engagement, GĐ3: Conversion). Mỗi giai đoạn cần nêu rõ: Mục tiêu, Hành động chính trên từng kênh, và KPI kỳ vọng.
                                3. **Budget Allocation**: Gợi ý phân bổ ngân sách {$record->budget}$ cho các kênh sao cho hiệu quả nhất.
                                4. **Risk Management**: 3 rủi ro tiềm ẩn và cách phòng tránh.
                                ";

                                // 3. Gọi API OpenAI
                                // Lưu ý: Dùng gpt-4o hoặc gpt-4-turbo để thông minh nhất. Dùng gpt-3.5-turbo để tiết kiệm.
                                $result = OpenAI::chat()->create([
                                    'model' => 'gpt-4o', // Hoặc 'gpt-3.5-turbo'
                                    'messages' => [
                                        ['role' => 'system', 'content' => $systemPrompt],
                                        ['role' => 'user', 'content' => $userPrompt],
                                    ],
                                    'temperature' => 0.7, // 0.7 để AI sáng tạo vừa đủ
                                ]);

                                // 4. Lấy kết quả và lưu vào Database
                                $content = $result->choices[0]->message->content;
                                
                                $record->update([
                                    'ai_strategy_advice' => $content
                                ]);

                                Notification::make()
                                    ->title('AI Analysis Completed!')
                                    ->success()
                                    ->send();

                            } catch (\Exception $e) {
                                // Xử lý lỗi (VD: Hết tiền, sai Key, mất mạng)
                                Notification::make()
                                    ->title('AI Error')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                        // LOGIC TRIỂN KHAI ĐA KÊNH THÔNG MINH
                        Action::make('execute_plan')
                            ->label(__('⚡ Deploy Omni-channel'))
                            ->icon('heroicon-o-rocket-launch')
                            ->color('success')
                            ->requiresConfirmation()
                            ->modalHeading('Deploy Plan')
                            ->modalDescription('System will generate tailored content for each selected channel (Scripts for TikTok, Articles for Blog, etc).')
                            ->action(function (MarketingPlan $record) {
                                if (empty($record->content_pillars) || empty($record->channels)) {
                                    Notification::make()->title('Missing Pillars or Channels!')->danger()->send();
                                    return;
                                }
                                
                                $count = 0;
                                foreach ($record->content_pillars as $pillar) {
                                    foreach ($record->channels as $channel) {
                                        // Tùy biến định dạng nội dung theo kênh
                                        $format = match ($channel) {
                                            'tiktok' => 'Video Script (Kịch bản ngắn)',
                                            'website' => 'Blog Article (SEO chuẩn)',
                                            'email' => 'Email Newsletter',
                                            'offline' => 'Design Brief (Standee/Poster)',
                                            default => 'Social Post (Caption + Image)',
                                        };

                                        $promptContext = "Draft {$format} for topic: " . ($pillar['theme'] ?? 'General');

                                        SocialPost::create([
                                            'user_id' => auth()->id(),
                                            'marketing_plan_id' => $record->id,
                                            'platform' => $channel, // Lưu kênh vào cột platform
                                            'content' => $promptContext,
                                            'topic' => $pillar['theme'] ?? 'General',
                                            'status' => 'draft',
                                            'scheduled_at' => now()->addDays($count),
                                        ]);
                                        $count++;
                                    }
                                }
                                Notification::make()->title("Deployed {$count} content items across all channels!")->success()->send();
                                return redirect(request()->header('Referer'));
                            }),
                    ])
                    ->schema([
                        TextEntry::make('ai_strategy_advice')->hiddenLabel()->markdown()->prose(),
                    ]),

                // 3. PILLARS & TARGETS
                Section::make(__('Strategy Details'))
                    ->columns(2)
                    ->schema([
                        RepeatableEntry::make('content_pillars')
                            ->label(__('Pillars'))
                            ->schema([
                                TextEntry::make('theme')->weight('bold')->color('primary'),
                                TextEntry::make('percent')->suffix('%')->badge(),
                            ])->grid(2),

                        KeyValueEntry::make('kpi_targets')
                            ->label(__('KPI Targets')),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PostsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketingPlans::route('/'),
            'create' => Pages\CreateMarketingPlan::route('/create'),
            'view' => Pages\ViewMarketingPlan::route('/{record}'),
            'edit' => Pages\EditMarketingPlan::route('/{record}/edit'),
        ];
    }
}