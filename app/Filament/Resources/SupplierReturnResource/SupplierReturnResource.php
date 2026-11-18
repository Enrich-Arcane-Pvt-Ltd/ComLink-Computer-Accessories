<?php

namespace App\Filament\Resources\SupplierReturnResource;

use App\Filament\Resources\SupplierReturnResource\Pages;
use App\Models\SupplierReturn;
use App\Services\ReturnRefundService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierReturnResource extends Resource
{
    protected static ?string $model = SupplierReturn::class;

    protected static ?string $navigationGroup = 'Refunds & Returns';

    protected static ?string $navigationLabel = 'Supplier Returns (RMA)';

    protected static ?string $modelLabel = 'Supplier Return';

    protected static ?string $pluralModelLabel = 'Supplier Returns (RMA)';

    protected static ?string $slug = 'refunds/supplier-returns';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $openCount = SupplierReturn::whereIn('status', ['pending', 'dispatched'])->count();

        return $openCount > 0 ? (string) $openCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(SupplierReturn::query()->with(['supplier', 'grn', 'items']))
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('rma_number')
                    ->label('RMA No')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->description(fn (SupplierReturn $record) => $record->grn ? 'GRN: ' . $record->grn->grn_number : 'No Linked GRN'),

                Tables\Columns\TextColumn::make('items_summary')
                    ->label('Items Returned')
                    ->state(fn (SupplierReturn $record) => $record->items->sum('quantity') . ' unit(s) (' . $record->items->count() . ' item)')
                    ->description(fn (SupplierReturn $record) => $record->items->pluck('product_name')->take(2)->join(', ')),

                Tables\Columns\TextColumn::make('total_cost_value')
                    ->label('Cost Value')
                    ->money('LKR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('credit_amount')
                    ->label('Credited Amount')
                    ->money('LKR')
                    ->sortable()
                    ->color('success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'gray',
                        'dispatched' => 'warning',
                        'credited' => 'info',
                        'replaced' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('dispatched_at')
                    ->label('Dispatched')
                    ->dateTime('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('resolved_at')
                    ->label('Resolved')
                    ->dateTime('M d, Y')
                    ->placeholder('Pending')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'dispatched' => 'Dispatched to Supplier',
                        'credited' => 'Credited / Refunded',
                        'replaced' => 'Replaced (Restocked)',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('resolve_rma')
                    ->label('Resolve RMA')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (SupplierReturn $record) => in_array($record->status, ['pending', 'dispatched']))
                    ->form(fn (SupplierReturn $record) => [
                        Forms\Components\Select::make('resolution_type')
                            ->label('Supplier Resolution')
                            ->required()
                            ->live()
                            ->options([
                                'credit_refund' => 'Supplier Credit / Cash Refund (Reduces Net GRN Cost & Recovers Loss)',
                                'replacement' => 'Supplier Replacement Unit (Restores Available Sellable Stock)',
                                'rejected' => 'Rejected by Supplier (Marks as Unrecoverable Scrap Loss)',
                            ])
                            ->default('credit_refund'),

                        Forms\Components\TextInput::make('credit_amount')
                            ->label('Credit / Refund Amount Received (Rs.)')
                            ->numeric()
                            ->prefix('Rs.')
                            ->default((float) $record->total_cost_value)
                            ->required(fn (Forms\Get $get) => $get('resolution_type') === 'credit_refund')
                            ->visible(fn (Forms\Get $get) => $get('resolution_type') === 'credit_refund'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Resolution Notes')
                            ->rows(2),
                    ])
                    ->action(function (SupplierReturn $record, array $data) {
                        app(ReturnRefundService::class)->resolveSupplierReturn(
                            $record,
                            $data['resolution_type'],
                            (float) ($data['credit_amount'] ?? 0),
                            $data['notes'] ?? null
                        );

                        Notification::make()
                            ->title('Supplier Return Resolved')
                            ->body("RMA {$record->rma_number} has been updated.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupplierReturns::route('/'),
            'view' => Pages\ViewSupplierReturn::route('/{record}'),
        ];
    }
}
