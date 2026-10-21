<?php

namespace App\Filament\Resources\GrnAnalyzerResource\Tables;

use App\Models\GrnReport;
use Filament\Tables;
use Filament\Tables\Table;

class GrnAnalyzerTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('report_number')
                    ->label('Report Info')
                    ->searchable()
                    ->sortable()
                    ->description(fn (GrnReport $record) => $record->title)
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\BadgeColumn::make('analysis_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => $state === 'supplier_wise' ? 'Supplier-Wise' : 'Product-Wise')
                    ->colors([
                        'info' => 'supplier_wise',
                        'warning' => 'product_wise',
                    ])
                    ->icons([
                        'heroicon-o-truck' => 'supplier_wise',
                        'heroicon-o-cube' => 'product_wise',
                    ]),

                Tables\Columns\TextColumn::make('scope')
                    ->label('Target Scope')
                    ->getStateUsing(fn (GrnReport $record) => $record->report_data['scope_label'] ?? 'All')
                    ->icon('heroicon-o-funnel')
                    ->iconColor('gray')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('period')
                    ->label('Time Period')
                    ->getStateUsing(function (GrnReport $record) {
                        $from = $record->date_from ? $record->date_from->format('M d, Y') : '';
                        $to = $record->date_to ? $record->date_to->format('M d, Y') : '';

                        return "{$from} - {$to}";
                    })
                    ->icon('heroicon-o-calendar')
                    ->iconColor('gray')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Total Qty')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => number_format((int) $state).' units')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Value (Rs.)')
                    ->formatStateUsing(fn ($state) => config('app.currency_symbol', 'Rs. ').number_format((float) $state, 2))
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Generated At')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('analysis_type')
                    ->label('Analysis Type')
                    ->options([
                        'supplier_wise' => 'Supplier-Wise',
                        'product_wise' => 'Product-Wise',
                    ]),
            ])
            ->actions([
                // 1. View Action
                Tables\Actions\Action::make('view_report')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->tooltip('View full detailed report')
                    ->url(fn (GrnReport $record) => route('admin.grn-analyzer.view', $record->id))
                    ->openUrlInNewTab(),

                // 2. Download PDF Action
                Tables\Actions\Action::make('download_pdf')
                    ->label('Download (.pdf)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->tooltip('Download formal PDF report')
                    ->url(fn (GrnReport $record) => route('admin.grn-analyzer.pdf', $record->id))
                    ->openUrlInNewTab(),

                // 3. Print Action
                Tables\Actions\Action::make('print_report')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->tooltip('Print report')
                    ->url(fn (GrnReport $record) => route('admin.grn-analyzer.print', $record->id))
                    ->openUrlInNewTab(),

                // 4. Delete Action
                Tables\Actions\DeleteAction::make()
                    ->tooltip('Delete saved report'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
