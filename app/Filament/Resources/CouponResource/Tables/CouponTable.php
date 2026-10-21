<?php

namespace App\Filament\Resources\CouponResource\Tables;

use App\Models\SystemSetting;
use Filament\Tables;
use Filament\Tables\Table;

class CouponTable
{
    public static function table(Table $table): Table
    {
        $currency = SystemSetting::first()->currency_symbol ?? 'LKR';

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->weight('bold')
                    ->copyable()
                    ->searchable()
                    ->icon('heroicon-m-ticket'),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fixed' => 'info',
                        'percent' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => $state === 'fixed' ? 'Fixed Amount' : 'Percentage'),

                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->formatStateUsing(function ($record) use ($currency) {
                        return $record->type === 'percent'
                            ? $record->value.'%'
                            : $currency.' '.number_format($record->value, 2);
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('usage_limit')
                    ->label('Usage')
                    ->formatStateUsing(
                        fn ($record) => $record->used_count.' / '.($record->usage_limit ?? '∞')
                    )
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Expiry')
                    ->date()
                    ->sortable()
                    ->placeholder('Never Expires')
                    ->color(fn ($state) => $state && now()->gt($state) ? 'danger' : 'success'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'fixed' => 'Fixed Amount',
                        'percent' => 'Percentage',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active Coupons')
                    ->falseLabel('Inactive Coupons'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // View Action
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
