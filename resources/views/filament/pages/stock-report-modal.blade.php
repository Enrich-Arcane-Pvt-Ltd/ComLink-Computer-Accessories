@php
    $report = $reportData ?? $report ?? [];
    $product = $report['product'] ?? [];
    $summary = $report['summary'] ?? [];
    $batches = $report['batches'] ?? [];

    $allTimeProfit = (float) ($summary['total_realized_profit'] ?? 0);
    $isAllTimeProfitable = $allTimeProfit >= 0;
    $allTimeMargin = (float) ($summary['realized_margin_pct'] ?? 0);
@endphp

<div class="stock-report-wrapper" style="color: #f1f5f9; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <style>
        .stock-report-wrapper {
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 16px;
            width: 100%;
        }
        .stock-report-wrapper * {
            box-sizing: border-box;
        }

        /* 1. Header Banner */
        .sr-banner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 20px;
            background: linear-gradient(135deg, #090e17 0%, #111a28 60%, #0d2232 100%);
            border: 1px solid rgba(6, 182, 212, 0.3);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        .sr-product-left {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
            flex: 1 1 300px;
        }
        .sr-thumb {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid rgba(6, 182, 212, 0.4);
            background: #1e293b;
            flex-shrink: 0;
        }
        .sr-badges {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
        }
        .sr-badge-code {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            background: rgba(6, 182, 212, 0.15);
            color: #22d3ee;
            border: 1px solid rgba(6, 182, 212, 0.35);
        }
        .sr-badge-cond {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.35);
        }
        .sr-badge-sup {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            background: rgba(148, 163, 184, 0.12);
            color: #cbd5e1;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        .sr-product-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.3;
        }
        .sr-price-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            text-align: right;
            flex-shrink: 0;
        }
        .sr-price-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #67e8f9;
        }
        .sr-price-val {
            font-size: 22px;
            font-weight: 800;
            font-family: ui-monospace, monospace;
            color: #22d3ee;
            line-height: 1.2;
            margin: 2px 0;
        }
        .sr-cost-val {
            font-size: 11px;
            font-family: ui-monospace, monospace;
            color: #94a3b8;
        }

        /* 2. Top Summary Metrics Grid */
        .sr-metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
            width: 100%;
        }
        .sr-metric-card {
            background: #111a28;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 80px;
        }
        .sr-metric-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
        }
        .sr-metric-val {
            font-size: 17px;
            font-weight: 800;
            font-family: ui-monospace, monospace;
            line-height: 1.2;
            margin: 3px 0 1px 0;
        }
        .sr-metric-sub {
            font-size: 10px;
            color: #64748b;
        }

        /* 3. Product All-Time Sales Profit & Loss Highlight Section */
        .sr-profit-section {
            background: linear-gradient(135deg, #0a1320 0%, #101c2e 100%);
            border: 1px solid {{ $isAllTimeProfitable ? 'rgba(16, 185, 129, 0.35)' : 'rgba(239, 68, 68, 0.35)' }};
            border-radius: 12px;
            padding: 14px 18px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
        }
        .sr-profit-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 8px;
        }
        .sr-profit-title {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #f1f5f9;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sr-profit-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }
        .sr-profit-item {
            background: #090e17;
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 8px;
            padding: 10px 14px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .sr-profit-item-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #94a3b8;
        }
        .sr-profit-item-val {
            font-size: 18px;
            font-weight: 800;
            font-family: ui-monospace, monospace;
            line-height: 1.25;
            margin: 2px 0;
        }
        .sr-profit-item-sub {
            font-size: 10px;
            color: #64748b;
        }

        /* 4. Table Box */
        .sr-table-card {
            background: #0c1420;
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }
        .sr-table-header {
            padding: 12px 18px;
            background: #131d2e;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .sr-table-title {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            color: #f1f5f9;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sr-legend {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            font-size: 10px;
            font-weight: 600;
        }
        .sr-legend-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 2px 8px;
            border-radius: 9999px;
        }

        /* Main Table */
        .sr-table-scroll {
            width: 100%;
            overflow-x: auto;
        }
        .sr-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 12px;
            min-width: 980px;
        }
        .sr-table th {
            padding: 10px 12px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #94a3b8;
            background: #111a28;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            white-space: nowrap;
        }
        .sr-table td {
            padding: 11px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
            vertical-align: middle;
        }
        .sr-row-active {
            background: rgba(16, 185, 129, 0.05);
        }
        .sr-row-ended {
            opacity: 0.85;
        }

        /* Status Pills */
        .sr-status-ended {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.35);
            white-space: nowrap;
        }
        .sr-status-active {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
            background: rgba(16, 185, 129, 0.18);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.45);
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);
            white-space: nowrap;
        }
        .sr-status-queued {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
            background: rgba(14, 165, 233, 0.15);
            color: #38bdf8;
            border: 1px solid rgba(14, 165, 233, 0.35);
            white-space: nowrap;
        }

        /* Sub row price breakdown */
        .sr-sub-row td {
            background: #090e17;
            padding: 8px 16px 10px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .sr-breakdown-box {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 11px;
        }
        .sr-breakdown-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #141c2b;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            padding: 4px 9px;
            color: #cbd5e1;
            font-family: ui-monospace, monospace;
        }

        /* Note Callout */
        .sr-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 10px;
            background: rgba(6, 182, 212, 0.06);
            border: 1px solid rgba(6, 182, 212, 0.2);
            font-size: 11px;
            line-height: 1.45;
            color: #94a3b8;
        }
        .sr-note strong {
            color: #e2e8f0;
        }
    </style>

    {{-- 1. Product Header Banner --}}
    <div class="sr-banner">
        <div class="sr-product-left">
            <img 
                src="{{ $product['image_url'] ?? '/images/placeholder.png' }}" 
                alt="{{ $product['name'] ?? 'Product' }}" 
                class="sr-thumb"
                onerror="this.src='/images/placeholder.png'"
            />
            <div style="min-width: 0;">
                <div class="sr-badges">
                    <span class="sr-badge-code">
                        Code: {{ $product['product_code'] ?? 'N/A' }}
                    </span>
                    <span class="sr-badge-cond">
                        {{ $product['condition'] ?? 'Brand New' }}
                    </span>
                    <span class="sr-badge-sup">
                        Supplier: {{ $product['supplier_name'] ?? 'Direct Stock' }}
                    </span>
                </div>
                <h2 class="sr-product-title">
                    {{ $product['name'] ?? 'Unknown Product' }}
                </h2>
            </div>
        </div>

        <div class="sr-price-right">
            <span class="sr-price-label">Current Latest Price</span>
            <div class="sr-price-val">
                Rs. {{ number_format((float) ($product['latest_price'] ?? 0), 2) }}
            </div>
            <div class="sr-cost-val">
                Latest GRN Cost: Rs. {{ number_format((float) ($product['latest_grn_price'] ?? 0), 2) }}
            </div>
        </div>
    </div>

    {{-- 2. Top Summary Metrics Grid (Inventory Flow) --}}
    <div class="sr-metrics-grid">
        {{-- Stock Received --}}
        <div class="sr-metric-card">
            <span class="sr-metric-label">Stock Received</span>
            <div class="sr-metric-val" style="color: #38bdf8;">
                {{ $summary['total_stock'] ?? 0 }} <span style="font-size: 11px; font-weight: normal; color: #64748b;">units</span>
            </div>
            <span class="sr-metric-sub">{{ $summary['total_batches_count'] ?? 0 }} GRN {{ ($summary['total_batches_count'] ?? 0) === 1 ? 'Batch' : 'Batches' }}</span>
        </div>

        {{-- Sold Quantity --}}
        <div class="sr-metric-card">
            <span class="sr-metric-label">Sold Quantity</span>
            <div class="sr-metric-val" style="color: #c084fc;">
                {{ $summary['total_sold'] ?? 0 }} <span style="font-size: 11px; font-weight: normal; color: #64748b;">units</span>
            </div>
            <span class="sr-metric-sub">Paid Customer Orders</span>
        </div>

        {{-- Available Stock --}}
        <div class="sr-metric-card">
            <span class="sr-metric-label">Available Stock</span>
            <div class="sr-metric-val" style="color: {{ ($summary['total_available'] ?? 0) > 0 ? '#34d399' : '#f87171' }};">
                {{ $summary['total_available'] ?? 0 }} <span style="font-size: 11px; font-weight: normal; color: #64748b;">units</span>
            </div>
            <span class="sr-metric-sub">{{ ($summary['total_available'] ?? 0) > 0 ? 'In Store & Warehouse' : 'Out of Stock' }}</span>
        </div>

        {{-- Total Stock Purchase Cost --}}
        <div class="sr-metric-card">
            <span class="sr-metric-label">Total Stock Cost</span>
            <div class="sr-metric-val" style="color: #cbd5e1;" title="Rs. {{ number_format((float) ($summary['total_grn_cost'] ?? 0), 2) }}">
                Rs. {{ number_format((float) ($summary['total_grn_cost'] ?? 0), 2) }}
            </div>
            <span class="sr-metric-sub">Batch Qty × GRN Unit Cost</span>
        </div>

        {{-- Current Selling Stock --}}
        <div class="sr-metric-card">
            <span class="sr-metric-label">Current Selling Stock</span>
            <div style="margin: 4px 0 2px 0; display: flex; flex-wrap: wrap; gap: 4px;">
                @if(!empty($summary['active_batch_codes']))
                    @foreach($summary['active_batch_codes'] as $actCode)
                        <span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; font-family: ui-monospace, monospace; background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);">
                            {{ $actCode }}
                        </span>
                    @endforeach
                @else
                    <span style="display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 11px; font-weight: 600; color: #94a3b8;">
                        None
                    </span>
                @endif
            </div>
            <span class="sr-metric-sub">FIFO Active Priority</span>
        </div>
    </div>

    {{-- 3. Feature 1: Product Profits & Losses From All-Time Sales (Clean & Simple) --}}
    <div class="sr-profit-section">
        <div class="sr-profit-header">
            <h3 class="sr-profit-title">
                <svg style="width: 16px; height: 16px; color: {{ $isAllTimeProfitable ? '#34d399' : '#f87171' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                Product Profit &amp; Loss From All-Time Sales
            </h3>
            @php
                $statusText = $allTimeProfit > 0 ? 'Overall Profitable' : ($allTimeProfit < 0 ? 'Loss Position' : (($summary['total_sold'] ?? 0) > 0 ? 'Break-Even' : 'No Sales Yet'));
                $statusColor = $allTimeProfit > 0 ? '#34d399' : ($allTimeProfit < 0 ? '#f87171' : '#94a3b8');
                $statusBg = $allTimeProfit > 0 ? 'rgba(16, 185, 129, 0.18)' : ($allTimeProfit < 0 ? 'rgba(239, 68, 68, 0.18)' : 'rgba(148, 163, 184, 0.15)');
                $statusBorder = $allTimeProfit > 0 ? 'rgba(16, 185, 129, 0.35)' : ($allTimeProfit < 0 ? 'rgba(239, 68, 68, 0.35)' : 'rgba(148, 163, 184, 0.25)');
            @endphp
            <span style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 700; font-family: ui-monospace, monospace; background: {{ $statusBg }}; color: {{ $statusColor }}; border: 1px solid {{ $statusBorder }};">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $statusColor }};"></span>
                {{ $statusText }} ({{ $allTimeMargin }}% Margin)
            </span>
        </div>

        <div class="sr-profit-grid">
            {{-- All-Time Sales Revenue --}}
            <div class="sr-profit-item">
                <span class="sr-profit-item-label">All-Time Sales Revenue</span>
                <div class="sr-profit-item-val" style="color: #34d399;">
                    Rs. {{ number_format((float) ($summary['total_achieved_revenue'] ?? 0), 2) }}
                </div>
                <span class="sr-profit-item-sub">Total revenue collected from {{ $summary['total_sold'] ?? 0 }} sold units</span>
            </div>

            {{-- Cost of Sold Goods (COGS) --}}
            <div class="sr-profit-item">
                <span class="sr-profit-item-label">Cost of Sold Stock (COGS)</span>
                <div class="sr-profit-item-val" style="color: #94a3b8;">
                    Rs. {{ number_format((float) ($summary['total_sold_cost'] ?? 0), 2) }}
                </div>
                <span class="sr-profit-item-sub">GRN purchase cost base of {{ $summary['total_sold'] ?? 0 }} sold units</span>
            </div>

            {{-- All-Time Net Profit / Loss --}}
            <div class="sr-profit-item" style="border-color: {{ $isAllTimeProfitable ? 'rgba(16, 185, 129, 0.3)' : 'rgba(239, 68, 68, 0.3)' }}; background: {{ $isAllTimeProfitable ? 'rgba(16, 185, 129, 0.06)' : 'rgba(239, 68, 68, 0.06)' }};">
                <span class="sr-profit-item-label" style="color: {{ $isAllTimeProfitable ? '#34d399' : '#f87171' }};">All-Time Net Profit / Loss</span>
                <div class="sr-profit-item-val" style="color: {{ $isAllTimeProfitable ? '#34d399' : '#f87171' }};">
                    {{ $isAllTimeProfitable ? '+' : '' }}Rs. {{ number_format($allTimeProfit, 2) }}
                </div>
                <span class="sr-profit-item-sub" style="color: {{ $isAllTimeProfitable ? '#86efac' : '#fca5a5' }};">
                    Revenue − Cost of Goods Sold ({{ $allTimeMargin }}% margin)
                </span>
            </div>

            {{-- Unsold Stock Asset Value --}}
            <div class="sr-profit-item">
                <span class="sr-profit-item-label">Unsold Stock Asset Value</span>
                <div class="sr-profit-item-val" style="color: #38bdf8;">
                    Rs. {{ number_format((float) ($summary['total_unsold_cost'] ?? 0), 2) }}
                </div>
                <span class="sr-profit-item-sub">{{ $summary['total_available'] ?? 0 }} available units remaining in stock</span>
            </div>
        </div>
    </div>

    {{-- 4. Feature 2: Stock-Wise Batch Breakdown & Selling Price History Table --}}
    <div class="sr-table-card">
        <div class="sr-table-header">
            <h3 class="sr-table-title">
                <svg style="width: 16px; height: 16px; color: #22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Stock-Wise Batch Breakdown &amp; Realized Profits
            </h3>
            <div class="sr-legend">
                <span class="sr-legend-pill" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35);">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #f87171;"></span> Ended
                </span>
                <span class="sr-legend-pill" style="background: rgba(16, 185, 129, 0.18); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.45);">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #34d399;"></span> Current Selling
                </span>
                <span class="sr-legend-pill" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.35);">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #38bdf8;"></span> Upcoming Queue
                </span>
            </div>
        </div>

        @if(empty($batches))
            <div style="padding: 30px; text-align: center; color: #64748b;">
                <div style="font-weight: 700; font-size: 14px; color: #94a3b8;">No Stock Batches (GRNs) Found</div>
                <div style="font-size: 11px; margin-top: 4px;">Receive Goods Received Notes (GRN) to register stock batches for this product.</div>
            </div>
        @else
            <div class="sr-table-scroll">
                <table class="sr-table">
                    <thead>
                        <tr>
                            <th>Stock Code</th>
                            <th>GRN &amp; Date</th>
                            <th style="text-align: center;">Batch Qty</th>
                            <th style="text-align: right;">GRN Unit Cost</th>
                            <th style="text-align: right;">Latest Price</th>
                            <th style="text-align: center;">Sold Qty</th>
                            <th style="text-align: center;">Available</th>
                            <th style="text-align: center;">Stock Status</th>
                            <th style="text-align: right;" title="Batch Qty × GRN Unit Cost">Revenue Price</th>
                            <th style="text-align: right;" title="Total Revenue Collected from Sold Units">Achieved Sale</th>
                            <th style="text-align: right;" title="Realized Profit = Achieved from Sale - Cost of Sold Units">Stock Profit / Loss</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($batches as $batch)
                            @php
                                $status = $batch['status'];
                                $isEnded = $status === 'ENDED';
                                $isActive = $status === 'ACTIVE';
                                $isQueued = $status === 'QUEUED';
                                $rowClass = $isActive ? 'sr-row-active' : ($isEnded ? 'sr-row-ended' : '');
                                $batchSold = (int) ($batch['sold_quantity'] ?? 0);
                                $batchRealizedProfit = (float) ($batch['realized_profit'] ?? 0);
                                $isBatchProfit = $batchRealizedProfit >= 0;
                            @endphp

                            {{-- Main Batch Row --}}
                            <tr class="{{ $rowClass }}">
                                {{-- Stock Code --}}
                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span style="padding: 2px 7px; border-radius: 4px; font-size: 11px; font-weight: 700; font-family: ui-monospace, monospace; background: rgba(6, 182, 212, 0.15); color: #22d3ee; border: 1px solid rgba(6, 182, 212, 0.35);">
                                            {{ $batch['stock_code'] }}
                                        </span>
                                        @if(!empty($batch['color_name']))
                                            <span style="padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; background: #1e293b; color: #cbd5e1; border: 1px solid rgba(255,255,255,0.1);">
                                                {{ $batch['color_name'] }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- GRN & Date --}}
                                <td>
                                    <div style="font-weight: 600; color: #f1f5f9;">{{ $batch['grn_number'] }}</div>
                                    <div style="font-size: 10px; color: #94a3b8;">{{ $batch['added_date'] }}</div>
                                </td>

                                {{-- Batch Qty --}}
                                <td style="text-align: center; font-family: ui-monospace, monospace; font-weight: 700; color: #f1f5f9;">
                                    {{ $batch['quantity'] }}
                                </td>

                                {{-- GRN Unit Cost --}}
                                <td style="text-align: right; font-family: ui-monospace, monospace; color: #94a3b8;">
                                    Rs. {{ number_format((float) $batch['grn_unit_price'], 2) }}
                                </td>

                                {{-- Latest Selling Price --}}
                                <td style="text-align: right; font-family: ui-monospace, monospace; font-weight: 700; color: #22d3ee;">
                                    Rs. {{ number_format((float) $batch['latest_price'], 2) }}
                                </td>

                                {{-- Sold Quantity --}}
                                <td style="text-align: center; font-family: ui-monospace, monospace; font-weight: 700; color: {{ $batchSold > 0 ? '#c084fc' : '#64748b' }};">
                                    {{ $batchSold }}
                                </td>

                                {{-- Available Quantity --}}
                                <td style="text-align: center; font-family: ui-monospace, monospace; font-weight: 700;">
                                    @if($batch['available_quantity'] > 0)
                                        <span style="color: #34d399;">{{ $batch['available_quantity'] }}</span>
                                    @else
                                        <span style="color: #f87171;">0</span>
                                    @endif
                                </td>

                                {{-- Status Badge --}}
                                <td style="text-align: center;">
                                    @if($isEnded)
                                        <span class="sr-status-ended">
                                            <span style="width: 5px; height: 5px; border-radius: 50%; background: #f87171;"></span>
                                            Ended (Sold Out)
                                        </span>
                                    @elseif($isActive)
                                        <span class="sr-status-active">
                                            <span style="width: 5px; height: 5px; border-radius: 50%; background: #34d399;"></span>
                                            Current Selling Stock
                                        </span>
                                    @else
                                        <span class="sr-status-queued">
                                            <span style="width: 5px; height: 5px; border-radius: 50%; background: #38bdf8;"></span>
                                            Upcoming Stock
                                        </span>
                                    @endif
                                </td>

                                {{-- Revenue Price for the Stock (Batch Qty x GRN Cost) --}}
                                <td style="text-align: right; font-family: ui-monospace, monospace; color: #cbd5e1;">
                                    Rs. {{ number_format((float) $batch['revenue_price'], 2) }}
                                </td>

                                {{-- Achieved from Sale --}}
                                <td style="text-align: right; font-family: ui-monospace, monospace; font-weight: 700; color: #34d399;">
                                    Rs. {{ number_format((float) $batch['achieved_from_sale'], 2) }}
                                </td>

                                {{-- Feature 2: Stock-Wise Profit / Loss --}}
                                <td style="text-align: right; font-family: ui-monospace, monospace;">
                                    @if($batchSold > 0)
                                        <div style="font-weight: 800; font-size: 13px; color: {{ $isBatchProfit ? '#34d399' : '#f87171' }};">
                                            {{ $isBatchProfit ? '+' : '' }}Rs. {{ number_format($batchRealizedProfit, 2) }}
                                        </div>
                                        <div style="font-size: 9px; color: #94a3b8;">
                                            Cost: Rs. {{ number_format((float) ($batch['realized_cogs'] ?? 0), 2) }}
                                            @if(($batch['realized_margin_pct'] ?? 0) > 0)
                                                ({{ $batch['realized_margin_pct'] }}%)
                                            @endif
                                        </div>
                                    @else
                                        <div style="color: #64748b; font-size: 12px;">Rs. 0.00</div>
                                        <div style="font-size: 9px; color: #475569;">No sales yet</div>
                                    @endif
                                </td>
                            </tr>

                            {{-- Sub-row: Price Breakdown & Sold History --}}
                            <tr class="sr-sub-row">
                                <td colspan="11">
                                    <div class="sr-breakdown-box">
                                        <span style="font-weight: 700; color: #64748b; text-transform: uppercase; font-size: 10px; letter-spacing: 0.04em;">
                                            Sales Breakdown by Selling Price:
                                        </span>

                                        @if(empty($batch['sales_breakdown']))
                                            <span style="color: #64748b; font-style: italic;">
                                                No sales fulfilled from this stock batch yet (Stock is in queue).
                                            </span>
                                        @else
                                            @foreach($batch['sales_breakdown'] as $sb)
                                                <div class="sr-breakdown-pill">
                                                    <span style="color: #c084fc; font-weight: 700;">{{ $sb['quantity'] }} units</span>
                                                    <span style="color: #64748b;">sold @</span>
                                                    <span style="color: #22d3ee; font-weight: 700;">Rs. {{ number_format((float) $sb['selling_price'], 2) }}</span>
                                                    <span style="color: #475569;">|</span>
                                                    <span style="color: #94a3b8;">Cost: Rs. {{ number_format((float) $sb['grn_price'], 2) }}</span>
                                                    <span style="color: #475569;">➔</span>
                                                    <span style="color: #34d399; font-weight: 700;">Rev: Rs. {{ number_format((float) $sb['total_revenue'], 2) }}</span>
                                                    <span style="color: #34d399; font-size: 10px; background: rgba(16, 185, 129, 0.15); padding: 1px 6px; border-radius: 3px; border: 1px solid rgba(16, 185, 129, 0.3);">
                                                        Profit: +Rs. {{ number_format((float) $sb['achieved_profit'], 2) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Explanatory Note for Admin --}}
    <div class="sr-note">
        <svg style="width: 16px; height: 16px; color: #22d3ee; flex-shrink: 0; margin-top: 1px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <strong>FIFO (First-In, First-Out) Sales &amp; Profit Tracking:</strong>
            Customer orders automatically consume stock starting from the oldest received batch. For products with color variants, each color variant maintains its own independent FIFO queue so that every active color is ready for purchase as <strong style="color: #34d399;">Current Selling Stock</strong>. When prices change, sales are attributed to the exact price charged, preserving the original GRN unit cost to accurately report <strong style="color: #34d399;">Stock-Wise Profits</strong> and <strong style="color: #22d3ee;">All-Time Sales Performance</strong>.
        </div>
    </div>
</div>
