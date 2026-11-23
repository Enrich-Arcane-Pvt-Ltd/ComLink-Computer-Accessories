<?php

namespace App\Filament\Resources\ChatbotLogResource;

use App\Filament\Resources\ChatbotLogResource\Pages;
use App\Models\ChatbotLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChatbotLogResource extends Resource
{
    protected static ?string $model = ChatbotLog::class;

    protected static ?string $navigationGroup = 'System Management';

    protected static ?string $navigationLabel = 'Chatbot Query Logs';

    protected static ?string $modelLabel = 'Chatbot Log';

    protected static ?string $pluralModelLabel = 'Chatbot Query Logs';

    protected static ?string $slug = 'system/chatbot-logs';

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(ChatbotLog::query()->with('user'))
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user_name')
                    ->label('User')
                    ->default('Guest')
                    ->searchable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('message')
                    ->label('Customer Question')
                    ->searchable()
                    ->wrap()
                    ->limit(100),

                Tables\Columns\TextColumn::make('resolution_type')
                    ->label('Handled By')
                    ->badge()
                    ->color(fn (string $state) => $state === 'gemini_ai' ? 'success' : 'warning')
                    ->formatStateUsing(fn (string $state) => $state === 'gemini_ai' ? 'Gemini AI' : 'Fallback'),

                Tables\Columns\TextColumn::make('bot_reply')
                    ->label('Bot Reply')
                    ->wrap()
                    ->limit(110),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Asked At')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('resolution_type')
                    ->label('Handled By')
                    ->options([
                        'gemini_ai' => 'Gemini AI',
                        'fallback' => 'Unmatched Fallback',
                    ]),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChatbotLogs::route('/'),
        ];
    }
}

