<x-filament-widgets::widget>
    <div class="sow-container">
        <style>
            .sow-container {
                --sow-bg-card: #ffffff;
                --sow-bg-card-alt: #f8fafc;
                --sow-border: #e2e8f0;
                --sow-border-subtle: #edf2f7;
                --sow-text-primary: #0f172a;
                --sow-text-secondary: #334155;
                --sow-text-muted: #64748b;
                --sow-text-faint: #94a3b8;
                --sow-brand: #0284c7;
                --sow-brand-soft: rgba(2, 132, 199, 0.1);
                --sow-success: #16a34a;
                --sow-success-soft: rgba(22, 163, 74, 0.1);
                --sow-amber: #d97706;
                --sow-amber-soft: rgba(217, 119, 6, 0.1);
                --sow-purple: #7c3aed;
                --sow-purple-soft: rgba(124, 58, 237, 0.1);

                font-family: inherit;
                display: flex;
                flex-direction: column;
                gap: 1.15rem;
                width: 100%;
                box-sizing: border-box;
                color: var(--sow-text-primary);
            }

            .dark .sow-container,
            :is(.dark) .sow-container {
                --sow-bg-card: #182030;
                --sow-bg-card-alt: #131926;
                --sow-border: #2a3547;
                --sow-border-subtle: #212a39;
                --sow-text-primary: #f8fafc;
                --sow-text-secondary: #cbd5e1;
                --sow-text-muted: #94a3b8;
                --sow-text-faint: #64748b;
                --sow-brand: #38bdf8;
                --sow-brand-soft: rgba(56, 189, 248, 0.12);
                --sow-success: #34d399;
                --sow-success-soft: rgba(52, 211, 153, 0.12);
                --sow-amber: #fbbf24;
                --sow-amber-soft: rgba(251, 191, 36, 0.12);
                --sow-purple: #a78bfa;
                --sow-purple-soft: rgba(167, 139, 250, 0.12);
            }

            .sow-container * {
                box-sizing: border-box;
            }

            /* Main Header Panel */
            .sow-panel {
                background-color: var(--sow-bg-card);
                border: 1px solid var(--sow-border);
                border-radius: 14px;
                padding: 1.25rem 1.5rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            }

            .sow-header {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }

            .sow-title-wrap {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .sow-icon-box {
                width: 40px;
                height: 40px;
                border-radius: 10px;
                background: var(--sow-brand-soft);
                color: var(--sow-brand);
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .sow-title {
                font-size: 1.1rem;
                font-weight: 800;
                color: var(--sow-text-primary);
                margin: 0;
                line-height: 1.2;
                letter-spacing: -0.01em;
            }

            .sow-subtitle {
                font-size: 0.78rem;
                color: var(--sow-text-muted);
                margin: 0.2rem 0 0 0;
            }

            /* Period Nav Buttons */
            .sow-period-nav {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                background-color: var(--sow-bg-card-alt);
                border: 1px solid var(--sow-border);
                padding: 0.25rem;
                border-radius: 10px;
                flex-wrap: wrap;
            }

            .sow-btn-period {
                border: none;
                background: transparent;
                color: var(--sow-text-muted);
                font-size: 0.75rem;
                font-weight: 700;
                padding: 0.45rem 0.85rem;
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.18s ease;
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
            }

            .sow-btn-period:hover {
                color: var(--sow-text-primary);
                background-color: rgba(255, 255, 255, 0.05);
            }

            .sow-btn-period.active {
                background-color: var(--sow-brand);
                color: #ffffff !important;
                box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
            }

            /* Active Period Banner */
            .sow-active-banner {
                margin-top: 1rem;
                padding: 0.65rem 0.95rem;
                border-radius: 8px;
                background: var(--sow-bg-card-alt);
                border: 1px solid var(--sow-border-subtle);
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 0.5rem;
                font-size: 0.78rem;
            }

            .sow-period-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                font-weight: 700;
                color: var(--sow-brand);
            }

            .sow-period-range {
                color: var(--sow-text-secondary);
                font-weight: 600;
            }

            .sow-custom-toggle-btn {
                background: none;
                border: 1px solid var(--sow-border);
                padding: 0.25rem 0.6rem;
                border-radius: 6px;
                font-size: 0.72rem;
                font-weight: 600;
                color: var(--sow-text-muted);
                cursor: pointer;
                transition: all 0.15s ease;
            }

            .sow-custom-toggle-btn:hover {
                color: var(--sow-text-primary);
                border-color: var(--sow-brand);
            }

            /* Custom Date Picker Drawer */
            .sow-custom-drawer {
                margin-top: 0.85rem;
                padding: 1rem;
                background: var(--sow-bg-card-alt);
                border: 1px solid var(--sow-border);
                border-radius: 10px;
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }

            .sow-drawer-row {
                display: flex;
                flex-wrap: wrap;
                align-items: flex-end;
                gap: 0.85rem;
            }

            .sow-input-group {
                display: flex;
                flex-direction: column;
                gap: 0.3rem;
                flex: 1;
                min-width: 140px;
            }

            .sow-input-group label {
                font-size: 0.72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: var(--sow-text-muted);
            }

            .sow-input-date {
                background-color: var(--sow-bg-card);
                border: 1px solid var(--sow-border);
                border-radius: 8px;
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
                color: var(--sow-text-primary);
                outline: none;
                width: 100%;
                transition: border-color 0.15s ease;
            }

            .sow-input-date:focus {
                border-color: var(--sow-brand);
            }

            .sow-btn-apply {
                background-color: var(--sow-brand);
                color: #ffffff;
                border: none;
                border-radius: 8px;
                padding: 0.55rem 1.15rem;
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                cursor: pointer;
                height: 36px;
                transition: opacity 0.15s ease;
            }

            .sow-btn-apply:hover {
                opacity: 0.9;
            }

            .sow-quick-presets {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.4rem;
                font-size: 0.72rem;
                color: var(--sow-text-muted);
            }

            .sow-chip-btn {
                background: var(--sow-bg-card);
                border: 1px solid var(--sow-border);
                border-radius: 9999px;
                padding: 0.2rem 0.65rem;
                font-size: 0.72rem;
                color: var(--sow-text-secondary);
                cursor: pointer;
                transition: all 0.15s ease;
            }

            .sow-chip-btn:hover {
                border-color: var(--sow-brand);
                color: var(--sow-brand);
            }

            /* 4 KPI Cards Grid */
            .sow-kpi-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 1rem;
            }

            .sow-kpi-card {
                background-color: var(--sow-bg-card);
                border: 1px solid var(--sow-border);
                border-radius: 14px;
                padding: 1.15rem 1.25rem;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                gap: 0.75rem;
                position: relative;
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            }

            .sow-kpi-card::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 3px;
                background: transparent;
            }

            .sow-kpi-card.brand::before { background: var(--sow-brand); }
            .sow-kpi-card.success::before { background: var(--sow-success); }
            .sow-kpi-card.amber::before { background: var(--sow-amber); }
            .sow-kpi-card.purple::before { background: var(--sow-purple); }

            .sow-kpi-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.5rem;
            }

            .sow-kpi-label {
                font-size: 0.72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: var(--sow-text-muted);
                margin: 0;
            }

            .sow-kpi-badge {
                font-size: 0.68rem;
                font-weight: 700;
                padding: 0.15rem 0.5rem;
                border-radius: 9999px;
            }

            .sow-kpi-badge.brand { background: var(--sow-brand-soft); color: var(--sow-brand); }
            .sow-kpi-badge.success { background: var(--sow-success-soft); color: var(--sow-success); }
            .sow-kpi-badge.amber { background: var(--sow-amber-soft); color: var(--sow-amber); }
            .sow-kpi-badge.purple { background: var(--sow-purple-soft); color: var(--sow-purple); }

            .sow-kpi-val {
                font-size: 1.6rem;
                font-weight: 800;
                line-height: 1.1;
                color: var(--sow-text-primary);
                letter-spacing: -0.02em;
                margin: 0.15rem 0 0 0;
            }

            .sow-kpi-sub {
                font-size: 0.72rem;
                color: var(--sow-text-muted);
                margin: 0;
            }

            /* Detailed Insights (2 Columns) */
            .sow-insights-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
                gap: 1rem;
            }

            .sow-insight-panel {
                background-color: var(--sow-bg-card);
                border: 1px solid var(--sow-border);
                border-radius: 14px;
                padding: 1.15rem 1.25rem;
                display: flex;
                flex-direction: column;
                gap: 0.85rem;
            }

            .sow-insight-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding-bottom: 0.65rem;
                border-bottom: 1px solid var(--sow-border-subtle);
            }

            .sow-insight-title {
                font-size: 0.85rem;
                font-weight: 800;
                color: var(--sow-text-primary);
                display: flex;
                align-items: center;
                gap: 0.4rem;
                margin: 0;
            }

            .sow-insight-sub {
                font-size: 0.7rem;
                color: var(--sow-text-muted);
            }

            /* Fulfillment Pipeline Rows */
            .sow-pipe-row {
                display: flex;
                flex-direction: column;
                gap: 0.35rem;
            }

            .sow-pipe-meta {
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 0.78rem;
            }

            .sow-pipe-status {
                display: flex;
                align-items: center;
                gap: 0.45rem;
                font-weight: 700;
            }

            .sow-pipe-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
            }

            .sow-pipe-bar-bg {
                width: 100%;
                height: 6px;
                background-color: var(--sow-bg-card-alt);
                border-radius: 9999px;
                overflow: hidden;
            }

            .sow-pipe-bar-fill {
                height: 100%;
                border-radius: 9999px;
                transition: width 0.3s ease;
            }

            /* Top Products List */
            .sow-prod-list {
                display: flex;
                flex-direction: column;
                gap: 0.6rem;
            }

            .sow-prod-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.45rem 0.6rem;
                border-radius: 8px;
                background-color: var(--sow-bg-card-alt);
                border: 1px solid var(--sow-border-subtle);
            }

            .sow-prod-rank {
                font-size: 0.75rem;
                font-weight: 800;
                color: var(--sow-brand);
                min-width: 18px;
            }

            .sow-prod-info {
                flex: 1;
                overflow: hidden;
            }

            .sow-prod-name {
                font-size: 0.78rem;
                font-weight: 700;
                color: var(--sow-text-primary);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                display: block;
            }

            .sow-prod-cat {
                font-size: 0.68rem;
                color: var(--sow-text-muted);
            }

            .sow-prod-stat {
                text-align: right;
                display: flex;
                flex-direction: column;
                align-items: flex-end;
            }

            .sow-prod-qty {
                font-size: 0.72rem;
                font-weight: 800;
                background: var(--sow-brand-soft);
                color: var(--sow-brand);
                padding: 0.1rem 0.4rem;
                border-radius: 4px;
            }

            .sow-prod-total {
                font-size: 0.74rem;
                font-weight: 700;
                color: var(--sow-text-secondary);
                margin-top: 0.15rem;
            }

            /* Mini Sales Activity Chart */
            .sow-chart-container {
                display: flex;
                align-items: flex-end;
                gap: 6px;
                height: 64px;
                padding-top: 0.5rem;
                overflow-x: auto;
            }

            .sow-chart-col {
                flex: 1;
                min-width: 22px;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: flex-end;
                align-items: center;
                gap: 4px;
                position: relative;
            }

            .sow-chart-bar {
                width: 100%;
                max-width: 24px;
                border-radius: 4px 4px 0 0;
                transition: all 0.2s ease;
                background-color: var(--sow-border);
            }

            .sow-chart-bar.active {
                background: linear-gradient(180deg, var(--sow-brand) 0%, rgba(2, 132, 199, 0.4) 100%);
            }

            .sow-chart-label {
                font-size: 0.62rem;
                color: var(--sow-text-muted);
                white-space: nowrap;
            }

            /* Empty State */
            .sow-empty-state {
                padding: 1.5rem;
                text-align: center;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.4rem;
                color: var(--sow-text-muted);
            }

            .sow-empty-icon {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                background: var(--sow-bg-card-alt);
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 0.25rem;
            }
        </style>

        <!-- 1. Header & Period Filter Controls -->
        <div class="sow-panel">
            <div class="sow-header">
                <div class="sow-title-wrap">
                    <div class="sow-icon-box">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <h2 class="sow-title">Sales Performance & Analytics</h2>
                        <p class="sow-subtitle">Real-time financial summary & volume breakdown for paid orders</p>
                    </div>
                </div>

                <!-- Navigation Period Filter Tabs -->
                <div class="sow-period-nav">
                    <button
                        type="button"
                        wire:click="setPeriod('daily')"
                        class="sow-btn-period {{ $period === 'daily' ? 'active' : '' }}"
                    >
                        <span>☀️ Daily (Today)</span>
                    </button>

                    <button
                        type="button"
                        wire:click="setPeriod('weekly')"
                        class="sow-btn-period {{ $period === 'weekly' ? 'active' : '' }}"
                    >
                        <span>📅 Weekly (7 Days)</span>
                    </button>

                    <button
                        type="button"
                        wire:click="setPeriod('monthly')"
                        class="sow-btn-period {{ $period === 'monthly' ? 'active' : '' }}"
                    >
                        <span>🗓️ Monthly (Month)</span>
                    </button>

                    <button
                        type="button"
                        wire:click="setPeriod('annually')"
                        class="sow-btn-period {{ $period === 'annually' ? 'active' : '' }}"
                    >
                        <span>📈 Annually (Year)</span>
                    </button>

                    <button
                        type="button"
                        wire:click="toggleCustomPicker"
                        class="sow-btn-period {{ $period === 'custom' ? 'active' : '' }}"
                    >
                        <span>⚙️ Custom Range</span>
                    </button>
                </div>
            </div>

            <!-- Active Period Range Banner -->
            <div class="sow-active-banner">
                <div class="sow-period-badge">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Period:</span>
                    <span class="sow-period-range">{{ $periodLabel }}</span>
                    <span style="color: var(--sow-text-muted); font-weight: normal;">({{ $daysCount }} {{ $daysCount === 1 ? 'day' : 'days' }})</span>
                </div>

                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    @if($totalOrders > 0)
                        <span style="color: var(--sow-success); font-weight: 700; font-size: 0.74rem;">
                            ● {{ $totalOrders }} Paid {{ $totalOrders === 1 ? 'Order' : 'Orders' }} Found
                        </span>
                    @else
                        <span style="color: var(--sow-text-muted); font-size: 0.74rem;">
                            No paid orders recorded in this timeframe
                        </span>
                    @endif

                    <button
                        type="button"
                        wire:click="toggleCustomPicker"
                        class="sow-custom-toggle-btn"
                    >
                        {{ $showCustomPicker ? 'Hide Date Inputs' : 'Select Specific Dates' }}
                    </button>
                </div>
            </div>

            <!-- Custom Date Range Drawer (Collapsible) -->
            @if($showCustomPicker)
                <div class="sow-custom-drawer">
                    <div class="sow-drawer-row">
                        <div class="sow-input-group">
                            <label>From (Start Date)</label>
                            <input
                                type="date"
                                wire:model.live="startDate"
                                class="sow-input-date"
                            />
                        </div>

                        <div class="sow-input-group">
                            <label>To (End Date)</label>
                            <input
                                type="date"
                                wire:model.live="endDate"
                                class="sow-input-date"
                            />
                        </div>

                        <button
                            type="button"
                            wire:click="applyCustomRange"
                            class="sow-btn-apply"
                        >
                            Apply Filter
                        </button>
                    </div>

                    <!-- Quick Preset Chips -->
                    <div class="sow-quick-presets">
                        <span>Quick Presets:</span>
                        <button type="button" wire:click="setQuickPreset('yesterday')" class="sow-chip-btn">Yesterday</button>
                        <button type="button" wire:click="setQuickPreset('last_7_days')" class="sow-chip-btn">Last 7 Days</button>
                        <button type="button" wire:click="setQuickPreset('last_30_days')" class="sow-chip-btn">Last 30 Days</button>
                        <button type="button" wire:click="setQuickPreset('last_month')" class="sow-chip-btn">Last Month</button>
                    </div>
                </div>
            @endif
        </div>

        <!-- 2. Core KPI Metric Cards Grid -->
        <div class="sow-kpi-grid">
            <!-- 1. Total Revenue -->
            <div class="sow-kpi-card brand">
                <div class="sow-kpi-header">
                    <span class="sow-kpi-label">Total Sales Revenue</span>
                    <span class="sow-kpi-badge brand">Paid Sales</span>
                </div>
                <div>
                    <h3 class="sow-kpi-val" style="color: var(--sow-brand);">
                        Rs. {{ number_format($totalRevenue, 2) }}
                    </h3>
                    <p class="sow-kpi-sub mt-1">
                        Total payments confirmed in this period
                    </p>
                </div>
            </div>

            <!-- 2. Total Orders -->
            <div class="sow-kpi-card success">
                <div class="sow-kpi-header">
                    <span class="sow-kpi-label">Paid Orders</span>
                    <span class="sow-kpi-badge success">100% Success</span>
                </div>
                <div>
                    <h3 class="sow-kpi-val">
                        {{ $totalOrders }} <span style="font-size: 1rem; font-weight: 600; color: var(--sow-text-muted);">{{ $totalOrders === 1 ? 'Order' : 'Orders' }}</span>
                    </h3>
                    <p class="sow-kpi-sub mt-1">
                        Completed transactions
                    </p>
                </div>
            </div>

            <!-- 3. Units Sold -->
            <div class="sow-kpi-card amber">
                <div class="sow-kpi-header">
                    <span class="sow-kpi-label">Units Sold</span>
                    <span class="sow-kpi-badge amber">{{ $distinctProductsCount }} Products</span>
                </div>
                <div>
                    <h3 class="sow-kpi-val">
                        {{ $totalUnits }} <span style="font-size: 1rem; font-weight: 600; color: var(--sow-text-muted);">Units</span>
                    </h3>
                    <p class="sow-kpi-sub mt-1">
                        @if($totalOrders > 0)
                            Avg {{ number_format($totalUnits / $totalOrders, 1) }} units per order
                        @else
                            0 units per order
                        @endif
                    </p>
                </div>
            </div>

            <!-- 4. Average Order Value -->
            <div class="sow-kpi-card purple">
                <div class="sow-kpi-header">
                    <span class="sow-kpi-label">Average Order Value (AOV)</span>
                    <span class="sow-kpi-badge purple">Spend / Order</span>
                </div>
                <div>
                    <h3 class="sow-kpi-val">
                        Rs. {{ number_format($avgOrderValue, 2) }}
                    </h3>
                    <p class="sow-kpi-sub mt-1">
                        Average basket size per customer
                    </p>
                </div>
            </div>
        </div>

        <!-- 3. Understandable Insights (Fulfillment Pipeline & Top Products) -->
        <div class="sow-insights-grid">
            <!-- Left: Fulfillment Pipeline -->
            <div class="sow-insight-panel">
                <div class="sow-insight-head">
                    <div>
                        <h4 class="sow-insight-title">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                            <span>Fulfillment Pipeline</span>
                        </h4>
                        <span class="sow-insight-sub">Status of orders placed in this period</span>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                    @foreach($fulfillmentMap as $status => $data)
                        @php
                            $pct = $totalOrders > 0 ? round(($data['count'] / $totalOrders) * 100) : 0;
                        @endphp
                        <div class="sow-pipe-row">
                            <div class="sow-pipe-meta">
                                <span class="sow-pipe-status" style="color: {{ $data['color'] }};">
                                    <span class="sow-pipe-dot" style="background-color: {{ $data['color'] }};"></span>
                                    <span>{{ $status }}</span>
                                </span>
                                <span style="font-weight: 700; color: var(--sow-text-primary);">
                                    {{ $data['count'] }} {{ $data['count'] === 1 ? 'order' : 'orders' }}
                                    <span style="color: var(--sow-text-muted); font-weight: normal; margin-left: 4px;">(Rs. {{ number_format($data['total'], 2) }})</span>
                                </span>
                            </div>
                            <div class="sow-pipe-bar-bg">
                                <div class="sow-pipe-bar-fill" style="width: {{ $pct }}%; background-color: {{ $data['color'] }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right: Top Selling Products -->
            <div class="sow-insight-panel">
                <div class="sow-insight-head">
                    <div>
                        <h4 class="sow-insight-title">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <span>Top Products in this Period</span>
                        </h4>
                        <span class="sow-insight-sub">Best-selling hardware & accessories</span>
                    </div>
                </div>

                @if($topProducts->isNotEmpty())
                    <div class="sow-prod-list">
                        @foreach($topProducts as $idx => $prod)
                            <div class="sow-prod-item">
                                <span class="sow-prod-rank">#{{ $idx + 1 }}</span>
                                <div class="sow-prod-info">
                                    <span class="sow-prod-name">{{ $prod->product_name }}</span>
                                    <span class="sow-prod-cat">{{ $prod->category_name ?: 'Computer Accessories' }}</span>
                                </div>
                                <div class="sow-prod-stat">
                                    <span class="sow-prod-qty">{{ $prod->total_qty }} {{ $prod->total_qty == 1 ? 'unit' : 'units' }}</span>
                                    <span class="sow-prod-total">Rs. {{ number_format((float) $prod->total_revenue, 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="sow-empty-state">
                        <div class="sow-empty-icon">📦</div>
                        <p style="font-weight: 700; margin: 0; color: var(--sow-text-secondary); font-size: 0.8rem;">No products sold in this period</p>
                        <p style="font-size: 0.72rem; margin: 0;">Try selecting Weekly, Monthly, or a wider date range above.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- 4. Sales Distribution Activity Timeline (Mini Bar Distribution) -->
        @if(!empty($chartBars) && $totalOrders > 0)
            <div class="sow-panel" style="padding: 1rem 1.25rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.78rem; font-weight: 700; color: var(--sow-text-primary); text-transform: uppercase; letter-spacing: 0.05em;">
                        Sales Volume Activity
                    </span>
                    <span style="font-size: 0.7rem; color: var(--sow-text-muted);">
                        Highest daily revenue highlighted
                    </span>
                </div>

                <div class="sow-chart-container">
                    @foreach($chartBars as $bar)
                        <div class="sow-chart-col" title="{{ $bar['full_date'] }}: Rs. {{ number_format($bar['revenue'], 2) }} ({{ $bar['orders_count'] }} orders)">
                            <div
                                class="sow-chart-bar {{ $bar['is_active'] ? 'active' : '' }}"
                                style="height: {{ $bar['height_pct'] }}%;"
                            ></div>
                            <span class="sow-chart-label">{{ $bar['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
