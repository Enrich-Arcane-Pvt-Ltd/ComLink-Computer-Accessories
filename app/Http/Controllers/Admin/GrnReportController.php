<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GrnReport;
use Barryvdh\DomPDF\Facade\Pdf;

class GrnReportController extends Controller
{
    /**
     * View report in browser preview mode
     */
    public function view(GrnReport $report)
    {
        return view('reports.grn-analysis-pdf', [
            'report' => $report,
            'isPdf' => false,
        ]);
    }

    /**
     * Download report as PDF
     */
    public function downloadPdf(GrnReport $report)
    {
        $pdf = Pdf::loadView('reports.grn-analysis-pdf', [
            'report' => $report,
            'isPdf' => true,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $fileName = "{$report->report_number}.pdf";

        return $pdf->download($fileName);
    }

    /**
     * Direct print view with auto-trigger
     */
    public function print(GrnReport $report)
    {
        return view('reports.grn-analysis-pdf', [
            'report' => $report,
            'isPdf' => false,
            'autoPrint' => true,
        ]);
    }
}
