<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $report->report_number }} - {{ $report->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        body {
            background-color: #ffffff;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
            padding: 24px;
        }
        .header {
            border-bottom: 2px solid #0284c7;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header table {
            width: 100%;
        }
        .company-name {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .company-subtitle {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }
        .report-title {
            text-align: right;
        }
        .report-title h1 {
            font-size: 15px;
            font-weight: 800;
            color: #0284c7;
            text-transform: uppercase;
        }
        .report-badge {
            display: inline-block;
            background-color: #e0f2fe;
            color: #0369a1;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            margin-top: 3px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 14px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
        }
        .meta-table td {
            padding: 3px 6px;
            font-size: 10.5px;
        }
        .meta-label {
            color: #64748b;
            font-weight: 600;
            width: 15%;
        }
        .meta-val {
            color: #0f172a;
            font-weight: 700;
        }
        /* Summary KPIs */
        .kpi-table {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: separate;
            border-spacing: 8px 0;
        }
        .kpi-card {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 10px;
            text-align: center;
        }
        .kpi-label {
            font-size: 9.5px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }
        .kpi-value {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 3px;
        }
        .kpi-primary {
            border-top: 3px solid #0284c7;
        }
        .kpi-success {
            border-top: 3px solid #10b981;
        }
        .kpi-warning {
            border-top: 3px solid #f59e0b;
        }
        .kpi-purple {
            border-top: 3px solid #8b5cf6;
        }
        /* Section Title */
        .section-title {
            font-size: 11.5px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-top: 14px;
            margin-bottom: 8px;
        }
        /* Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        table.data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
            color: #1e293b;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: 700;
        }
        .footer-note {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px dashed #cbd5e1;
            display: flex;
            justify-content: space-between;
        }
        .sign-table {
            width: 100%;
            margin-top: 28px;
        }
        .sign-box {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #94a3b8;
            font-size: 9.5px;
            font-weight: 600;
            color: #475569;
        }
        .no-print-bar {
            background: #0f172a;
            color: #ffffff;
            padding: 10px 20px;
            position: sticky;
            top: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: -24px -24px 20px -24px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #0284c7;
            color: #ffffff;
            text-decoration: none;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 12px;
            border: none;
            cursor: pointer;
        }
        .btn-gray {
            background: #334155;
        }
        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>

@if(!isset($isPdf) || ! $isPdf)
<div class="no-print-bar">
    <div style="font-weight: 700; font-size: 14px;">
        📄 GRN Analysis Report Preview &mdash; {{ $report->report_number }}
    </div>
    <div style="display: flex; gap: 8px;">
        <button onclick="window.print()" class="btn">
            🖨️ Print Report
        </button>
        <a href="{{ route('admin.grn-analyzer.pdf', $report->id) }}" class="btn btn-gray" target="_blank">
            ⬇️ Download PDF
        </a>
    </div>
</div>
@endif

{{-- Header --}}
<div class="header">
    <table>
        <tr>
            <td style="width: 60%;">
                <div class="company-name">COMLINK COMPUTER ACCESSORIES</div>
                <div class="company-subtitle">Inventory & Procurement Management Division | Vicky Store Storefront</div>
            </td>
            <td class="report-title" style="width: 40%;">
                <h1>GRN Analysis Report</h1>
                <div class="report-badge">{{ strtoupper(str_replace('_', ' ', $report->analysis_type)) }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- Metadata --}}
<div class="meta-table">
    <table>
        <tr>
            <td class="meta-label">Report Number:</td>
            <td class="meta-val">{{ $report->report_number }}</td>
            <td class="meta-label">Analysis Type:</td>
            <td class="meta-val">{{ $report->analysis_type === 'supplier_wise' ? 'Supplier-Wise Analysis' : 'Product-Wise Analysis' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Target Scope:</td>
            <td class="meta-val">{{ $report->report_data['scope_label'] ?? 'All' }}</td>
            <td class="meta-label">Period Covered:</td>
            <td class="meta-val">{{ \Carbon\Carbon::parse($report->date_from)->format('M d, Y') }} &mdash; {{ \Carbon\Carbon::parse($report->date_to)->format('M d, Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Generated Date:</td>
            <td class="meta-val">{{ $report->created_at->format('M d, Y h:i A') }}</td>
            <td class="meta-label">Generated By:</td>
            <td class="meta-val">{{ $report->creator?->name ?? 'System Admin' }}</td>
        </tr>
        @if($report->notes)
        <tr>
            <td class="meta-label">Notes:</td>
            <td class="meta-val" colspan="3">{{ $report->notes }}</td>
        </tr>
        @endif
    </table>
</div>

{{-- KPI Summary Cards --}}
<table class="kpi-table">
    <tr>
        <td style="width: 28%;">
            <div class="kpi-card kpi-primary">
                <div class="kpi-label">Total Procurement Spend</div>
                <div class="kpi-value">Rs. {{ number_format($report->total_amount, 2) }}</div>
            </div>
        </td>
        <td style="width: 24%;">
            <div class="kpi-card kpi-success">
                <div class="kpi-label">Total Quantity Received</div>
                <div class="kpi-value">{{ number_format($report->total_quantity) }} units</div>
            </div>
        </td>
        <td style="width: 24%;">
            <div class="kpi-card kpi-warning">
                <div class="kpi-label">Total GRN Orders</div>
                <div class="kpi-value">{{ $report->total_grns }} batches</div>
            </div>
        </td>
        <td style="width: 24%;">
            <div class="kpi-card kpi-purple">
                <div class="kpi-label">Distinct Products / Items</div>
                <div class="kpi-value">{{ count($report->report_data['products_summary'] ?? []) }} products</div>
            </div>
        </td>
    </tr>
</table>

{{-- Section: Supplier Breakdown --}}
@if(!empty($report->report_data['suppliers_summary']))
<div class="section-title">1. Supplier Procurement Breakdown</div>
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 45%;">Supplier Name</th>
            <th class="text-center" style="width: 12%;">GRN Count</th>
            <th class="text-right" style="width: 15%;">Quantity Received</th>
            <th class="text-right" style="width: 13%;">Total Value (Rs.)</th>
            <th class="text-right" style="width: 10%;">Share %</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report->report_data['suppliers_summary'] as $index => $sup)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td class="font-bold">{{ $sup['supplier_name'] }}</td>
            <td class="text-center">{{ $sup['grn_count'] }}</td>
            <td class="text-right">{{ number_format($sup['total_quantity']) }}</td>
            <td class="text-right font-bold">{{ number_format($sup['total_amount'], 2) }}</td>
            <td class="text-right">{{ $sup['percentage'] }}%</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Section: Product Breakdown --}}
@if(!empty($report->report_data['products_summary']))
<div class="section-title">2. Product Procurement Breakdown</div>
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 15%;">Product Code</th>
            <th style="width: 35%;">Product Name</th>
            <th class="text-center" style="width: 12%;">Condition</th>
            <th class="text-right" style="width: 10%;">Qty</th>
            <th class="text-right" style="width: 13%;">Avg Unit Price</th>
            <th class="text-right" style="width: 15%;">Total Value (Rs.)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report->report_data['products_summary'] as $index => $prod)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td class="font-bold" style="color: #0284c7;">{{ $prod['product_code'] }}</td>
            <td>{{ $prod['product_name'] }}</td>
            <td class="text-center">{{ $prod['product_condition'] }}</td>
            <td class="text-right font-bold">{{ number_format($prod['total_quantity']) }}</td>
            <td class="text-right">{{ number_format($prod['avg_price'], 2) }}</td>
            <td class="text-right font-bold">{{ number_format($prod['total_amount'], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Section: Detailed GRN Items Ledger --}}
@if(!empty($report->report_data['detailed_items']))
<div class="section-title">3. Itemized GRN Receipts Ledger</div>
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 12%;">GRN No</th>
            <th style="width: 13%;">Date</th>
            <th style="width: 25%;">Supplier</th>
            <th style="width: 25%;">Product</th>
            <th class="text-center" style="width: 7%;">Qty</th>
            <th class="text-right" style="width: 12%;">Price (Rs.)</th>
            <th class="text-right" style="width: 14%;">Total (Rs.)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report->report_data['detailed_items'] as $item)
        <tr>
            <td class="font-bold" style="color: #0284c7;">{{ $item['grn_number'] }}</td>
            <td>{{ $item['date'] }}</td>
            <td>{{ $item['supplier_name'] }}</td>
            <td>
                <strong>{{ $item['product_code'] }}</strong> - {{ $item['product_name'] }}
                <span style="font-size: 8.5px; color: #64748b;">({{ $item['product_condition'] }})</span>
            </td>
            <td class="text-center font-bold">{{ $item['quantity'] }}</td>
            <td class="text-right">{{ number_format($item['price_per_unit'], 2) }}</td>
            <td class="text-right font-bold">{{ number_format($item['total'], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background-color: #f1f5f9;">
            <td colspan="4" class="font-bold text-right" style="padding: 6px 8px;">GRAND TOTAL:</td>
            <td class="font-bold text-center" style="padding: 6px 8px;">{{ number_format($report->total_quantity) }}</td>
            <td></td>
            <td class="font-bold text-right" style="padding: 6px 8px; color: #0284c7; font-size: 11px;">Rs. {{ number_format($report->total_amount, 2) }}</td>
        </tr>
    </tfoot>
</table>
@endif

{{-- Sign-off block --}}
<table class="sign-table">
    <tr>
        <td style="width: 30%;"><div class="sign-box">Prepared By (Inventory Executive)</div></td>
        <td style="width: 5%;"></td>
        <td style="width: 30%;"><div class="sign-box">Verified By (Accounts / Stores)</div></td>
        <td style="width: 5%;"></td>
        <td style="width: 30%;"><div class="sign-box">Authorized Signatory (Management)</div></td>
    </tr>
</table>

</body>
</html>
