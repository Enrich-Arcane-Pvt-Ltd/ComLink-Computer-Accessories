<?php

namespace App\Filament\Resources\GrnAnalyzerResource\Pages;

use App\Filament\Resources\GrnAnalyzerResource\GrnAnalyzerResource;
use App\Models\InventoryProduct;
use App\Models\Supplier;
use App\Services\GrnAnalysisService;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListGrnAnalyzers extends ListRecords
{
    protected static string $resource = GrnAnalyzerResource::class;

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_report')
                ->label('Generate GRN Report')
                ->icon('heroicon-o-document-chart-bar')
                ->color('primary')
                ->modalHeading('Generate GRN Analysis Report')
                ->modalDescription('Analyze Goods Received Notes by selected time period, supplier-wise or product-wise.')
                ->modalSubmitActionLabel('Generate & Save Report')
                ->modalWidth('2xl')
                ->form([
                    Forms\Components\Radio::make('analysis_type')
                        ->label('Select Analysis Mode')
                        ->options([
                            'supplier_wise' => 'Supplier-Wise Analysis (Procurement by Supplier)',
                            'product_wise' => 'Product-Wise Analysis (Procurement by Product / Item)',
                        ])
                        ->default('supplier_wise')
                        ->required()
                        ->live(),

                    Forms\Components\Select::make('supplier_id')
                        ->label('Select Supplier')
                        ->options(fn () => ['' => 'All Suppliers (Consolidated)'] + Supplier::pluck('name', 'id')->toArray())
                        ->default('')
                        ->searchable()
                        ->preload()
                        ->visible(fn (Get $get) => $get('analysis_type') === 'supplier_wise')
                        ->helperText('Select a specific supplier, or leave blank to analyze all suppliers.'),

                    Forms\Components\Select::make('product_code')
                        ->label('Select Product')
                        ->options(function () {
                            $products = InventoryProduct::pluck('product_name', 'product_code')->toArray();
                            $options = ['' => 'All Products (Consolidated)'];
                            foreach ($products as $code => $name) {
                                $options[$code] = "{$code} - {$name}";
                            }

                            return $options;
                        })
                        ->default('')
                        ->searchable()
                        ->preload()
                        ->visible(fn (Get $get) => $get('analysis_type') === 'product_wise')
                        ->helperText('Select a specific product, or leave blank to analyze all products.'),

                    Forms\Components\Select::make('time_preset')
                        ->label('Time Period Preset')
                        ->options([
                            'this_month' => 'This Month',
                            'last_30_days' => 'Last 30 Days',
                            'this_quarter' => 'This Quarter',
                            'this_year' => 'This Year',
                            'all_time' => 'All Time',
                            'custom' => 'Custom Date Range',
                        ])
                        ->default('this_month')
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            $now = now();
                            switch ($state) {
                                case 'this_month':
                                    $set('date_from', $now->copy()->startOfMonth()->format('Y-m-d'));
                                    $set('date_to', $now->copy()->endOfDay()->format('Y-m-d'));
                                    break;
                                case 'last_30_days':
                                    $set('date_from', $now->copy()->subDays(30)->format('Y-m-d'));
                                    $set('date_to', $now->copy()->endOfDay()->format('Y-m-d'));
                                    break;
                                case 'this_quarter':
                                    $set('date_from', $now->copy()->startOfQuarter()->format('Y-m-d'));
                                    $set('date_to', $now->copy()->endOfDay()->format('Y-m-d'));
                                    break;
                                case 'this_year':
                                    $set('date_from', $now->copy()->startOfYear()->format('Y-m-d'));
                                    $set('date_to', $now->copy()->endOfDay()->format('Y-m-d'));
                                    break;
                                case 'all_time':
                                    $set('date_from', '2020-01-01');
                                    $set('date_to', $now->copy()->endOfDay()->format('Y-m-d'));
                                    break;
                            }
                        }),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\DatePicker::make('date_from')
                                ->label('Start Date (From)')
                                ->default(now()->startOfMonth()->format('Y-m-d'))
                                ->required(),

                            Forms\Components\DatePicker::make('date_to')
                                ->label('End Date (To)')
                                ->default(now()->format('Y-m-d'))
                                ->required(),
                        ]),

                    Forms\Components\TextInput::make('title')
                        ->label('Custom Report Title (Optional)')
                        ->placeholder('e.g. Q4 Procurement Analysis or TechZone Evaluation')
                        ->maxLength(255),

                    Forms\Components\Textarea::make('notes')
                        ->label('Observations / Notes (Optional)')
                        ->rows(2)
                        ->placeholder('Add any audit remarks or notes for this report...'),
                ])
                ->action(function (array $data) {
                    $service = app(GrnAnalysisService::class);
                    $report = $service->generateReport($data);

                    Notification::make()
                        ->title('Report Generated Successfully')
                        ->body("Report {$report->report_number} generated with {$report->total_items_count} items (Total: Rs. ".number_format($report->total_amount, 2).').')
                        ->success()
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('view')
                                ->button()
                                ->label('View Report')
                                ->url(route('admin.grn-analyzer.view', $report->id), shouldOpenInNewTab: true),
                            \Filament\Notifications\Actions\Action::make('pdf')
                                ->button()
                                ->label('Download PDF')
                                ->color('gray')
                                ->url(route('admin.grn-analyzer.pdf', $report->id), shouldOpenInNewTab: true),
                        ])
                        ->send();
                }),
        ];
    }
}
