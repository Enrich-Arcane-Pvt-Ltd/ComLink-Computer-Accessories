<?php

namespace App\Filament\Resources\ProductResource\Tables;

use App\Models\SystemSetting;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Model;

class ProductTable
{
    public static function table(Table $table): Table
    {
        $currency = SystemSetting::first()->currency_symbol ?? 'LKR';

        return $table
            ->columns([
                // Image එක (JSON නිසා පළමු පින්තූරය ගන්නවා)
                Tables\Columns\ImageColumn::make('images')
                    ->circular()
                    ->getStateUsing(fn($record) => is_array($record->images) ? ($record->images[0] ?? null) : null)
                    ->defaultImageUrl(url('/images/placeholder.png'))
                    ->extraImgAttributes(['style' => 'background-color: white; padding: 2px; border: 1px solid #e5e7eb;']),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn($record) => $record->sku ?? 'No SKU'),

                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Brand')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->sortable()
                    ->formatStateUsing(function (Model $record) use ($currency) {
                        if ($record->type === 'variable') {
                            $min = $record->variants->min('price');
                            $max = $record->variants->max('price');

                            if (!$min) return 'N/A';

                            if ($min == $max) {
                                return $currency . ' ' . number_format($min, 2);
                            }

                            return $currency . ' ' . number_format($min, 2) . ' - ' . number_format($max, 2);
                        }

                        return $currency . ' ' . number_format($record->price, 2);
                    }),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Stock')
                    ->badge()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        if ($record->type === 'variable') {
                            return $record->variants()->sum('stock');
                        }
                        return $record->quantity;
                    })
                    ->color(fn($state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state < 10 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'variable' => 'purple',
                        'simple' => 'info',
                    }),

                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->color('warning'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('brand')
                    ->relationship('brand', 'name'),

                SelectFilter::make('type')
                    ->options([
                        'simple' => 'Simple Product',
                        'variable' => 'Variable Product',
                    ]),

                TernaryFilter::make('is_visible')
                    ->label('Visibility')
                    ->trueLabel('Visible Only')
                    ->falseLabel('Hidden Only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
