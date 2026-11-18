<?php

namespace App\Filament\Resources\CustomerReturnResource;

use App\Filament\Resources\CustomerReturnResource\Pages;
use App\Models\CustomerReturn;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerReturnResource extends Resource
{
    protected static ?string $model = CustomerReturn::class;

    protected static ?string $navigationGroup = 'Refunds & Returns';

    protected static ?string $navigationLabel = 'Customer Refunds';

    protected static ?string $modelLabel = 'Customer Refund';

    protected static ?string $pluralModelLabel = 'Customer Refunds';

    protected static ?string $slug = 'refunds/customer-refunds';

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $pendingCount = CustomerReturn::where('status', 'pending')->count();
        if ($pendingCount > 0) {
            return (string) $pendingCount;
        }

        $approvedCount = CustomerReturn::where('status', 'approved')->count();

        return $approvedCount > 0 ? (string) $approvedCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return CustomerReturn::where('status', 'pending')->exists() ? 'danger' : 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Order & Refund Details')
                ->schema([
                    Forms\Components\Select::make('order_id')
                        ->label('Paid Order')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->options(function () {
                            return Order::where('payment_status', 'Paid')
                                ->where('refund_status', '!=', 'full')
                                ->orderByDesc('id')
                                ->get()
                                ->mapWithKeys(fn (Order $o) => [
                                    $o->id => "{$o->order_number} — {$o->customer_name} (Rs. " . number_format((float) $o->total_amount, 2) . ')',
                                ]);
                        })
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if (! $state) {
                                $set('items', []);
                                $set('refund_amount', 0);

                                return;
                            }

                            $order = Order::with('items')->find($state);
                            if (! $order) {
                                return;
                            }

                            $repeaterItems = [];
                            $total = 0.0;
                            foreach ($order->items as $item) {
                                $returnable = $item->returnable_quantity;
                                if ($returnable <= 0) {
                                    continue;
                                }
                                $repeaterItems[] = [
                                    'order_item_id' => $item->id,
                                    'product_name' => $item->product_name . ($item->color ? " ({$item->color})" : ''),
                                    'max_returnable' => $returnable,
                                    'quantity' => $returnable,
                                    'unit_price' => (float) $item->unit_price,
                                    'condition' => 'damaged',
                                    'notes' => null,
                                ];
                                $total += $returnable * (float) $item->unit_price;
                            }

                            $set('items', $repeaterItems);
                            $set('refund_amount', round($total, 2));
                        })
                        ->disabled(fn ($operation) => $operation !== 'create'),

                    Forms\Components\Select::make('refund_method')
                        ->label('Refund Method')
                        ->required()
                        ->default('cash')
                        ->options([
                            'cash' => 'Cash Refund',
                            'bank_transfer' => 'Bank Transfer',
                            'card_refund' => 'Card / Stripe Refund',
                            'store_credit' => 'Store Credit',
                        ]),

                    Forms\Components\TextInput::make('reason')
                        ->label('Return Reason')
                        ->default('Damaged / Defective Product')
                        ->maxLength(255)
                        ->required(),

                    Forms\Components\TextInput::make('refund_amount')
                        ->label('Total Refund Amount (Rs.)')
                        ->numeric()
                        ->prefix('Rs.')
                        ->required()
                        ->minValue(0),

                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Admin Notes')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Returned Items & Condition Labeling')
                ->description('Set quantity to 0 for items NOT being returned. Returned damaged items are automatically placed in Damaged Stock and will NOT restore sellable website inventory.')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->label('Order Items')
                        ->schema([
                            Forms\Components\Hidden::make('order_item_id'),
                            Forms\Components\Hidden::make('max_returnable'),

                            Forms\Components\TextInput::make('product_name')
                                ->label('Product')
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(4),

                            Forms\Components\TextInput::make('quantity')
                                ->label('Return Qty')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(fn (Get $get) => (int) ($get('max_returnable') ?? 999))
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $items = $get('../../items') ?? [];
                                    $sum = 0.0;
                                    foreach ($items as $row) {
                                        $sum += (int) ($row['quantity'] ?? 0) * (float) ($row['unit_price'] ?? 0);
                                    }
                                    $set('../../refund_amount', round($sum, 2));
                                })
                                ->helperText(fn (Get $get) => 'Max: ' . ($get('max_returnable') ?? 0))
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('unit_price')
                                ->label('Unit Price')
                                ->numeric()
                                ->prefix('Rs.')
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(2),

                            Forms\Components\Select::make('condition')
                                ->label('Condition Label')
                                ->required()
                                ->default('damaged')
                                ->options([
                                    'damaged' => 'Damaged (Quarantine)',
                                    'good' => 'Good / Resellable',
                                ])
                                ->columnSpan(4),
                        ])
                        ->columns(12)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->defaultItems(0),
                ])
                ->visible(fn ($operation) => $operation === 'create'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(CustomerReturn::query()->with(['order', 'items']))
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('return_number')
                    ->label('Return No')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->description(fn (CustomerReturn $record) => $record->requested_by_customer ? 'Customer Submission' : 'Admin Entry'),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order No')
                    ->searchable()
                    ->sortable()
                    ->description(fn (CustomerReturn $record) => $record->order?->customer_name ?? 'Guest'),

                Tables\Columns\TextColumn::make('items_summary')
                    ->label('Returned Items')
                    ->state(fn (CustomerReturn $record) => $record->items->sum('quantity') . ' unit(s) across ' . $record->items->count() . ' item(s)')
                    ->description(function (CustomerReturn $record) {
                        $damaged = $record->items->where('condition', 'damaged')->sum('quantity');
                        $good = $record->items->where('condition', 'good')->sum('quantity');
                        $parts = [];
                        if ($damaged > 0) {
                            $parts[] = "{$damaged} Damaged";
                        }
                        if ($good > 0) {
                            $parts[] = "{$good} Good";
                        }

                        return implode(' | ', $parts);
                    }),

                Tables\Columns\TextColumn::make('refund_amount')
                    ->label('Refund Amount')
                    ->money('LKR')
                    ->sortable()
                    ->weight('bold')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('refund_method')
                    ->label('Method')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title()),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('refunded_at')
                    ->label('Refunded At')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('refund_method')
                    ->options([
                        'cash' => 'Cash Refund',
                        'bank_transfer' => 'Bank Transfer',
                        'card_refund' => 'Card / Stripe Refund',
                        'store_credit' => 'Store Credit',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (CustomerReturn $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Critical Action: Confirm Refund Approval')
                    ->modalDescription('Are you sure you want to APPROVE this customer refund? Once approved, the refund amount will be deducted from Total Sales/Income and the returned items will enter Damaged Stock for Supplier Return.')
                    ->modalSubmitActionLabel('Yes, Approve Refund')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Approval Notes (Optional)')
                            ->rows(2),
                    ])
                    ->action(function (CustomerReturn $record, array $data) {
                        app(\App\Services\ReturnRefundService::class)->approveCustomerReturn($record, $data['admin_notes'] ?? null);

                        \Filament\Notifications\Notification::make()
                            ->title('Refund Request Approved')
                            ->body("Return {$record->return_number} approved. Sales & Damaged Stock updated.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (CustomerReturn $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Critical Action: Confirm Refund Rejection')
                    ->modalDescription('Are you sure you want to REJECT this customer refund request? The customer will see "Rejected" on their Orders page with instructions to call ComLink for more information.')
                    ->modalSubmitActionLabel('Yes, Reject Refund')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason (Optional)')
                            ->rows(2),
                    ])
                    ->action(function (CustomerReturn $record, array $data) {
                        app(\App\Services\ReturnRefundService::class)->rejectCustomerReturn($record, $data['rejection_reason'] ?? null);

                        \Filament\Notifications\Notification::make()
                            ->title('Refund Request Rejected')
                            ->body("Return {$record->return_number} has been rejected.")
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerReturns::route('/'),
            'create' => Pages\CreateCustomerReturn::route('/create'),
            'view' => Pages\ViewCustomerReturn::route('/{record}'),
        ];
    }
}
