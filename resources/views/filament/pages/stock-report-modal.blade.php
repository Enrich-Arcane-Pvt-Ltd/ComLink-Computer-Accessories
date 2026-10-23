@php
    $report = $reportData ?? $report ?? [];
    $product = $report['product'] ?? [];
    $summary = $report['summary'] ?? [];
    $batches = $report['batches'] ?? [];
@endphp

<div class="stock-report-wrapper" style="color: #f1f5f9; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <style>
        .stock-report-wrapper {
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 18px;
            width: 100%;
        }
        .stock-report-wrapper * {
            box-sizing: border-box;
        }
        .sr-banner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 20px;
            background: linear-gradient(135deg, #090e17 0%, #111a28 60%, #0d2232 100%);
            border: 1px solid rgba(6, 182, 212, 0.3);
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
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

        /* KPI Cards Grid */
        .sr-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            width: 100%;
        }
        .sr-kpi-card {
            background: #111a28;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 85px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        .sr-kpi-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
        }
        .sr-kpi-val {
            font-size: 18px;
            font-weight: 800;
            font-family: ui-monospace, monospace;
            line-height: 1.2;
            margin: 4px 0 2px 0;
        }
        .sr-kpi-sub {
            font-size: 10px;
            color: #64748b;
        }

        /* Table Box */
        .sr-table-card {
            background: #0c1420;
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 14px;
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
            min-width: 900px;
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
            opacity: 0.82;
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
            padding: 3px 8px;
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

    {{-- 2. Summary KPI Metric Cards --}}
    <div class="sr-kpi-grid">
        {{-- Total Stock Received --}}
        <div class="sr-kpi-card">
            <span class="sr-kpi-label">Stock Received</span>
            <div class="sr-kpi-val" style="color: #38bdf8;">
                {{ $summary['total_stock'] ?? 0 }} <span style="font-size: 11px; font-weight: normal; color: #64748b;">units</span>
            </div>
            <span class="sr-kpi-sub">{{ $summary['total_batches_count'] ?? 0 }} GRN {{ ($summary['total_batches_count'] ?? 0) === 1 ? 'Batch' : 'Batches' }}</span>
        </div>

        {{-- Sold Quantity --}}
        <div class="sr-kpi-card">
            <span class="sr-kpi-label">Sold Quantity</span>
            <div class="sr-kpi-val" style="color: #c084fc;">
                {{ $summary['total_sold'] ?? 0 }} <span style="font-size: 11px; font-weight: normal; color: #64748b;">units</span>
            </div>
            <span class="sr-kpi-sub">Paid Customer Orders</span>
        </div>

        {{-- Available Stock --}}
        <div class="sr-kpi-card">
            <span class="sr-kpi-label">Available Stock</span>
            <div class="sr-kpi-val" style="color: {{ ($summary['total_available'] ?? 0) > 0 ? '#34d399' : '#f87171' }};">
                {{ $summary['total_available'] ?? 0 }} <span style="font-size: 11px; font-weight: normal; color: #64748b;">units</span>
            </div>
            <span class="sr-kpi-sub">{{ ($summary['total_available'] ?? 0) > 0 ? 'In Store & Warehouse' : 'Out of Stock' }}</span>
        </div>

        {{-- Achieved from Sale --}}
        <div class="sr-kpi-card">
            <span class="sr-kpi-label">Achieved From Sale</span>
            <div class="sr-kpi-val" style="color: #34d399; font-size: 16px;" title="Rs. {{ number_format((float) ($summary['total_achieved_revenue'] ?? 0), 2) }}">
                Rs. {{ number_format((float) ($summary['total_achieved_revenue'] ?? 0), 2) }}
            </div>
            <span class="sr-kpi-sub">Collected Revenue</span>
        </div>

        {{-- Revenue Price for Stock --}}
        <div class="sr-kpi-card">
            <span class="sr-kpi-label">Total Revenue Value</span>
            <div class="sr-kpi-val" style="color: #22d3ee; font-size: 16px;" title="Rs. {{ number_format((float) ($summary['total_expected_revenue'] ?? 0), 2) }}">
                Rs. {{ number_format((float) ($summary['total_expected_revenue'] ?? 0), 2) }}
            </div>
            <span class="sr-kpi-sub">At Current Latest Price</span>
        </div>

        {{-- Active Selling Stock --}}
        <div class="sr-kpi-card">
            <span class="sr-kpi-label">Current Selling Stock</span>
            <div style="margin: 4px 0 2px 0;">
                <span style="display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; font-family: ui-monospace, monospace; background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);">
                    {{ $summary['active_batch_code'] ?? 'None' }}
                </span>
            </div>
            <span class="sr-kpi-sub">FIFO Active Priority</span>
        </div>
    </div>

    {{-- 3. Stock Wise Detailed History Table --}}
    <div class="sr-table-card">
        <div class="sr-table-header">
            <h3 class="sr-table-title">
                <svg style="width: 16px; height: 16px; color: #22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Stock-Wise Batch Breakdown & Selling Price History
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
                            <th>GRN Invoice & Date</th>
                            <th style="text-align: center;">Batch Qty</th>
                            <th style="text-align: right;">GRN Unit Cost</th>
                            <th style="text-align: right;">Latest Price</th>
                            <th style="text-align: center;">Sold Qty</th>
                            <th style="text-align: center;">Available</th>
                            <th style="text-align: center;">Stock Status</th>
                            <th style="text-align: right;">Revenue Price</th>
                            <th style="text-align: right;">Achieved From Sale</th>
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
                                <td style="text-align: center; font-family: ui-monospace, monospace; font-weight: 700; color: {{ $batch['sold_quantity'] > 0 ? '#c084fc' : '#64748b' }};">
                                    {{ $batch['sold_quantity'] }}
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

                                {{-- Revenue Price for the Stock (Expected) --}}
                                <td style="text-align: right; font-family: ui-monospace, monospace; color: #cbd5e1;">
                                    Rs. {{ number_format((float) $batch['revenue_price'], 2) }}
                                </td>

                                {{-- Achieved from Sale --}}
                                <td style="text-align: right; font-family: ui-monospace, monospace; font-weight: 700; color: #34d399;">
                                    Rs. {{ number_format((float) $batch['achieved_from_sale'], 2) }}
                                </td>
                            </tr>

                            {{-- Sub-row: Price Breakdown & Sold History --}}
                            <tr class="sr-sub-row">
                                <td colspan="10">
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
                                                    <span style="color: #34d399; font-size: 10px; background: rgba(16, 185, 129, 0.15); padding: 1px 5px; border-radius: 3px;">
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
            <strong>FIFO (First-In, First-Out) Stock Principle:</strong>
            Customer orders automatically consume stock starting from the oldest received batch (Batch 1). Once an older batch is completely depleted, it transitions to <strong style="color: #f87171;">Ended (Sold Out)</strong>, and subsequent sales automatically draw from the next queue batch as the <strong style="color: #34d399;">Current Selling Stock</strong>. When admin updates the Latest Price in Products, any subsequent sales are tracked under that new selling price while retaining original GRN unit cost.
        </div>
    </div>
</div>
