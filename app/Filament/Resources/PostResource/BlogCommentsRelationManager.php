<?php

namespace App\Filament\Resources\PostResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\TernaryFilter;

class BlogCommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'allComments';

    // ✅ Fix: parent class nullable string විදිහට declare කරලා තියෙනවා
    protected static ?string $inverseRelationship = 'post';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('comment')
            ->columns([
                TextColumn::make('user_name')
                    ->label('Name')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('user_email')
                    ->label('Email')
                    ->searchable()
                    ->color('gray'),

                TextColumn::make('comment')
                    ->limit(80)
                    ->wrap(),

                IconColumn::make('is_approved')
                    ->label('Approved')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_approved')->label('Approval Status'),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => !$record->is_approved)
                    ->action(fn($record) => $record->update(['is_approved' => true]))
                    ->requiresConfirmation(),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->is_approved)
                    ->action(fn($record) => $record->update(['is_approved' => false]))
                    ->requiresConfirmation(),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('approve_selected')
                    ->label('Approve Selected')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn($records) => $records->each->update(['is_approved' => true])),

                Tables\Actions\BulkAction::make('reject_selected')
                    ->label('Reject Selected')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn($records) => $records->each->update(['is_approved' => false])),

                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
