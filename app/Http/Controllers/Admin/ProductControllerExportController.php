<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductControllerExcelService;
use Illuminate\Http\Request;

class ProductControllerExportController extends Controller
{
    public function export(Request $request, ProductControllerExcelService $service)
    {
        $path = $service->exportToXlsx([
            'category_id' => $request->query('category_id'),
            'subcategory_id' => $request->query('subcategory_id'),
            'supplier_id' => $request->query('supplier_id'),
            'visibility' => $request->query('visibility', 'visible'),
        ]);

        $filename = 'comlink-products-price-sheet-' . now()->format('Ymd-His') . '.xlsx';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
