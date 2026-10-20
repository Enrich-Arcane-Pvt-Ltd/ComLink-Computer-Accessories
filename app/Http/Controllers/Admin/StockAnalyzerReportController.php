<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StockAnalysisService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class StockAnalyzerReportController extends Controller
{
    protected StockAnalysisService $analysisService;

    public function __construct(StockAnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
    }

    /**
     * Download or view the PDF analysis report.
     */
    public function downloadPdf(Request $request)
    {
        $period = $request->query('period', 'all');
        $customStart = $request->query('start_date');
        $customEnd = $request->query('end_date');
        $supplierId = $request->query('supplier_id') ? (int) $request->query('supplier_id') : null;
        $categoryId = $request->query('category_id') ? (int) $request->query('category_id') : null;
        $search = $request->query('search');

        $data = $this->analysisService->analyze($period, $customStart, $customEnd, $supplierId, $categoryId, $search);

        $pdf = Pdf::loadView('reports.stock-analyzer-pdf', [
            'data' => $data,
            'isPrint' => false,
        ])
        ->setPaper('a4', 'landscape')
        ->setOption('isRemoteEnabled', true)
        ->setOption('dpi', 150);

        $filename = 'stock-analysis-report-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Print-friendly HTML report view.
     */
    public function printView(Request $request)
    {
        $period = $request->query('period', 'all');
        $customStart = $request->query('start_date');
        $customEnd = $request->query('end_date');
        $supplierId = $request->query('supplier_id') ? (int) $request->query('supplier_id') : null;
        $categoryId = $request->query('category_id') ? (int) $request->query('category_id') : null;
        $search = $request->query('search');

        $data = $this->analysisService->analyze($period, $customStart, $customEnd, $supplierId, $categoryId, $search);

        return view('reports.stock-analyzer-pdf', [
            'data' => $data,
            'isPrint' => true,
        ]);
    }
}
