<?php

namespace App\Filament\Resources\SalesInfoResource;

use App\Filament\Resources\OrderResource\Schemas\OrderForm;
use App\Filament\Resources\SalesInfoResource\Pages;
use App\Filament\Resources\SalesInfoResource\Widgets\SalesInfoOverviewWidget;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesInfoResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationGroup = 'Sales Section';

    protected static ?string $navigationLabel = 'Sales Info';

    protected static ?string $modelLabel = 'Sale';

    protected static ?string $pluralModelLabel = 'Sales Info';

    protected static ?string $slug = 'sales-section/sales-info';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 1;

    /**
     * Scope table strictly to orders with successful payment.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['items', 'user'])
            ->where('payment_status', 'Paid');
    }

    public static function form(Form $form): Form
    {
        return $form->schema(OrderForm::schema())->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Order No (under that: No of units (no of products))
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order No')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->description(fn (Order $record): string =>
                        $record->total_units . ' ' . ($record->total_units === 1 ? 'unit' : 'units') .
                        ' (' . $record->total_products_count . ' ' . ($record->total_products_count === 1 ? 'product' : 'products') . ')'
                    ),

                // 2. User Details (User name and under that: Primary phone no)
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('User Details')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->where('customer_name', 'like', "%{$search}%")
                              ->orWhere('customer_phone', 'like', "%{$search}%")
                              ->orWhere('customer_email', 'like', "%{$search}%");
                        });
                    })
                    ->sortable()
                    ->weight('semibold')
                    ->formatStateUsing(fn ($state, Order $record) => $state ?: ($record->user?->name ?? 'Guest User'))
                    ->description(fn (Order $record): string => $record->customer_phone ?: ($record->customer_secondary_phone ?: 'No phone provided')),

                // 3. Total Amount
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->formatStateUsing(fn ($state) => 'Rs. ' . number_format((float) $state, 2))
                    ->sortable()
                    ->weight('bold')
                    ->color('info'),

                // 4. Placed Date
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Placed Date')
                    ->dateTime('M d, Y · h:i A')
                    ->sortable(),

                // Status Column for clarity
                Tables\Columns\TextColumn::make('status')
                    ->label('Fulfillment')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'processing' => 'info',
                        'packaging' => 'warning',
                        'shipping' => 'primary',
                        'delivered' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Fulfillment Status')
                    ->options([
                        'Processing' => 'Processing',
                        'Packaging' => 'Packaging',
                        'Shipping' => 'Shipping',
                        'Delivered' => 'Delivered',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->label('Date Range')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('From Date'),
                        Forms\Components\DatePicker::make('created_until')->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                // 5. Receipt button: opens modal showing order receipt and below it Download button
                Tables\Actions\Action::make('receipt')
                    ->label('Receipt')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->modalHeading(fn (Order $record): string => "Official Receipt — #{$record->order_number}")
                    ->modalDescription('View order transaction summary and download customer PDF receipt.')
                    ->modalWidth(MaxWidth::FourExtraLarge)
                    ->modalContent(fn (Order $record) => view('filament.components.order-receipt-preview', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->extraModalFooterActions([
                        Tables\Actions\Action::make('download_pdf')
                            ->label('Download Receipt')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('primary')
                            ->url(fn (Order $record): string => route('orders.receipt', ['order' => $record->id]))
                            ->openUrlInNewTab(),
                    ]),

                // 6. View: full order details view option
                Tables\Actions\ViewAction::make()
                    ->label('View'),

                // 7. Edit
                Tables\Actions\EditAction::make()
                    ->label('Edit'),

                // 8. Delete
                Tables\Actions\DeleteAction::make()
                    ->label('Delete'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getWidgets(): array
    {
        return [
            SalesInfoOverviewWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesInfos::route('/'),
            'view' => Pages\ViewSalesInfo::route('/{record}'),
            'edit' => Pages\EditSalesInfo::route('/{record}/edit'),
        ];
    }
}
