<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProfitLossAnalysisService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ProfitLossReportController extends Controller
{
    protected ProfitLossAnalysisService $service;

    public function __construct(ProfitLossAnalysisService $service)
    {
        $this->service = $service;
    }

    /**
     * Download landscape PDF of the Profit & Loss Analysis.
     */
    public function downloadPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(180);

        $categoryId = $request->query('category_id') ? (int) $request->query('category_id') : null;
        $subCategoryId = $request->query('subcategory_id') ? (int) $request->query('subcategory_id') : null;
        $productId = $request->query('product_id') ? (int) $request->query('product_id') : null;

        $reportData = $this->service->calculate($categoryId, $subCategoryId, $productId);

        $pdf = Pdf::loadView('reports.profit-loss-pdf', [
            'data' => $reportData,
            'isPrint' => false,
            'generatedAt' => now()->format('M d, Y h:i A'),
        ])
            ->setPaper('a4', 'landscape')
            ->setOption('isRemoteEnabled', true)
            ->setOption('dpi', 150);

        $safeScope = $reportData['filter_meta']['category_name']
            ? str($reportData['filter_meta']['category_name'])->slug('-')
            : ($reportData['filter_meta']['product_name'] ? str($reportData['filter_meta']['product_name'])->slug('-') : 'all-products');

        $filename = 'comlink-profit-loss-' . $safeScope . '-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Print-friendly browser view.
     */
    public function printView(Request $request)
    {
        $categoryId = $request->query('category_id') ? (int) $request->query('category_id') : null;
        $subCategoryId = $request->query('subcategory_id') ? (int) $request->query('subcategory_id') : null;
        $productId = $request->query('product_id') ? (int) $request->query('product_id') : null;

        $reportData = $this->service->calculate($categoryId, $subCategoryId, $productId);

        return view('reports.profit-loss-pdf', [
            'data' => $reportData,
            'isPrint' => true,
            'generatedAt' => now()->format('M d, Y h:i A'),
        ]);
    }
}
