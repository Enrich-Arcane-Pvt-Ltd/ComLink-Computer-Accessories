<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GRN Invoice - {{ $grn->grn_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #fff !important;
                color: #000 !important;
                padding: 0 !important;
            }
            @page {
                margin: 1.5cm;
                size: A4 portrait;
            }
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-4 sm:p-8 text-gray-900">

    <!-- Action Bar (hidden when printing) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div class="flex items-center gap-3">
            <a href="/admin/grns" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition flex items-center gap-1.5">
                &larr; Back to GRN List
            </a>
            <span class="text-gray-300">|</span>
            <span class="text-sm font-bold text-gray-800">Invoice: {{ $grn->grn_number }}</span>
        </div>
        <button onclick="window.print()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm flex items-center gap-2 transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print GRN Invoice
        </button>
    </div>

    <!-- Printable Invoice Sheet -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-sm border border-gray-200 print:border-none print:shadow-none print:p-0">
        <!-- Header -->
        <div class="flex justify-between items-start border-b border-gray-200 pb-8 mb-8">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">ComLink Computer Accessories</h1>
                <p class="text-sm text-gray-500 mt-1">Computer Parts, Laptops & High-Performance Peripherals</p>
                <div class="text-xs text-gray-500 mt-3 space-y-0.5">
                    <p>Phone: +94 77 123 4567 / +94 11 234 5678</p>
                    <p>Email: support@comlink.lk | Web: www.comlink.lk</p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 text-xs font-black uppercase tracking-widest bg-blue-50 text-blue-700 rounded-md border border-blue-200">
                    Goods Received Note (GRN)
                </span>
                <p class="text-lg font-mono font-bold text-gray-900 mt-3">{{ $grn->grn_number }}</p>
                <p class="text-xs text-gray-500 mt-1">Date: {{ $grn->created_at->format('M d, Y h:i A') }}</p>
                @if($grn->purchaseOrder)
                    <p class="text-xs text-gray-600 mt-1">PO Ref: <span class="font-mono font-semibold">{{ $grn->purchaseOrder->po_number }}</span></p>
                @elseif($grn->direct_po_number)
                    <p class="text-xs text-gray-600 mt-1">Direct Ref: <span class="font-mono font-semibold">{{ $grn->direct_po_number }}</span></p>
                @endif
            </div>
        </div>

        <!-- Supplier & PO Details -->
        <div class="grid grid-cols-2 gap-8 mb-8">
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200/80">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Supplier Details</h3>
                <p class="text-base font-bold text-gray-900">{{ $grn->supplier?->name ?? 'N/A' }}</p>
                <p class="text-xs text-gray-600 mt-1">Contact: {{ $grn->supplier?->contact_no ?? '—' }}</p>
                <p class="text-xs text-gray-600">Email: {{ $grn->supplier?->email ?? '—' }}</p>
                <p class="text-xs text-gray-600">Address: {{ $grn->supplier?->address ?? '—' }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200/80">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">GRN Metadata</h3>
                <p class="text-xs text-gray-700"><span class="font-semibold text-gray-500">Invoice Number:</span> {{ $grn->grn_number }}</p>
                <p class="text-xs text-gray-700 mt-1"><span class="font-semibold text-gray-500">Purchase Order / Direct Ref:</span> {{ $grn->purchaseOrder?->po_number ?? $grn->direct_po_number ?? 'Direct Receipt' }}</p>
                <p class="text-xs text-gray-700 mt-1"><span class="font-semibold text-gray-500">Items Count:</span> {{ $grn->items->count() }} items ({{ $grn->items->sum('product_quantity') }} total units)</p>
                <p class="text-xs text-gray-700 mt-1"><span class="font-semibold text-gray-500">Status:</span> Received & Verified</p>
            </div>
        </div>

        <!-- Items Table -->
        <table class="w-full text-left text-sm mb-8 border-collapse">
            <thead>
                <tr class="border-b-2 border-gray-900 text-xs font-bold uppercase tracking-wider text-gray-700 bg-gray-50">
                    <th class="py-3 px-3 text-center w-12">#</th>
                    <th class="py-3 px-3">Product Code</th>
                    <th class="py-3 px-3">Product Description</th>
                    <th class="py-3 px-3 text-center">Condition</th>
                    <th class="py-3 px-3 text-right">Price / Unit</th>
                    <th class="py-3 px-3 text-center">Qty</th>
                    <th class="py-3 px-3 text-right">Total (Rs.)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($grn->items as $index => $item)
                    <tr>
                        <td class="py-3.5 px-3 text-center text-xs text-gray-500 font-mono">{{ $index + 1 }}</td>
                        <td class="py-3.5 px-3 font-mono font-bold text-xs text-gray-900">{{ $item->product_code }}</td>
                        <td class="py-3.5 px-3 font-semibold text-gray-800">
                            {{ $item->product_name }}
                            @if(!empty($item->color_name))
                                <span class="inline-block ml-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-purple-100 text-purple-800 border border-purple-200">
                                    Color: {{ $item->color_name }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 text-center">
                            <span class="inline-block px-2 py-0.5 text-[11px] font-semibold rounded {{ $item->product_condition === 'Brandnew' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $item->product_condition }}
                            </span>
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono text-gray-700">Rs. {{ number_format((float) $item->price_per_unit, 2) }}</td>
                        <td class="py-3.5 px-3 text-center font-bold text-gray-900">{{ $item->product_quantity }}</td>
                        <td class="py-3.5 px-3 text-right font-mono font-bold text-gray-900">Rs. {{ number_format((float) $item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-gray-900">
                    <td colspan="6" class="py-4 px-3 text-right text-xs uppercase tracking-wider font-bold text-gray-600">
                        Sub Total Amount:
                    </td>
                    <td class="py-4 px-3 text-right font-mono font-black text-lg text-blue-700">
                        Rs. {{ number_format((float) $grn->sub_total, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures & Acceptance -->
        <div class="mt-16 pt-8 border-t border-gray-200 grid grid-cols-3 gap-8 text-center text-xs text-gray-600">
            <div>
                <div class="border-b border-gray-400 pb-1 mb-2"></div>
                <p class="font-semibold text-gray-800">Received By (Store Keeper)</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Signature & Date</p>
            </div>
            <div>
                <div class="border-b border-gray-400 pb-1 mb-2"></div>
                <p class="font-semibold text-gray-800">Quality / Goods Inspected By</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Signature & Date</p>
            </div>
            <div>
                <div class="border-b border-gray-400 pb-1 mb-2"></div>
                <p class="font-semibold text-gray-800">Authorized Manager Approval</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Signature & Stamp</p>
            </div>
        </div>
    </div>
</body>
</html>
