<?php

namespace App\Services;

use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\GrnReport;
use App\Models\InventoryProduct;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class GrnAnalysisService
{
    /**
     * Generate a new GRN Analysis Report and save snapshot
     */
    public function generateReport(array $params): GrnReport
    {
        $analysisType = $params['analysis_type'] ?? 'supplier_wise';
        $supplierId = ! empty($params['supplier_id']) ? (int) $params['supplier_id'] : null;
        $productCode = ! empty($params['product_code']) ? trim($params['product_code']) : null;
        $dateFrom = Carbon::parse($params['date_from'])->startOfDay();
        $dateTo = Carbon::parse($params['date_to'])->endOfDay();

        // 1. Fetch GRNs in time period
        $query = Grn::query()
            ->with(['supplier', 'items.inventoryProduct'])
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($analysisType === 'supplier_wise' && $supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($analysisType === 'product_wise' && $productCode) {
            $query->whereHas('items', fn ($q) => $q->where('product_code', $productCode));
        }

        $grns = $query->orderBy('created_at', 'desc')->get();

        // 2. Filter items and compute totals
        $allDetailedItems = [];
        $supplierGroups = [];
        $productGroups = [];
        $totalQuantity = 0;
        $totalAmount = 0.0;
        $distinctGrnIds = [];

        foreach ($grns as $grn) {
            $supName = $grn->supplier?->name ?? 'Unknown Supplier';
            $supId = $grn->supplier_id ?? 0;

            foreach ($grn->items as $item) {
                // If product-wise and a specific product is requested, skip other products
                if ($analysisType === 'product_wise' && $productCode && $item->product_code !== $productCode) {
                    continue;
                }

                $distinctGrnIds[$grn->id] = true;
                $qty = (int) $item->product_quantity;
                $unitPrice = (float) $item->price_per_unit;
                $lineTotal = (float) $item->total;

                $totalQuantity += $qty;
                $totalAmount += $lineTotal;

                // Detailed item
                $allDetailedItems[] = [
                    'grn_id' => $grn->id,
                    'grn_number' => $grn->grn_number,
                    'date' => $grn->created_at->format('Y-m-d H:i'),
                    'supplier_name' => $supName,
                    'product_code' => $item->product_code,
                    'product_name' => $item->product_name,
                    'product_condition' => $item->product_condition,
                    'quantity' => $qty,
                    'price_per_unit' => $unitPrice,
                    'total' => $lineTotal,
                ];

                // Supplier breakdown
                if (! isset($supplierGroups[$supId])) {
                    $supplierGroups[$supId] = [
                        'supplier_id' => $supId,
                        'supplier_name' => $supName,
                        'grn_ids' => [],
                        'total_quantity' => 0,
                        'total_amount' => 0.0,
                    ];
                }
                $supplierGroups[$supId]['grn_ids'][$grn->id] = true;
                $supplierGroups[$supId]['total_quantity'] += $qty;
                $supplierGroups[$supId]['total_amount'] += $lineTotal;

                // Product breakdown
                $codeKey = $item->product_code;
                if (! isset($productGroups[$codeKey])) {
                    $productGroups[$codeKey] = [
                        'product_code' => $item->product_code,
                        'product_name' => $item->product_name,
                        'product_condition' => $item->product_condition,
                        'total_quantity' => 0,
                        'total_amount' => 0.0,
                        'unit_prices' => [],
                    ];
                }
                $productGroups[$codeKey]['total_quantity'] += $qty;
                $productGroups[$codeKey]['total_amount'] += $lineTotal;
                $productGroups[$codeKey]['unit_prices'][] = $unitPrice;
            }
        }

        // Format supplier groups
        $formattedSuppliers = [];
        foreach ($supplierGroups as $sup) {
            $formattedSuppliers[] = [
                'supplier_id' => $sup['supplier_id'],
                'supplier_name' => $sup['supplier_name'],
                'grn_count' => count($sup['grn_ids']),
                'total_quantity' => $sup['total_quantity'],
                'total_amount' => round($sup['total_amount'], 2),
                'percentage' => $totalAmount > 0 ? round(($sup['total_amount'] / $totalAmount) * 100, 1) : 0,
            ];
        }
        usort($formattedSuppliers, fn ($a, $b) => $b['total_amount'] <=> $a['total_amount']);

        // Format product groups
        $formattedProducts = [];
        foreach ($productGroups as $prod) {
            $avgPrice = ! empty($prod['unit_prices']) ? array_sum($prod['unit_prices']) / count($prod['unit_prices']) : 0;
            $formattedProducts[] = [
                'product_code' => $prod['product_code'],
                'product_name' => $prod['product_name'],
                'product_condition' => $prod['product_condition'],
                'total_quantity' => $prod['total_quantity'],
                'avg_price' => round($avgPrice, 2),
                'total_amount' => round($prod['total_amount'], 2),
                'percentage' => $totalAmount > 0 ? round(($prod['total_amount'] / $totalAmount) * 100, 1) : 0,
            ];
        }
        usort($formattedProducts, fn ($a, $b) => $b['total_amount'] <=> $a['total_amount']);

        // Build Title
        $scopeText = 'All';
        if ($analysisType === 'supplier_wise') {
            $scopeText = $supplierId ? (Supplier::find($supplierId)?->name ?? 'Selected Supplier') : 'All Suppliers';
            $defaultTitle = "GRN Analysis ({$scopeText}) - " . $dateFrom->format('M d') . ' to ' . $dateTo->format('M d, Y');
        } else {
            $scopeText = $productCode ? ($productCode . ' - ' . (InventoryProduct::where('product_code', $productCode)->value('product_name') ?? 'Selected Product')) : 'All Products';
            $defaultTitle = "GRN Analysis ({$scopeText}) - " . $dateFrom->format('M d') . ' to ' . $dateTo->format('M d, Y');
        }

        $title = ! empty($params['title']) ? trim($params['title']) : $defaultTitle;

        // Create Report record
        return GrnReport::create([
            'title' => $title,
            'analysis_type' => $analysisType,
            'supplier_id' => $supplierId,
            'product_code' => $productCode,
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
            'total_grns' => count($distinctGrnIds),
            'total_items_count' => count($allDetailedItems),
            'total_quantity' => $totalQuantity,
            'total_amount' => round($totalAmount, 2),
            'notes' => $params['notes'] ?? null,
            'created_by' => Auth::id(),
            'report_data' => [
                'scope_label' => $scopeText,
                'date_from_formatted' => $dateFrom->format('M d, Y'),
                'date_to_formatted' => $dateTo->format('M d, Y'),
                'suppliers_summary' => $formattedSuppliers,
                'products_summary' => $formattedProducts,
                'detailed_items' => $allDetailedItems,
            ],
        ]);
    }
}
