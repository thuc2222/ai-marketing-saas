<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use App\Models\EmailList;
use App\Models\User;
use App\Settings\GeneralSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use OpenAI\Laravel\Facades\OpenAI;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;
    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';
    protected static ?string $navigationGroup = 'Marketing Operations';
    protected static ?string $navigationLabel = 'Email Campaigns';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Campaign Details'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('e.g. Monthly Newsletter - Jan')),
                        
                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('Subject line of the email')),

                        // --- AI CONTENT EDITOR ---
                        Forms\Components\RichEditor::make('content')
                            ->label(__('Email Content'))
                            ->required()
                            ->columnSpanFull()
                            ->hintAction(
                                Forms\Components\Actions\Action::make('generate_ai')
                                    ->label(__('✨ Generate with AI'))
                                    ->icon('heroicon-o-sparkles')
                                    ->color(Color::Purple)
                                    ->form([
                                        Forms\Components\Textarea::make('prompt')
                                            ->label(__('What is this email about?'))
                                            ->placeholder(__('Write a promotional email for our new summer sale, 20% off all items.'))
                                            ->required(),
                                        Forms\Components\Select::make('tone')
                                            ->options([
                                                'professional' => __('Professional'),
                                                'friendly' => __('Friendly'),
                                                'urgent' => __('Urgent / Sales'),
                                            ])
                                            ->default('friendly')
                                    ])
                                    ->action(function (array $data, Forms\Set $set) {
                                        // Call OpenAI API
                                        $settings = app(GeneralSettings::class);
                                        
                                        if (!$settings->openai_api_key) {
                                            Notification::make()->title(__('OpenAI API Key missing in settings'))->danger()->send();
                                            return;
                                        }

                                        try {
                                            $client = \OpenAI::factory()->withApiKey($settings->openai_api_key)->make();
                                            $response = $client->chat()->create([
                                                'model' => 'gpt-4o-mini', // or gpt-3.5-turbo
                                                'messages' => [
                                                    ['role' => 'system', 'content' => "You are an expert email copywriter. Write HTML email content. Tone: {$data['tone']}. Only output the HTML body content, no markdown."],
                                                    ['role' => 'user', 'content' => $data['prompt']],
                                                ],
                                            ]);

                                            $aiContent = $response->choices[0]->message->content;
                                            $set('content', $aiContent);
                                            
                                            Notification::make()->title(__('Content generated successfully!'))->success()->send();
                                        } catch (\Exception $e) {
                                            Notification::make()->title(__('AI Error: {message}', ['message' => $e->getMessage()]))->danger()->send();
                                        }
                                    })
                            ),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'processing' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('sent_count')
                    ->label('Sent')
                    ->sortable(),
                Tables\Columns\TextColumn::make('open_rate')
                    ->label('Open Rate')
                    ->state(fn (Campaign $record) => $record->sent_count > 0 
                        ? round(($record->open_count / $record->sent_count) * 100, 1) . '%' 
                        : '0%'),
                Tables\Columns\TextColumn::make('bounce_rate')
                    ->label('Bounce Rate')
                    ->color('danger')
                    ->state(fn (Campaign $record) => $record->sent_count > 0 
                        ? round(($record->bounce_count / $record->sent_count) * 100, 1) . '%' 
                        : '0%'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                // --- SENDING ACTION WITH CREDIT DEDUCTION ---
                Tables\Actions\Action::make('send')
                    ->label('Send Campaign')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Select::make('email_list_id')
                            ->label('Select Subscriber List')
                            ->options(EmailList::pluck('name', 'id')) // In real app, filter by auth user
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $count = \App\Models\Subscriber::where('email_list_id', $state)
                                    ->where('status', 'active')
                                    ->count();
                                $set('recipient_count', $count);
                                $set('credits_needed', ceil($count / 100)); // 100 emails = 1 Credit
                            }),
                        
                        Forms\Components\TextInput::make('recipient_count')
                            ->label('Total Recipients')
                            ->disabled(),
                            
                        Forms\Components\TextInput::make('credits_needed')
                            ->label('Credits Required')
                            ->helperText('Rate: 1 Credit per 100 Emails')
                            ->disabled(),
                    ])
                    ->action(function (Campaign $record, array $data) {
                        $user = auth()->user();
                        $listId = $data['email_list_id'];
                        $recipients = \App\Models\Subscriber::where('email_list_id', $listId)
                                        ->where('status', 'active')
                                        ->get();
                        
                        $count = $recipients->count();
                        if ($count === 0) {
                            Notification::make()->title('Selected list is empty.')->warning()->send();
                            return;
                        }

                        // Credit Calculation
                        $cost = ceil($count / 100);

                        if ($user->credits < $cost) {
                            Notification::make()
                                ->title('Insufficient Credits')
                                ->body("You need {$cost} credits but have {$user->credits}.")
                                ->danger()
                                ->send();
                            return;
                        }

                        // Deduct Credits & Update Campaign
                        DB::transaction(function () use ($user, $cost, $record, $count) {
                            $user->decrement('credits', $cost);
                            
                            $record->update([
                                'status' => 'processing',
                                'total_recipients' => $count,
                                'sent_count' => 0, // Will update as queue runs
                            ]);
                            
                            // Dispatch Job (Mockup logic)
                            // In production: SendCampaignJob::dispatch($record, $recipients);
                            
                            // For Demo: Simulate sending
                            $record->update(['status' => 'completed', 'sent_count' => $count]);
                        });

                        Notification::make()
                            ->title('Campaign Sent!')
                            ->body("{$cost} credits deducted. Emails are being sent.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Campaign $record) => $record->status === 'draft'),
            ]);
    }

    // --- REPORTING VIEW (Infolist) ---
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Performance Report')
                    ->schema([
                        Infolists\Components\Grid::make(4)->schema([
                            Infolists\Components\TextEntry::make('sent_count')
                                ->label('Total Sent')
                                ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                
                            Infolists\Components\TextEntry::make('open_count')
                                ->label('Opened')
                                ->color('success'),
                                
                            Infolists\Components\TextEntry::make('click_count')
                                ->label('Clicked Link')
                                ->color('info'),
                                
                            Infolists\Components\TextEntry::make('bounce_count')
                                ->label('Bounced')
                                ->color('danger'),
                        ]),
                        
                        Infolists\Components\ViewEntry::make('html_preview')
                            ->view('filament.components.campaign-preview') // You need to create this simple blade
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
            'view' => Pages\ViewCampaign::route('/{record}'),
        ];
    }
}