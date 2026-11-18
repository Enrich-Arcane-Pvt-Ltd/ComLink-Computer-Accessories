<?php

namespace App\Filament\Resources\DamagedStockResource;

use App\Filament\Resources\DamagedStockResource\Pages;
use App\Models\CustomerReturnItem;
use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\Supplier;
use App\Services\ReturnRefundService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DamagedStockResource extends Resource
{
    protected static ?string $model = CustomerReturnItem::class;

    protected static ?string $navigationGroup = 'Refunds & Returns';

    protected static ?string $navigationLabel = 'Damaged Stock';

    protected static ?string $modelLabel = 'Damaged Item';

    protected static ?string $pluralModelLabel = 'Damaged Stock';

    protected static ?string $slug = 'refunds/damaged-stock';

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $pendingCount = CustomerReturnItem::where('condition', 'damaged')
            ->where('resolution_status', 'in_damaged_stock')
            ->whereHas('customerReturn', fn ($q) => $q->where('status', 'approved'))
            ->sum('quantity');

        return $pendingCount > 0 ? (string) $pendingCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['customerReturn.order', 'product.supplier', 'supplierReturnItems.supplierReturn'])
            ->where('condition', 'damaged')
            ->whereHas('customerReturn', fn ($q) => $q->where('status', 'approved'))
            ->orderByDesc('id');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customerReturn.return_number')
                    ->label('Return No')
                    ->searchable()
                    ->weight('bold')
                    ->color('primary')
                    ->description(fn (CustomerReturnItem $record) => 'Order: ' . ($record->customerReturn?->order?->order_number ?? 'N/A')),

                Tables\Columns\TextColumn::make('product_name')
                    ->label('Damaged Product')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn (CustomerReturnItem $record) => $record->product?->product_code ?: 'No Product Code'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Damaged Qty')
                    ->alignCenter()
                    ->badge()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('remaining_damaged_quantity')
                    ->label('In Store (Pending RMA)')
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => (int) $state > 0 ? 'warning' : 'gray'),

                Tables\Columns\TextColumn::make('unit_cost')
                    ->label('GRN Unit Cost')
                    ->money('LKR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_loss_value')
                    ->label('Cost Value')
                    ->state(fn (CustomerReturnItem $record) => (float) $record->unit_cost * (int) $record->quantity)
                    ->money('LKR')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('resolution_status')
                    ->label('Resolution Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'in_damaged_stock' => 'danger',
                        'sent_to_supplier' => 'warning',
                        'supplier_credited' => 'info',
                        'supplier_replaced' => 'success',
                        'scrapped' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('resolution_status')
                    ->label('Status')
                    ->options([
                        'in_damaged_stock' => 'In Damaged Stock (Pending RMA)',
                        'sent_to_supplier' => 'Sent to Supplier',
                        'supplier_credited' => 'Supplier Credited',
                        'supplier_replaced' => 'Supplier Replaced',
                        'scrapped' => 'Scrapped (Write-off)',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('send_to_supplier')
                    ->label('Send to Supplier')
                    ->icon('heroicon-o-truck')
                    ->color('warning')
                    ->visible(fn (CustomerReturnItem $record) => $record->remaining_damaged_quantity > 0)
                    ->form(function (CustomerReturnItem $record) {
                        $defaultSupplierId = $record->product?->supplier_id;
                        $defaultGrnId = null;
                        $defaultGrnItemId = null;

                        if ($record->product?->product_code) {
                            $latestGrnItem = GrnItem::with('grn')
                                ->where('product_code', $record->product->product_code)
                                ->latest()
                                ->first();
                            if ($latestGrnItem) {
                                $defaultGrnItemId = $latestGrnItem->id;
                                $defaultGrnId = $latestGrnItem->grn_id;
                                if (! $defaultSupplierId && $latestGrnItem->grn?->supplier_id) {
                                    $defaultSupplierId = $latestGrnItem->grn->supplier_id;
                                }
                            }
                        }

                        return [
                            Forms\Components\Select::make('supplier_id')
                                ->label('Supplier')
                                ->options(Supplier::orderBy('name')->pluck('name', 'id'))
                                ->default($defaultSupplierId)
                                ->searchable()
                                ->required()
                                ->live(),

                            Forms\Components\Select::make('grn_id')
                                ->label('Linked GRN (Optional)')
                                ->options(function (Forms\Get $get) {
                                    $supId = $get('supplier_id');
                                    $q = Grn::orderByDesc('id');
                                    if ($supId) {
                                        $q->where('supplier_id', $supId);
                                    }

                                    return $q->pluck('grn_number', 'id');
                                })
                                ->default($defaultGrnId)
                                ->searchable(),

                            Forms\Components\Hidden::make('grn_item_id')
                                ->default($defaultGrnItemId),

                            Forms\Components\TextInput::make('quantity')
                                ->label('Quantity to Return to Supplier')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue($record->remaining_damaged_quantity)
                                ->default($record->remaining_damaged_quantity),

                            Forms\Components\TextInput::make('unit_cost')
                                ->label('GRN Unit Cost (Rs.)')
                                ->numeric()
                                ->prefix('Rs.')
                                ->required()
                                ->default((float) $record->unit_cost),

                            Forms\Components\Textarea::make('notes')
                                ->label('RMA / Dispatch Notes')
                                ->rows(2),
                        ];
                    })
                    ->action(function (CustomerReturnItem $record, array $data) {
                        app(ReturnRefundService::class)->createSupplierReturn(
                            (int) $data['supplier_id'],
                            ! empty($data['grn_id']) ? (int) $data['grn_id'] : null,
                            [
                                [
                                    'customer_return_item_id' => $record->id,
                                    'grn_item_id' => ! empty($data['grn_item_id']) ? (int) $data['grn_item_id'] : null,
                                    'quantity' => (int) $data['quantity'],
                                    'unit_cost' => (float) $data['unit_cost'],
                                ],
                            ],
                            [
                                'status' => 'dispatched',
                                'notes' => $data['notes'] ?? null,
                            ]
                        );

                        Notification::make()
                            ->title('Supplier Return (RMA) Created')
                            ->body('Damaged item has been dispatched to the supplier.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('mark_scrapped')
                    ->label('Scrap')
                    ->icon('heroicon-o-trash')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription('Mark this damaged item as scrapped (unrecoverable write-off)?')
                    ->visible(fn (CustomerReturnItem $record) => $record->remaining_damaged_quantity > 0)
                    ->action(function (CustomerReturnItem $record) {
                        $record->update(['resolution_status' => 'scrapped']);
                        Notification::make()
                            ->title('Marked as Scrapped')
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulk_send_to_supplier')
                    ->label('Send Selected to Supplier (RMA)')
                    ->icon('heroicon-o-truck')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->options(Supplier::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('grn_id')
                            ->label('Linked GRN (Optional)')
                            ->options(function (Forms\Get $get) {
                                $supId = $get('supplier_id');
                                $q = Grn::orderByDesc('id');
                                if ($supId) {
                                    $q->where('supplier_id', $supId);
                                }

                                return $q->pluck('grn_number', 'id');
                            })
                            ->searchable(),

                        Forms\Components\Textarea::make('notes')
                            ->label('RMA Notes')
                            ->rows(2),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $itemsData = [];
                        foreach ($records as $record) {
                            $rem = $record->remaining_damaged_quantity;
                            if ($rem <= 0) {
                                continue;
                            }
                            $itemsData[] = [
                                'customer_return_item_id' => $record->id,
                                'quantity' => $rem,
                                'unit_cost' => (float) $record->unit_cost,
                            ];
                        }

                        if (empty($itemsData)) {
                            Notification::make()
                                ->title('No eligible damaged items selected')
                                ->warning()
                                ->send();

                            return;
                        }

                        app(ReturnRefundService::class)->createSupplierReturn(
                            (int) $data['supplier_id'],
                            ! empty($data['grn_id']) ? (int) $data['grn_id'] : null,
                            $itemsData,
                            [
                                'status' => 'dispatched',
                                'notes' => $data['notes'] ?? null,
                            ]
                        );

                        Notification::make()
                            ->title('Batch Supplier Return Created')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDamagedStocks::route('/'),
        ];
    }
}
