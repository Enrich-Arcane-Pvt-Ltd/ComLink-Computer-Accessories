<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock & Profitability Analysis Report - {{ $data['period_label'] }}</title>
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
            color: #1e293b;
            background: #fff;
            font-size: 11px;
            line-height: 1.4;
        }

        /* Screen Print Bar */
        @media screen {
            body {
                background: #f1f5f9;
                padding: 20px;
            }
            .page-container {
                max-width: 1120px;
                margin: 0 auto;
                background: #fff;
                padding: 28px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.08);
                border-radius: 8px;
            }
            .screen-actions {
                max-width: 1120px;
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
                background: #2563eb;
                color: #fff;
            }
            .btn-secondary {
                background: #fff;
                color: #475569;
                border-color: #cbd5e1;
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
        .header-top {
            display: table;
            width: 100%;
        }
        .header-left {
            display: table-cell;
            vertical-align: top;
            width: 65%;
        }
        .header-right {
            display: table-cell;
            vertical-align: top;
            width: 35%;
            text-align: right;
        }
        .company-name {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 2px 0;
        }
        .company-tagline {
            font-size: 10px;
            color: #64748b;
            margin: 0 0 6px 0;
        }
        .report-title-badge {
            display: inline-block;
            background: #0f172a;
            color: #fff;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .meta-item {
            font-size: 10px;
            color: #475569;
            margin-bottom: 3px;
        }
        .meta-item strong {
            color: #0f172a;
        }

        /* KPI Cards Grid */
        .kpi-grid {
            display: table;
            width: 100%;
            margin-bottom: 16px;
            border-collapse: separate;
            border-spacing: 8px 0;
        }
        .kpi-cell {
            display: table-cell;
            width: 20%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            vertical-align: top;
        }
        .kpi-title {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .kpi-value.positive { color: #059669; }
        .kpi-value.negative { color: #dc2626; }
        .kpi-value.accent { color: #2563eb; }
        .kpi-sub {
            font-size: 9px;
            color: #64748b;
        }

        /* Product Performance Table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        table.data-table th {
            background: #f1f5f9;
            color: #1e293b;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 7px 6px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        table.data-table th.center, table.data-table td.center {
            text-align: center;
        }
        table.data-table th.right, table.data-table td.right {
            text-align: right;
        }
        table.data-table td {
            padding: 6px 6px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
            vertical-align: middle;
        }
        table.data-table tr:nth-child(even) td {
            background: #f8fafc;
        }
        table.data-table tfoot td {
            background: #e2e8f0;
            font-weight: 700;
            border-top: 2px solid #0f172a;
            font-size: 10px;
        }

        .prod-title {
            font-weight: 700;
            color: #0f172a;
        }
        .prod-sub {
            font-size: 9px;
            color: #64748b;
        }
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-gray { background: #f1f5f9; color: #475569; }

        /* Footer */
        .report-footer {
            margin-top: 20px;
            border-top: 1px solid #cbd5e1;
            padding-top: 10px;
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            width: 50%;
            font-size: 9px;
            color: #64748b;
        }
        .footer-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            font-size: 9px;
            color: #64748b;
        }
    </style>
</head>
<body @if(!empty($isPrint)) onload="window.print()" @endif>

    @if(!empty($isPrint))
    <div class="screen-actions">
        <a href="javascript:history.back()" class="btn btn-secondary">
            &larr; Back to Dashboard
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            Print Report
        </button>
    </div>
    @endif

    <div class="page-container">
        <!-- Header -->
        <div class="report-header">
            <div class="header-top">
                <div class="header-left">
                    <h1 class="company-name">ComLink Computer Accessories Shop</h1>
                    <p class="company-tagline">Premium Hardware & Accessories &bull; Inventory & Stock Management System</p>
                    <span class="report-title-badge">Stock & Profitability Analysis Report</span>
                </div>
                <div class="header-right">
                    <div class="meta-item">Period: <strong>{{ $data['period_label'] }}</strong></div>
                    <div class="meta-item">Generated: <strong>{{ $data['generated_at'] }}</strong></div>
                    <div class="meta-item">Catalog Products: <strong>{{ $data['metrics']['total_products_count'] }} items</strong></div>
                </div>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="kpi-grid">
            <div class="kpi-cell">
                <div class="kpi-title">Total Purchase Cost</div>
                <div class="kpi-value">Rs. {{ number_format($data['metrics']['total_expense'], 2) }}</div>
                <div class="kpi-sub">{{ number_format($data['metrics']['total_purchased_qty']) }} units purchased in period</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-title">Sales Revenue</div>
                <div class="kpi-value positive">Rs. {{ number_format($data['metrics']['total_revenue'], 2) }}</div>
                <div class="kpi-sub">{{ number_format($data['metrics']['total_sold_qty']) }} units sold via paid orders</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-title">Cost of Sold Goods</div>
                <div class="kpi-value">Rs. {{ number_format($data['metrics']['total_cogs'], 2) }}</div>
                <div class="kpi-sub">Purchase cost base of sold units</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-title">Gross Profit (Gain)</div>
                <div class="kpi-value {{ $data['metrics']['total_gross_profit'] >= 0 ? 'positive' : 'negative' }}">
                    {{ $data['metrics']['total_gross_profit'] >= 0 ? '+' : '' }}Rs. {{ number_format($data['metrics']['total_gross_profit'], 2) }}
                </div>
                <div class="kpi-sub">Margin: <strong>{{ $data['metrics']['overall_profit_margin'] }}%</strong></div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-title">Unsold Stock Value</div>
                <div class="kpi-value accent">Rs. {{ number_format($data['metrics']['total_stock_value'], 2) }}</div>
                <div class="kpi-sub">{{ number_format($data['metrics']['total_remaining_stock']) }} physical units in hand</div>
            </div>
        </div>

        <!-- Main Product Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;" class="center">#</th>
                    <th style="width: 200px;">Product Details</th>
                    <th style="width: 60px;" class="center">Purchased</th>
                    <th style="width: 85px;" class="right">Avg Cost</th>
                    <th style="width: 95px;" class="right">Purchase Expense</th>
                    <th style="width: 55px;" class="center">Sold</th>
                    <th style="width: 85px;" class="right">Avg Price</th>
                    <th style="width: 95px;" class="right">Sales Revenue</th>
                    <th style="width: 85px;" class="right">COGS</th>
                    <th style="width: 95px;" class="right">Gross Profit</th>
                    <th style="width: 55px;" class="center">Margin %</th>
                    <th style="width: 60px;" class="center">In Stock</th>
                    <th style="width: 95px;" class="right">Stock Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['products'] as $index => $row)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>
                            <div class="prod-title">{{ $row['name'] }}</div>
                            <div class="prod-sub">
                                Code: <strong>{{ $row['product_code'] }}</strong> &bull;
                                Condition: <strong>{{ $row['condition'] }}</strong> &bull;
                                Supplier: {{ $row['supplier_name'] }}
                            </div>
                            @if(!empty($row['color_breakdown']))
                                <div style="margin-top: 3px; font-size: 8px;">
                                    <strong style="color: #7c3aed;">Colors:</strong>
                                    @foreach($row['color_breakdown'] as $cb)
                                        <span style="display: inline-block; background: #f3e8ff; color: #6b21a8; padding: 1px 3px; border-radius: 2px; margin-right: 2px;">
                                            {{ $cb['color'] }}: {{ $cb['stock'] }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="center font-bold">{{ number_format($row['purchased_qty']) }}</td>
                        <td class="right">Rs. {{ number_format($row['avg_purchase_price'], 2) }}</td>
                        <td class="right font-bold">Rs. {{ number_format($row['purchase_expense'], 2) }}</td>
                        
                        <td class="center font-bold">{{ number_format($row['sold_qty']) }}</td>
                        <td class="right">Rs. {{ number_format($row['avg_selling_price'], 2) }}</td>
                        <td class="right font-bold" style="color: #059669;">Rs. {{ number_format($row['sales_revenue'], 2) }}</td>
                        
                        <td class="right">Rs. {{ number_format($row['cogs'], 2) }}</td>
                        <td class="right font-bold" style="color: {{ $row['gross_profit'] >= 0 ? '#059669' : '#dc2626' }};">
                            {{ $row['gross_profit'] >= 0 ? '+' : '' }}Rs. {{ number_format($row['gross_profit'], 2) }}
                        </td>
                        <td class="center">
                            <span class="badge {{ $row['profit_margin_pct'] >= 15 ? 'badge-green' : ($row['profit_margin_pct'] > 0 ? 'badge-amber' : 'badge-gray') }}">
                                {{ $row['profit_margin_pct'] }}%
                            </span>
                        </td>
                        <td class="center">
                            <strong>{{ number_format($row['current_stock']) }}</strong>
                            <div style="font-size: 8px; color: #64748b;">(W:{{ $row['warehouse_stock'] }}|S:{{ $row['web_stock'] }})</div>
                        </td>
                        <td class="right font-bold" style="color: #2563eb;">Rs. {{ number_format($row['remaining_stock_value'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="center" style="padding: 20px; color: #64748b;">No products found matching the selected filter criteria.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="right">TOTALS:</td>
                    <td class="center">{{ number_format($data['metrics']['total_purchased_qty']) }}</td>
                    <td>-</td>
                    <td class="right">Rs. {{ number_format($data['metrics']['total_expense'], 2) }}</td>
                    <td class="center">{{ number_format($data['metrics']['total_sold_qty']) }}</td>
                    <td>-</td>
                    <td class="right" style="color: #059669;">Rs. {{ number_format($data['metrics']['total_revenue'], 2) }}</td>
                    <td class="right">Rs. {{ number_format($data['metrics']['total_cogs'], 2) }}</td>
                    <td class="right" style="color: {{ $data['metrics']['total_gross_profit'] >= 0 ? '#059669' : '#dc2626' }};">
                        {{ $data['metrics']['total_gross_profit'] >= 0 ? '+' : '' }}Rs. {{ number_format($data['metrics']['total_gross_profit'], 2) }}
                    </td>
                    <td class="center">
                        <span class="badge {{ $data['metrics']['overall_profit_margin'] >= 15 ? 'badge-green' : 'badge-amber' }}">
                            {{ $data['metrics']['overall_profit_margin'] }}%
                        </span>
                    </td>
                    <td class="center">{{ number_format($data['metrics']['total_remaining_stock']) }}</td>
                    <td class="right" style="color: #2563eb;">Rs. {{ number_format($data['metrics']['total_stock_value'], 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Footer -->
        <div class="report-footer">
            <div class="footer-left">
                ComLink Computer Accessories Shop &bull; Confidential Management Report &bull; Page 1 of 1
            </div>
            <div class="footer-right">
                This document is a system-generated audit report. All transactions are backed by Goods Received Notes and customer payment logs.
            </div>
        </div>
    </div>
</body>
</html>
