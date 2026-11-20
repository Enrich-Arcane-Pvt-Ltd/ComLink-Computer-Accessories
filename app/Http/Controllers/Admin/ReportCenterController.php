<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportCenterService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportCenterController extends Controller
{
    public function __construct(protected ReportCenterService $service) {}

    protected function extractFilters(Request $request): array
    {
        return [
            'category_id' => $request->query('category_id'),
            'subcategory_id' => $request->query('subcategory_id'),
            'product_id' => $request->query('product_id'),
            'supplier_id' => $request->query('supplier_id'),
            'product_type' => $request->query('product_type', 'all'),
            'period' => $request->query('period', 'all'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];
    }

    public function downloadPdf(Request $request, string $type)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(180);

        $reportData = $this->service->generate($type, $this->extractFilters($request));

        $pdf = Pdf::loadView('reports.unified-report-pdf', [
            'data' => $reportData,
            'isPrint' => false,
            'generatedAt' => now()->format('M d, Y h:i A'),
        ])
            ->setPaper('a4', 'landscape')
            ->setOption('isRemoteEnabled', true)
            ->setOption('dpi', 150);

        $slug = str($type)->replace('_', '-');
        $filename = "comlink-{$slug}-report-" . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }

    public function printView(Request $request, string $type)
    {
        $reportData = $this->service->generate($type, $this->extractFilters($request));

        return view('reports.unified-report-pdf', [
            'data' => $reportData,
            'isPrint' => true,
            'generatedAt' => now()->format('M d, Y h:i A'),
        ]);
    }
}
