<?php

namespace App\Filament\Resources\GrnResource\Tables;

use App\Models\Grn;
use Filament\Tables;
use Filament\Tables\Table;

class GrnTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('grn_number')
                    ->label('Purchase GRN Invoice No')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->fontFamily('mono')
                    ->color('primary')
                    ->copyable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier Name')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-truck')
                    ->iconColor('success')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('purchaseOrder.po_number')
                    ->label('Purchase Order No')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->placeholder('Direct'),

                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items Count')
                    ->suffix(' items')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sub_total')
                    ->label('Sub Total')
                    ->formatStateUsing(fn ($state) => 'Rs. '.number_format((float) $state, 2))
                    ->weight('bold')
                    ->fontFamily('mono')
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created Date & Time')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('print')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->url(fn (Grn $record): string => route('admin.grn.print', $record->id))
                    ->openUrlInNewTab(),

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
