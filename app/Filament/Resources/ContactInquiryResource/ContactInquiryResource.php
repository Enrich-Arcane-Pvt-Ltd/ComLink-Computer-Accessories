<?php

namespace App\Filament\Resources\ContactInquiryResource;

use App\Models\ContactInquiry;
use App\Notifications\ContactInquiryReplyNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Notification;

class ContactInquiryResource extends Resource
{
    protected static ?string $model = ContactInquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Customer Relations';

    protected static ?string $pluralLabel = 'Contact Inquiries';

    // Form එක සාමාන්‍යයෙන් Edit කරන්න නොදෙන නිසා හිස්ව තබයි (අපි පාවිච්චි කරන්නේ Infolist / View)
    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    // 📋 Table එක (List View)
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-clipboard'),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'replied' => 'success',
                        'spam' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'replied' => 'heroicon-o-check-circle',
                        'spam' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Received At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make()->label('Open Message'),

                // ✉️ Custom Reply Action (Filament Magic Modal)
                Tables\Actions\Action::make('reply')
                    ->icon('heroicon-o-arrow-turn-up-left')
                    ->color('success')
                    ->modalHeading(fn (ContactInquiry $record) => "Reply to: {$record->name}")
                    ->modalDescription('This will send an official email response directly to the customer.')
                    ->form([
                        Forms\Components\Textarea::make('reply_message')
                            ->label('Your Email Response')
                            ->rows(6)
                            ->required()
                            ->placeholder('Write your reply message here...')
                            ->helperText('Markdown formatting is supported.'),
                    ])
                    // රිප්ලයි කරලා ඉවර නම් බටන් එක ඩිසේබල් කරන්න පුළුවන්
                    ->disabled(fn (ContactInquiry $record) => $record->status === 'replied')
                    ->action(function (ContactInquiry $record, array $data): void {

                        // 1. Customer හට ඊමේල් Notification එක යැවීම
                        Notification::route('mail', $record->email)
                            ->notify(new ContactInquiryReplyNotification($record, $data['reply_message']));

                        // 2. ඩේටාබේස් එක යාවත්කාලීන කිරීම
                        $record->update([
                            'status' => 'replied',
                            'reply_message' => $data['reply_message'],
                            'replied_at' => now(),
                        ]);

                        // 3. Success Toast එකක් පෙන්වීම
                        \Filament\Notifications\Notification::make()
                            ->title('Reply Sent Successfully')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    // 👀 Detail View එක ලස්සනට පෙන්වන්න Infolist එකක්
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Inquiry Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')->weight('bold'),
                        Infolists\Components\TextEntry::make('email')->icon('heroicon-m-envelope'),
                        Infolists\Components\TextEntry::make('phone')->icon('heroicon-m-phone'),
                        Infolists\Components\TextEntry::make('created_at')->label('Received At')->dateTime(),
                        Infolists\Components\TextEntry::make('subject')->columnSpanFull()->weight('bold'),
                        Infolists\Components\TextEntry::make('message')
                            ->label('Customer Message')
                            ->columnSpanFull()
                            ->prose()
                            ->extraAttributes([
                                'style' => 'background-color: rgba(0,0,0,0.03); padding: 15px; border-radius: 8px;',
                            ]),
                    ])->columns(2),

                Infolists\Components\Section::make('Official Response Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'replied' => 'success',
                                'spam' => 'danger',
                            }),
                        Infolists\Components\TextEntry::make('replied_at')
                            ->placeholder('Not replied yet')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('reply_message')
                            ->label('Sent Response')
                            ->columnSpanFull()
                            ->placeholder('No response sent yet.')
                            ->prose(),
                    ])->columns(2)->visible(fn (ContactInquiry $record) => $record->status === 'replied'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactInquiries::route('/'),
            'view' => Pages\ViewContactInquiry::route('/{record}'),
        ];
    }
}
