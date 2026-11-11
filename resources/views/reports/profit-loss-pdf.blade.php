<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profit & Loss Analysis Report - {{ $data['filter_meta']['description'] }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 12mm 12mm 12mm;
        }
        * {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        body {
            margin: 0;
            padding: 0;
            color: #0f172a;
            background: #fff;
            font-size: 10.5px;
            line-height: 1.4;
        }

        /* Screen Print Bar */
        @media screen {
            body {
                background: #0b1120;
                padding: 24px;
                color: #0f172a;
            }
            .page-container {
                max-width: 1140px;
                margin: 0 auto;
                background: #ffffff;
                padding: 28px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.4);
                border-radius: 8px;
            }
            .screen-actions {
                max-width: 1140px;
                margin: 0 auto 16px auto;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 8px 16px;
                border-radius: 6px;
                font-weight: 600;
                font-size: 12px;
                text-decoration: none;
                cursor: pointer;
                border: 1px solid transparent;
            }
            .btn-primary {
                background: #0284c7;
                color: #fff;
            }
            .btn-secondary {
                background: #1e293b;
                color: #e2e8f0;
                border-color: #334155;
            }
        }
        @media print {
            .screen-actions {
                display: none !important;
            }
            body {
                background: #fff;
                padding: 0;
            }
            .page-container {
                padding: 0;
                box-shadow: none;
            }
        }

        /* Header */
        .report-header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .header-table {
            display: table;
            width: 100%;
        }
        .header-cell-left {
            display: table-cell;
            vertical-align: top;
            width: 60%;
        }
        .header-cell-right {
            display: table-cell;
            vertical-align: top;
            width: 40%;
            text-align: right;
        }
        .brand-title {
            font-size: 19px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 3px 0;
        }
        .brand-tagline {
            font-size: 10px;
            color: #64748b;
            margin: 0 0 8px 0;
        }
        .badge-title {
            display: inline-block;
            background: #0284c7;
            color: #ffffff;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .filter-scope-badge {
            display: inline-block;
            background: #f1f5f9;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 600;
            border-radius: 4px;
            margin-top: 4px;
        }
        .meta-line {
            font-size: 10px;
            color: #475569;
            margin-bottom: 3px;
        }
        .meta-line strong {
            color: #0f172a;
        }

        /* KPI Cards Table */
        .kpi-table {
            display: table;
            width: 100%;
            margin-bottom: 16px;
            border-collapse: separate;
            border-spacing: 8px 0;
        }
        .kpi-col {
            display: table-cell;
            width: 25%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            vertical-align: top;
        }
        .kpi-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .kpi-amount {
            font-size: 17px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 4px;
            color: #0f172a;
        }
        .kpi-sub {
            font-size: 9.5px;
            color: #64748b;
        }

        .kpi-pill {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: 700;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .pill-emerald { background: #dcfce7; color: #166534; }
        .pill-red { background: #fee2e2; color: #991b1b; }
        .pill-cyan { background: #e0f2fe; color: #0369a1; }
        .pill-slate { background: #f1f5f9; color: #475569; }

        /* Product Table */
        .table-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin: 0 0 8px 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #cbd5e1;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }
        table.data-table th {
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            text-align: left;
            padding: 6px 8px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-top: 1px solid #0f172a;
            border-bottom: 1px solid #0f172a;
        }
        table.data-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            color: #334155;
        }
        table.data-table tr:nth-child(even) td {
            background: #f8fafc;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }
        .text-emerald { color: #15803d; }
        .text-red { color: #b91c1c; }
        .text-cyan { color: #0284c7; }

        /* Report Footer */
        .report-footer {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px solid #cbd5e1;
            font-size: 9px;
            color: #64748b;
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            vertical-align: middle;
        }
        .footer-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }
    </style>
</head>
<body>

@if($isPrint)
<div class="screen-actions">
    <div>
        <a href="{{ url('/admin') }}" class="btn btn-secondary">
            &larr; Back to Dashboard
        </a>
    </div>
    <div style="display: flex; gap: 8px;">
        <button onclick="window.print()" class="btn btn-secondary">
            Print Report
        </button>
        <a href="{{ route('admin.profit-loss.pdf', request()->query()) }}" class="btn btn-primary">
            Download PDF
        </a>
    </div>
</div>
@endif

<div class="page-container">
    {{-- Header --}}
    <div class="report-header">
        <div class="header-table">
            <div class="header-cell-left">
                <div class="brand-title">ComLink Computers & Accessories</div>
                <div class="brand-tagline">Battlestation Hardware & Technology Solutions | Profit / Loss Analysis</div>
                <div class="badge-title">Executive Profit & Loss Analysis</div>
                <div style="margin-top: 5px;">
                    <span class="filter-scope-badge">
                        Scope: {{ $data['filter_meta']['description'] }}
                    </span>
                </div>
            </div>
            <div class="header-cell-right">
                <div class="meta-line">Generated: <strong>{{ $generatedAt }}</strong></div>
                <div class="meta-line">Report Ref: <strong>PL-{{ now()->format('YmdHi') }}</strong></div>
                <div class="meta-line">Matched Products: <strong>{{ $data['summary']['products_count'] }} items</strong></div>
                <div class="meta-line">Status: <strong class="{{ $data['summary']['is_profitable'] ? 'text-emerald' : 'text-red' }}">{{ $data['summary']['is_profitable'] ? 'PROFITABLE (+)' : 'LOSS (-)' }}</strong></div>
            </div>
        </div>
    </div>

    {{-- 4 Metric Summary Cards --}}
    <div class="kpi-table">
        {{-- Card 1: Total GRN Value --}}
        <div class="kpi-col">
            <div class="kpi-label">Total GRN Value (Procurement)</div>
            <div class="kpi-amount" style="color: #4338ca;">
                Rs. {{ number_format($data['summary']['total_grn_value'], 2) }}
            </div>
            <div class="kpi-sub">
                <span class="kpi-pill pill-slate">{{ $data['summary']['products_count'] }} Total Products</span>
            </div>
        </div>

        {{-- Card 2: Product Stock Breakdown --}}
        <div class="kpi-col">
            <div class="kpi-label">Stock Movement (Units)</div>
            <div class="kpi-amount" style="color: #0f172a;">
                {{ number_format($data['summary']['total_stock_count']) }} <span style="font-size: 11px; font-weight: normal; color: #64748b;">Total Units</span>
            </div>
            <div class="kpi-sub">
                <span class="kpi-pill pill-emerald">{{ number_format($data['summary']['available_stock_count']) }} Available</span>
                <span class="kpi-pill pill-cyan">{{ number_format($data['summary']['sold_stock_count']) }} Sold</span>
            </div>
        </div>

        {{-- Card 3: Gained Sales Income --}}
        <div class="kpi-col">
            <div class="kpi-label">Gained Sales Income (Revenue)</div>
            <div class="kpi-amount" style="color: #0284c7;">
                Rs. {{ number_format($data['summary']['gained_income'], 2) }}
            </div>
            <div class="kpi-sub">
                <span>COGS: Rs. {{ number_format($data['summary']['cogs'], 2) }}</span>
            </div>
        </div>

        {{-- Card 4: Net Profit / Loss --}}
        <div class="kpi-col" style="background: {{ $data['summary']['is_profitable'] ? '#f0fdf4' : '#fef2f2' }}; border-color: {{ $data['summary']['is_profitable'] ? '#86efac' : '#fca5a5' }};">
            <div class="kpi-label" style="color: {{ $data['summary']['is_profitable'] ? '#166534' : '#991b1b' }};">Net Profit / Loss</div>
            <div class="kpi-amount" style="color: {{ $data['summary']['is_profitable'] ? '#15803d' : '#b91c1c' }};">
                {{ $data['summary']['net_profit'] >= 0 ? '+' : '' }}Rs. {{ number_format($data['summary']['net_profit'], 2) }}
            </div>
            <div class="kpi-sub">
                <span class="kpi-pill {{ $data['summary']['is_profitable'] ? 'pill-emerald' : 'pill-red' }}">
                    {{ $data['summary']['profit_margin'] }}% Margin
                </span>
                <span style="font-weight: 700; color: {{ $data['summary']['is_profitable'] ? '#166534' : '#991b1b' }};">
                    {{ $data['summary']['is_profitable'] ? 'Profitable' : 'Loss' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Product Breakdown Table --}}
    <div class="table-title">Product Profit / Loss Breakdown ({{ count($data['products']) }} Items)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 12%;">Code</th>
                <th style="width: 25%;">Product Name</th>
                <th style="width: 13%;">Category</th>
                <th class="text-center" style="width: 7%;">Total</th>
                <th class="text-center" style="width: 7%;">Available</th>
                <th class="text-center" style="width: 6%;">Sold</th>
                <th class="text-right" style="width: 8%;">Unit Cost</th>
                <th class="text-right" style="width: 8%;">Unit Price</th>
                <th class="text-right" style="width: 9%;">Total GRN</th>
                <th class="text-right" style="width: 9%;">Sales Income</th>
                <th class="text-right" style="width: 9%;">Net Profit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['products'] as $idx => $prod)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td style="font-family: monospace; font-size: 8.5px;">{{ $prod['product_code'] ?: '-' }}</td>
                <td class="font-bold">{{ $prod['name'] }}</td>
                <td style="font-size: 8.5px; color: #64748b;">{{ $prod['categories'] }}</td>
                <td class="text-center">{{ $prod['total_stock'] }}</td>
                <td class="text-center font-bold {{ $prod['available_stock'] <= 5 ? 'text-red' : 'text-emerald' }}">{{ $prod['available_stock'] }}</td>
                <td class="text-center">{{ $prod['sold_stock'] }}</td>
                <td class="text-right">{{ number_format($prod['unit_cost'], 2) }}</td>
                <td class="text-right">{{ number_format($prod['selling_price'], 2) }}</td>
                <td class="text-right">{{ number_format($prod['grn_value'], 2) }}</td>
                <td class="text-right font-bold text-cyan">{{ number_format($prod['gained_income'], 2) }}</td>
                <td class="text-right font-bold {{ $prod['is_profitable'] ? 'text-emerald' : 'text-red' }}">
                    {{ $prod['net_profit'] >= 0 ? '+' : '' }}{{ number_format($prod['net_profit'], 2) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" style="text-align: center; padding: 20px; color: #64748b;">
                    No products found matching the selected filter criteria.
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #0f172a; color: #ffffff; font-weight: 700;">
                <td colspan="4" style="color: #ffffff; padding: 7px 8px;">TOTAL SUMMARY</td>
                <td class="text-center" style="color: #ffffff;">{{ number_format($data['summary']['total_stock_count']) }}</td>
                <td class="text-center" style="color: #86efac;">{{ number_format($data['summary']['available_stock_count']) }}</td>
                <td class="text-center" style="color: #38bdf8;">{{ number_format($data['summary']['sold_stock_count']) }}</td>
                <td colspan="2"></td>
                <td class="text-right" style="color: #c7d2fe;">Rs. {{ number_format($data['summary']['total_grn_value'], 2) }}</td>
                <td class="text-right" style="color: #38bdf8;">Rs. {{ number_format($data['summary']['gained_income'], 2) }}</td>
                <td class="text-right" style="color: {{ $data['summary']['is_profitable'] ? '#86efac' : '#fca5a5' }};">
                    {{ $data['summary']['net_profit'] >= 0 ? '+' : '' }}Rs. {{ number_format($data['summary']['net_profit'], 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Footer --}}
    <div class="report-footer">
        <div class="footer-left">
            ComLink e-Commerce Management System &bull; Confidential Business Analytics &bull; System Generated
        </div>
        <div class="footer-right">
            Page 1 of 1
        </div>
    </div>
</div>

</body>
</html>

