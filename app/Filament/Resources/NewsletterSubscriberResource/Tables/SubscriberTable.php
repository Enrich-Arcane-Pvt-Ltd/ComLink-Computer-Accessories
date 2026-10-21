<?php

namespace App\Filament\Resources\NewsletterSubscriberResource\Tables;

use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SubscriberTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable()
                    ->sortable()
                    ->copyable() // Admin ට ලෙහෙසියෙන්ම email එක copy කරගන්න පුළුවන්
                    ->copyMessage('Email address copied')
                    ->weight('bold'),

                IconColumn::make('is_active')
                    ->label('Subscribed Status')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Subscribed Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc') // අලුත්ම ඒවා උඩටම එන්න
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Subscription Status')
                    ->trueLabel('Active Subscribers')
                    ->falseLabel('Unsubscribed'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // Modal එකකින් විස්තර බලාගන්න විතරක් View දෙනවා
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
