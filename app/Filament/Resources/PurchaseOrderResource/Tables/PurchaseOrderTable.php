<?php

namespace App\Filament\Resources\PurchaseOrderResource\Tables;

use App\Filament\Resources\GrnResource\GrnResource;
use App\Models\PurchaseOrder;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseOrderTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('po_number')
                    ->label('Purchase Order No')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->fontFamily('mono')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier Name')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-truck')
                    ->iconColor('success')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Purchase Order Status')
                    ->badge()
                    ->colors([
                        'warning' => 'Planning',
                        'success' => 'Approved',
                        'danger' => 'Rejected',
                    ])
                    ->sortable(),

                Tables\Columns\ViewColumn::make('order_details')
                    ->label('Order Details')
                    ->view('filament.tables.columns.po-order-details'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Created Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Planning' => 'Planning',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name'),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with(['grns', 'supplier', 'items']))
            ->actions([
                Tables\Actions\Action::make('create_grn')
                    ->label(fn (PurchaseOrder $record): string => $record->grns->isNotEmpty() ? 'GRN Done' : 'GRN')
                    ->icon(fn (PurchaseOrder $record): string => $record->grns->isNotEmpty() ? 'heroicon-o-check-circle' : 'heroicon-o-document-plus')
                    ->color(fn (PurchaseOrder $record): string => $record->grns->isNotEmpty() ? 'gray' : 'success')
                    ->tooltip(fn (PurchaseOrder $record): string => $record->grns->isNotEmpty()
                            ? 'GRN already created: '.$record->grns->first()->grn_number.' (Click to view)'
                            : 'Create Goods Received Note from this Purchase Order'
                    )
                    ->url(fn (PurchaseOrder $record): string => $record->grns->isNotEmpty()
                            ? GrnResource::getUrl('view', ['record' => $record->grns->first()->id])
                            : GrnResource::getUrl('create', ['purchase_order_id' => $record->id])
                    ),

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
