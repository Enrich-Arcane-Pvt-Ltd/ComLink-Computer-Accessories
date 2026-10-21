<x-filament-panels::page>
    @php
        $analysis = $this->analysisData;
        $metrics = $analysis['metrics'];
        $products = $analysis['products'];
    @endphp

    <style>
        .sa-card {
            background-color: #18181b;
            border: 1px solid #27272a;
            border-radius: 12px;
            color: #f4f4f5;
        }
        .sa-input, .sa-select {
            background-color: #27272a !important;
            border: 1px solid #3f3f46 !important;
            color: #f4f4f5 !important;
            border-radius: 8px !important;
            font-size: 12px !important;
        }
        .sa-input:focus, .sa-select:focus {
            border-color: #06b6d4 !important;
            outline: none !important;
            box-shadow: 0 0 0 1px #06b6d4 !important;
        }
        .sa-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 12px;
            color: #f4f4f5;
        }
        .sa-table th {
            background-color: #202024;
            color: #a1a1aa;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 14px;
            border-bottom: 1px solid #27272a;
        }
        .sa-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #27272a;
            vertical-align: middle;
        }
        .sa-table tr:hover td {
            background-color: rgba(255, 255, 255, 0.02);
        }
        .sa-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
        }
    </style>

    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!-- 1. Top Controls Bar: Filter Presets, Inputs & Action Buttons -->
        <div class="sa-card" style="padding: 18px 20px;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 14px;">
                <!-- Time Period Filter Tabs -->
                <div>
                    <span style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #a1a1aa; margin-bottom: 8px;">
                        Analysis Period
                    </span>
                    <div style="display: inline-flex; flex-wrap: wrap; align-items: center; background-color: #27272a; border-radius: 8px; padding: 3px; gap: 3px;">
                        @foreach([
                            'all' => 'All Time',
                            'today' => 'Today',
                            'this_week' => 'This Week',
                            'this_month' => 'This Month',
                            'last_month' => 'Last Month',
                            'this_year' => 'This Year',
                            'custom' => 'Custom Range',
                        ] as $key => $label)
                            <button
                                type="button"
                                wire:click="applyPeriod('{{ $key }}')"
                                style="padding: 5px 12px; font-size: 11px; font-weight: 600; border-radius: 6px; cursor: pointer; border: none; transition: all 0.15s; {{ $period === $key ? 'background-color: #06b6d4; color: #ffffff;' : 'background-color: transparent; color: #d4d4d8;' }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Action Buttons: Print & Download PDF -->
                <div style="display: flex; align-items: center; gap: 10px;">
                    <a
                        href="{{ $this->printUrl }}"
                        target="_blank"
                        style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; font-size: 12px; font-weight: 600; border-radius: 8px; text-decoration: none; color: #f4f4f5; background-color: #27272a; border: 1px solid #3f3f46; transition: background-color 0.15s;"
                    >
                        <svg style="width: 14px; height: 14px; color: #a1a1aa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print Report
                    </a>

                    <a
                        href="{{ $this->pdfUrl }}"
                        target="_blank"
                        style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; font-size: 12px; font-weight: 600; border-radius: 8px; text-decoration: none; color: #ffffff; background-color: #2563eb; transition: background-color 0.15s;"
                    >
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download PDF
                    </a>
                </div>
            </div>

            <!-- Custom Date Inputs (if custom period selected) -->
            @if($period === 'custom')
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; padding: 12px; background-color: #202024; border-radius: 8px; border: 1px dashed #3f3f46; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 600; color: #a1a1aa; margin-bottom: 4px;">Start Date</label>
                        <input
                            type="date"
                            wire:model.live="customStart"
                            class="sa-input"
                            style="width: 100%; padding: 6px 10px;"
                        />
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 600; color: #a1a1aa; margin-bottom: 4px;">End Date</label>
                        <input
                            type="date"
                            wire:model.live="customEnd"
                            class="sa-input"
                            style="width: 100%; padding: 6px 10px;"
                        />
                    </div>
                </div>
            @endif

            <!-- Secondary Filters: Search, Supplier, Category -->
            <div style="display: grid; grid-template-columns: 2fr 1.2fr 1.2fr auto; gap: 12px; align-items: center; padding-top: 12px; border-top: 1px solid #27272a;">
                <div style="position: relative;">
                    <input
                        type="text"
                        wire:model.live.debounce.350ms="search"
                        placeholder="Search product name or product code..."
                        class="sa-input"
                        style="width: 100%; padding: 7px 10px 7px 32px;"
                    />
                    <svg style="width: 14px; height: 14px; color: #71717a; position: absolute; left: 10px; top: 10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <div>
                    <select wire:model.live="supplierId" class="sa-select" style="width: 100%; padding: 7px 10px;">
                        <option value="">All Suppliers</option>
                        @foreach($this->suppliers as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select wire:model.live="categoryId" class="sa-select" style="width: 100%; padding: 7px 10px;">
                        <option value="">All Categories</option>
                        @foreach($this->categories as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <button
                        type="button"
                        wire:click="resetFilters"
                        title="Reset all filters"
                        style="padding: 7px 10px; background-color: #27272a; border: 1px solid #3f3f46; color: #a1a1aa; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;"
                    >
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. Five Executive KPI Metric Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px;">
            <!-- Card 1: Total Purchase Expense -->
            <div class="sa-card" style="padding: 16px 18px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #f59e0b;">
                        Purchase Expense
                    </span>
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; background-color: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </span>
                </div>
                <div style="font-size: 18px; font-weight: 800; color: #f4f4f5; letter-spacing: -0.02em;">
                    Rs. {{ number_format($metrics['total_expense'], 2) }}
                </div>
                <div style="font-size: 11px; color: #71717a; margin-top: 4px;">
                    {{ number_format($metrics['total_purchased_qty']) }} units acquired via GRN
                </div>
            </div>

            <!-- Card 2: Total Sales Revenue -->
            <div class="sa-card" style="padding: 16px 18px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #10b981;">
                        Sales Revenue
                    </span>
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; background-color: rgba(16, 185, 129, 0.15); color: #10b981;">
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                </div>
                <div style="font-size: 18px; font-weight: 800; color: #10b981; letter-spacing: -0.02em;">
                    Rs. {{ number_format($metrics['total_revenue'], 2) }}
                </div>
                <div style="font-size: 11px; color: #71717a; margin-top: 4px;">
                    {{ number_format($metrics['total_sold_qty']) }} units sold (Paid Orders)
                </div>
            </div>

            <!-- Card 3: Cost of Goods Sold (COGS) -->
            <div class="sa-card" style="padding: 16px 18px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #a78bfa;">
                        Cost of Sold Goods
                    </span>
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; background-color: rgba(167, 139, 250, 0.15); color: #a78bfa;">
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                </div>
                <div style="font-size: 18px; font-weight: 800; color: #f4f4f5; letter-spacing: -0.02em;">
                    Rs. {{ number_format($metrics['total_cogs'], 2) }}
                </div>
                <div style="font-size: 11px; color: #71717a; margin-top: 4px;">
                    Purchase cost base of sold units
                </div>
            </div>

            <!-- Card 4: Gross Profit Earned -->
            <div class="sa-card" style="padding: 16px 18px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: {{ $metrics['total_gross_profit'] >= 0 ? '#14b8a6' : '#f43f5e' }};">
                        Gross Profit
                    </span>
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; background-color: {{ $metrics['total_gross_profit'] >= 0 ? 'rgba(20, 184, 166, 0.15)' : 'rgba(244, 63, 94, 0.15)' }}; color: {{ $metrics['total_gross_profit'] >= 0 ? '#14b8a6' : '#f43f5e' }};">
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </span>
                </div>
                <div style="font-size: 18px; font-weight: 800; color: {{ $metrics['total_gross_profit'] >= 0 ? '#14b8a6' : '#f43f5e' }}; letter-spacing: -0.02em;">
                    {{ $metrics['total_gross_profit'] >= 0 ? '+' : '' }}Rs. {{ number_format($metrics['total_gross_profit'], 2) }}
                </div>
                <div style="font-size: 11px; color: #71717a; margin-top: 4px;">
                    Gross Margin: <strong style="color: {{ $metrics['overall_profit_margin'] >= 15 ? '#10b981' : '#f4f4f5' }};">{{ $metrics['overall_profit_margin'] }}%</strong>
                </div>
            </div>

            <!-- Card 5: Remaining Stock Value -->
            <div class="sa-card" style="padding: 16px 18px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #38bdf8;">
                        Stock Asset Value
                    </span>
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; background-color: rgba(56, 189, 248, 0.15); color: #38bdf8;">
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </span>
                </div>
                <div style="font-size: 18px; font-weight: 800; color: #38bdf8; letter-spacing: -0.02em;">
                    Rs. {{ number_format($metrics['total_stock_value'], 2) }}
                </div>
                <div style="font-size: 11px; color: #71717a; margin-top: 4px;">
                    {{ number_format($metrics['total_remaining_stock']) }} unsold physical units
                </div>
            </div>
        </div>

        <!-- 3. Product-Wise Financial Performance Ledger Table -->
        <div class="sa-card" style="overflow: hidden;">
            <div style="padding: 16px 20px; border-bottom: 1px solid #27272a; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h3 style="font-size: 14px; font-weight: 700; color: #f4f4f5; margin: 0;">
                        Product Profitability & Expense Ledger
                    </h3>
                    <div style="font-size: 11px; color: #71717a; margin-top: 2px;">
                        Showing results for: <span style="font-weight: 600; color: #06b6d4;">{{ $analysis['period_label'] }}</span>
                    </div>
                </div>
                <span style="font-size: 11px; font-weight: 600; color: #71717a;">
                    {{ count($products) }} {{ count($products) === 1 ? 'product' : 'products' }} analyzed
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table class="sa-table">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">#</th>
                            <th style="min-width: 250px;">Product Details</th>
                            <th style="min-width: 160px;">Purchasing (Expense)</th>
                            <th style="min-width: 160px;">Sales (Earnings)</th>
                            <th style="min-width: 160px;">Gross Profit & Margin</th>
                            <th style="min-width: 150px;">Current Stock Status</th>
                            <th style="width: 90px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $index => $prod)
                            <tr>
                                <!-- # Index -->
                                <td style="text-align: center; color: #71717a; font-weight: 500;">
                                    {{ $index + 1 }}
                                </td>

                                <!-- Product Details -->
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <img
                                            src="{{ $prod['image_url'] }}"
                                            alt="{{ $prod['name'] }}"
                                            style="width: 38px; height: 38px; min-width: 38px; min-height: 38px; max-width: 38px; max-height: 38px; object-fit: cover; border-radius: 6px; border: 1px solid rgba(156, 163, 175, 0.25); background-color: #27272a;"
                                            onerror="this.src='/images/placeholder.png'"
                                        />
                                        <div style="display: flex; flex-direction: column; min-width: 0;">
                                            <span style="font-weight: 600; font-size: 13px; line-height: 1.25; color: #f4f4f5; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 280px;" title="{{ $prod['name'] }}">
                                                {{ $prod['name'] }}
                                            </span>
                                            <div style="display: flex; align-items: center; gap: 6px; margin-top: 3px;">
                                                <span style="display: inline-flex; align-items: center; padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; font-family: monospace; background-color: rgba(6, 182, 212, 0.15); color: #06b6d4; border: 1px solid rgba(6, 182, 212, 0.3);">
                                                    {{ $prod['product_code'] }}
                                                </span>
                                                <span style="font-size: 11px; color: #71717a;">
                                                    &bull; {{ $prod['condition'] }}
                                                </span>
                                            </div>
                                            @if(!empty($prod['color_breakdown']))
                                                <div style="display: flex; align-items: center; gap: 4px; margin-top: 5px; flex-wrap: wrap;">
                                                    <span style="display: inline-flex; align-items: center; gap: 2px; font-size: 10px; font-weight: 700; color: #c084fc; background-color: rgba(192, 132, 252, 0.15); padding: 1px 5px; border-radius: 4px; border: 1px solid rgba(192, 132, 252, 0.3);">
                                                        🎨 {{ count($prod['color_breakdown']) }} Colors
                                                    </span>
                                                    @foreach($prod['color_breakdown'] as $cb)
                                                        <span style="font-size: 10px; font-weight: 600; padding: 1px 5px; border-radius: 4px; background: rgba(39, 39, 42, 0.85); color: #e4e4e7; border: 1px solid #3f3f46;" title="Stock: {{ $cb['stock'] }} units | Sold: {{ $cb['sold_qty'] }} units">
                                                            <span style="color: #38bdf8; font-weight: 700;">{{ $cb['color'] }}:</span> {{ $cb['stock'] }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Purchasing (Expense) -->
                                <td>
                                    <div style="font-weight: 700; font-size: 13px; color: #f4f4f5;">
                                        Rs. {{ number_format($prod['purchase_expense'], 2) }}
                                    </div>
                                    <div style="font-size: 11px; color: #71717a; margin-top: 2px;">
                                        {{ number_format($prod['purchased_qty']) }} units @ Rs. {{ number_format($prod['avg_purchase_price'], 2) }}
                                    </div>
                                </td>

                                <!-- Sales (Earnings) -->
                                <td>
                                    <div style="font-weight: 700; font-size: 13px; color: #10b981;">
                                        Rs. {{ number_format($prod['sales_revenue'], 2) }}
                                    </div>
                                    <div style="font-size: 11px; color: #71717a; margin-top: 2px;">
                                        {{ number_format($prod['sold_qty']) }} sold @ Rs. {{ number_format($prod['avg_selling_price'], 2) }}
                                    </div>
                                </td>

                                <!-- Gross Profit & Margin -->
                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span style="font-weight: 700; font-size: 13px; color: {{ $prod['gross_profit'] >= 0 ? '#14b8a6' : '#f43f5e' }};">
                                            {{ $prod['gross_profit'] >= 0 ? '+' : '' }}Rs. {{ number_format($prod['gross_profit'], 2) }}
                                        </span>
                                        <span class="sa-badge" style="{{ $prod['profit_margin_pct'] >= 15 ? 'background-color: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);' : ($prod['profit_margin_pct'] > 0 ? 'background-color: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);' : 'background-color: rgba(113, 113, 122, 0.15); color: #a1a1aa; border: 1px solid rgba(113, 113, 122, 0.25);') }}">
                                            {{ $prod['profit_margin_pct'] }}%
                                        </span>
                                    </div>
                                    <div style="font-size: 10px; color: #71717a; margin-top: 2px;">
                                        COGS: Rs. {{ number_format($prod['cogs'], 2) }}
                                    </div>
                                </td>

                                <!-- Current Stock Status -->
                                <td>
                                    <span class="sa-badge" style="{{ $prod['current_stock'] > 5 ? 'background-color: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);' : ($prod['current_stock'] > 0 ? 'background-color: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);' : 'background-color: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);') }}">
                                        {{ number_format($prod['current_stock']) }} units
                                    </span>
                                    <div style="font-size: 10px; color: #38bdf8; font-weight: 600; margin-top: 3px;">
                                        Value: Rs. {{ number_format($prod['remaining_stock_value'], 2) }}
                                    </div>
                                </td>

                                <!-- Action: View Breakdown Modal -->
                                <td style="text-align: center;">
                                    <button
                                        type="button"
                                        wire:click="openBreakdown({{ $prod['product_id'] }})"
                                        style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; font-size: 11px; font-weight: 600; border-radius: 6px; cursor: pointer; border: 1px solid rgba(6, 182, 212, 0.3); background-color: rgba(6, 182, 212, 0.15); color: #06b6d4; transition: all 0.15s;"
                                    >
                                        <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Breakdown
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 40px; text-align: center; color: #71717a;">
                                    <div style="font-size: 13px; font-weight: 600; color: #f4f4f5; margin-bottom: 4px;">No products found</div>
                                    <div style="font-size: 11px;">Try adjusting your time period, search keywords, or supplier filters.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($products) > 0)
                        <tfoot>
                            <tr style="background-color: #202024; border-top: 2px solid #3f3f46; font-weight: 700; color: #f4f4f5;">
                                <td colspan="2" style="text-align: right; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #a1a1aa;">
                                    Ledger Totals:
                                </td>
                                <td>
                                    <div style="font-size: 13px; font-weight: 800; color: #f4f4f5;">
                                        Rs. {{ number_format($metrics['total_expense'], 2) }}
                                    </div>
                                    <div style="font-size: 10px; color: #71717a;">{{ number_format($metrics['total_purchased_qty']) }} units</div>
                                </td>
                                <td>
                                    <div style="font-size: 13px; font-weight: 800; color: #10b981;">
                                        Rs. {{ number_format($metrics['total_revenue'], 2) }}
                                    </div>
                                    <div style="font-size: 10px; color: #71717a;">{{ number_format($metrics['total_sold_qty']) }} units</div>
                                </td>
                                <td>
                                    <div style="font-size: 13px; font-weight: 800; color: #14b8a6;">
                                        {{ $metrics['total_gross_profit'] >= 0 ? '+' : '' }}Rs. {{ number_format($metrics['total_gross_profit'], 2) }}
                                    </div>
                                    <div style="font-size: 10px; color: #71717a;">Margin: {{ $metrics['overall_profit_margin'] }}%</div>
                                </td>
                                <td>
                                    <div style="font-size: 13px; font-weight: 800; color: #38bdf8;">
                                        Rs. {{ number_format($metrics['total_stock_value'], 2) }}
                                    </div>
                                    <div style="font-size: 10px; color: #71717a;">{{ number_format($metrics['total_remaining_stock']) }} units</div>
                                </td>
                                <td style="text-align: center; color: #71717a;">&bull;</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- 4. Granular In-Depth Breakdown Modal (GRN Batches & Customer Orders) -->
    @if($showBreakdownModal && $activeBreakdown)
        <div style="position: fixed; inset: 0; z-index: 100; overflow-y: auto;">
            <!-- Backdrop -->
            <div 
                style="position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.75); backdrop-filter: blur(2px);" 
                wire:click="closeBreakdown"
            ></div>

            <!-- Modal Panel Container -->
            <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;">
                <div style="position: relative; width: 100%; max-width: 850px; background-color: #18181b; border: 1px solid #3f3f46; border-radius: 14px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden; z-index: 101;">
                    <!-- Modal Header -->
                    <div style="padding: 16px 20px; background-color: #202024; border-bottom: 1px solid #27272a; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h3 style="font-size: 15px; font-weight: 800; color: #f4f4f5; margin: 0;">
                                Financial Breakdown: {{ $activeBreakdown['product']['name'] }}
                            </h3>
                            <div style="display: flex; align-items: center; gap: 8px; font-size: 11px; color: #a1a1aa; margin-top: 3px;">
                                <span>Code: <strong style="color: #06b6d4; font-family: monospace;">{{ $activeBreakdown['product']['product_code'] }}</strong></span>
                                <span>&bull;</span>
                                <span>Condition: <strong style="color: #f4f4f5;">{{ $activeBreakdown['product']['condition'] }}</strong></span>
                                <span>&bull;</span>
                                <span>Supplier: <strong style="color: #f4f4f5;">{{ $activeBreakdown['product']['supplier_name'] }}</strong></span>
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click="closeBreakdown"
                            style="padding: 6px; background-color: #27272a; border: 1px solid #3f3f46; color: #a1a1aa; border-radius: 6px; cursor: pointer;"
                        >
                            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div style="padding: 20px; max-height: 70vh; overflow-y: auto; display: flex; flex-direction: column; gap: 20px;">
                        <!-- Summary Ribbon -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; padding: 12px 16px; background-color: #202024; border: 1px solid #27272a; border-radius: 8px; font-size: 11px;">
                            <div>
                                <span style="color: #71717a; display: block;">Total Purchased:</span>
                                <strong style="color: #f4f4f5; font-size: 13px;">{{ number_format($activeBreakdown['total_inward_qty']) }} units</strong>
                            </div>
                            <div>
                                <span style="color: #71717a; display: block;">Total GRN Expense:</span>
                                <strong style="color: #f59e0b; font-size: 13px;">Rs. {{ number_format($activeBreakdown['total_inward_expense'], 2) }}</strong>
                            </div>
                            <div>
                                <span style="color: #71717a; display: block;">Total Units Sold:</span>
                                <strong style="color: #f4f4f5; font-size: 13px;">{{ number_format($activeBreakdown['total_outward_qty']) }} units</strong>
                            </div>
                            <div>
                                <span style="color: #71717a; display: block;">Total Sales Revenue:</span>
                                <strong style="color: #10b981; font-size: 13px;">Rs. {{ number_format($activeBreakdown['total_outward_revenue'], 2) }}</strong>
                            </div>
                        </div>

                        <!-- Registered Color Variants (if multi-color product) -->
                        @if(!empty($activeBreakdown['color_variants']))
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <h4 style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #c084fc; margin: 0; display: flex; align-items: center; gap: 6px;">
                                    🎨 Registered Color Variants
                                </h4>
                                <span style="font-size: 11px; color: #71717a;">
                                    {{ count($activeBreakdown['color_variants']) }} colors
                                </span>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 8px;">
                                @foreach($activeBreakdown['color_variants'] as $cVar)
                                    <div style="padding: 10px 12px; background-color: #202024; border: 1px solid #3f3f46; border-radius: 6px;">
                                        <div style="font-weight: 700; font-size: 12px; color: #c084fc;">{{ $cVar['color'] }}</div>
                                        <div style="font-size: 11px; color: #a1a1aa; margin-top: 3px;">
                                            Stock: <strong style="color: #38bdf8;">{{ number_format($cVar['stock']) }} units</strong>
                                        </div>
                                        <div style="font-size: 11px; color: #71717a; margin-top: 2px;">
                                            Price: Rs. {{ number_format($cVar['price'], 2) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- 1. Inward Batches (GRNs) -->
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <h4 style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #f59e0b; margin: 0; display: flex; align-items: center; gap: 6px;">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                    </svg>
                                    Inward Goods Received Notes (GRN Batches)
                                </h4>
                                <span style="font-size: 11px; color: #71717a;">
                                    {{ count($activeBreakdown['inward_batches']) }} {{ count($activeBreakdown['inward_batches']) === 1 ? 'batch' : 'batches' }}
                                </span>
                            </div>

                            <div style="border: 1px solid #27272a; border-radius: 8px; overflow: hidden;">
                                <table class="sa-table">
                                    <thead>
                                        <tr>
                                            <th>GRN Number</th>
                                            <th>Received Date</th>
                                            <th>Supplier</th>
                                            <th style="text-align: center;">Quantity</th>
                                            <th style="text-align: right;">Unit Price</th>
                                            <th style="text-align: right;">Total Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($activeBreakdown['inward_batches'] as $grn)
                                            <tr>
                                                <td style="font-family: monospace; font-weight: 700; color: #06b6d4;">
                                                    {{ $grn['grn_code'] }}
                                                </td>
                                                <td style="color: #a1a1aa;">{{ $grn['received_date'] }}</td>
                                                <td style="color: #f4f4f5;">
                                                    {{ $grn['supplier_name'] }}
                                                    @if(!empty($grn['color_name']))
                                                        <span style="display: inline-block; margin-left: 4px; font-size: 10px; font-weight: bold; color: #c084fc; background: rgba(192, 132, 252, 0.15); padding: 1px 5px; border-radius: 3px; border: 1px solid rgba(192, 132, 252, 0.3);">
                                                            {{ $grn['color_name'] }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td style="text-align: center; font-weight: 700; color: #f4f4f5;">{{ number_format($grn['quantity']) }}</td>
                                                <td style="text-align: right; color: #a1a1aa;">Rs. {{ number_format($grn['unit_price'], 2) }}</td>
                                                <td style="text-align: right; font-weight: 700; color: #f4f4f5;">Rs. {{ number_format($grn['total'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" style="padding: 20px; text-align: center; color: #71717a;">No inward GRN batches recorded for this product yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 2. Outward Sales Orders -->
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <h4 style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #10b981; margin: 0; display: flex; align-items: center; gap: 6px;">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                    </svg>
                                    Outward Sales Orders (Paid Orders)
                                </h4>
                                <span style="font-size: 11px; color: #71717a;">
                                    {{ count($activeBreakdown['outward_sales']) }} {{ count($activeBreakdown['outward_sales']) === 1 ? 'order' : 'orders' }}
                                </span>
                            </div>

                            <div style="border: 1px solid #27272a; border-radius: 8px; overflow: hidden;">
                                <table class="sa-table">
                                    <thead>
                                        <tr>
                                            <th>Order Number</th>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th style="text-align: center;">Qty Sold</th>
                                            <th style="text-align: right;">Selling Price</th>
                                            <th style="text-align: right;">Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($activeBreakdown['outward_sales'] as $sale)
                                            <tr>
                                                <td style="font-family: monospace; font-weight: 700; color: #10b981;">
                                                    {{ $sale['order_number'] }}
                                                </td>
                                                <td style="color: #a1a1aa;">{{ $sale['order_date'] }}</td>
                                                <td>
                                                    <div style="color: #f4f4f5; font-weight: 600;">
                                                        {{ $sale['customer_name'] }}
                                                        @if(!empty($sale['color_name']))
                                                            <span style="display: inline-block; margin-left: 4px; font-size: 10px; font-weight: bold; color: #c084fc; background: rgba(192, 132, 252, 0.15); padding: 1px 5px; border-radius: 3px; border: 1px solid rgba(192, 132, 252, 0.3);">
                                                                {{ $sale['color_name'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div style="font-size: 10px; color: #71717a;">{{ $sale['customer_phone'] }}</div>
                                                </td>
                                                <td style="text-align: center; font-weight: 700; color: #f4f4f5;">{{ number_format($sale['quantity']) }}</td>
                                                <td style="text-align: right; color: #a1a1aa;">Rs. {{ number_format($sale['unit_price'], 2) }}</td>
                                                <td style="text-align: right; font-weight: 700; color: #10b981;">Rs. {{ number_format($sale['total_price'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" style="padding: 20px; text-align: center; color: #71717a;">No completed customer sales recorded for this product yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div style="padding: 14px 20px; background-color: #202024; border-top: 1px solid #27272a; display: flex; justify-content: flex-end;">
                        <button
                            type="button"
                            wire:click="closeBreakdown"
                            style="padding: 6px 16px; font-size: 12px; font-weight: 600; border-radius: 8px; cursor: pointer; border: 1px solid #3f3f46; background-color: #27272a; color: #f4f4f5;"
                        >
                            Close Breakdown
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
