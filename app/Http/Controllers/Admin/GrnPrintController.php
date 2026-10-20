<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grn;
use Illuminate\Http\Request;

class GrnPrintController extends Controller
{
    public function show(int $id)
    {
        $grn = Grn::with(['supplier', 'purchaseOrder', 'items'])->findOrFail($id);

        return view('filament.pages.print-grn', compact('grn'));
    }
}
