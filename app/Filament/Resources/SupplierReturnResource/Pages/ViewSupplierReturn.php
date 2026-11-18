<?php

namespace App\Filament\Resources\SupplierReturnResource\Pages;

use App\Filament\Resources\SupplierReturnResource\SupplierReturnResource;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewSupplierReturn extends ViewRecord
{
    protected static string $resource = SupplierReturnResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Supplier RMA Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('rma_number')->label('RMA No')->weight('bold'),
                        Infolists\Components\TextEntry::make('supplier.name')->label('Supplier'),
                        Infolists\Components\TextEntry::make('grn.grn_number')->label('Linked GRN')->placeholder('None'),
                        Infolists\Components\TextEntry::make('status')->badge(),
                        Infolists\Components\TextEntry::make('total_cost_value')->label('Cost Value')->money('LKR'),
                        Infolists\Components\TextEntry::make('credit_amount')->label('Credited Amount')->money('LKR')->color('success'),
                        Infolists\Components\TextEntry::make('dispatched_at')->label('Dispatched At')->dateTime(),
                        Infolists\Components\TextEntry::make('resolved_at')->label('Resolved At')->dateTime()->placeholder('Pending'),
                        Infolists\Components\TextEntry::make('notes')->label('Notes')->columnSpanFull(),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Returned Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('product_name')->label('Product')->weight('bold'),
                                Infolists\Components\TextEntry::make('quantity')->label('Qty'),
                                Infolists\Components\TextEntry::make('unit_cost')->label('GRN Unit Cost')->money('LKR'),
                                Infolists\Components\TextEntry::make('total_cost')->label('Total Cost')->money('LKR'),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }
}
