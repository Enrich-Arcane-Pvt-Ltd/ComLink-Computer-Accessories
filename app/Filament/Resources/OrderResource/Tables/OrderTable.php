<?php

namespace App\Filament\Resources\OrderResource\Tables;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;

class OrderTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1st column: Product Image (left) + Order Number & units/products count (right)
                Tables\Columns\ViewColumn::make('order_info')
                    ->label('Order Details')
                    ->view('filament.tables.columns.order-info')
                    ->searchable(query: fn ($query, $search) => $query->where('order_number', 'like', "%{$search}%")),

                // 2nd column: User Name
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('User Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                // 3rd column: Total Amount
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->formatStateUsing(fn ($state) => 'Rs. '.number_format((float) $state, 2))
                    ->sortable()
                    ->weight('bold')
                    ->color('info'),

                // 4th column: Order Status
                Tables\Columns\SelectColumn::make('status')
                    ->label('Order Status')
                    ->options([
                        'Processing' => 'Processing',
                        'Packaging' => 'Packaging',
                        'Shipping' => 'Shipping',
                        'Delivered' => 'Delivered',
                    ])
                    ->selectablePlaceholder(false)
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Placed At')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Processing' => 'Processing',
                        'Packaging' => 'Packaging',
                        'Shipping' => 'Shipping',
                        'Delivered' => 'Delivered',
                    ]),
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
