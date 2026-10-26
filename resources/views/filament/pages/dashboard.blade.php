<x-filament-panels::page>
    @php
        // ---- Display helpers (no new data needed from the Page class) ----
        $compact = function ($n) {
            $n = (float) $n;
            $abs = abs($n);
            if ($abs >= 1000000) return number_format($n / 1000000, 2) . 'M';
            if ($abs >= 1000)    return number_format($n / 1000, 1) . 'k';
            return number_format($n, 2);
        };

        $c3 = (array) $chartThree;
        $donutColors = ['#06b6d4', '#6366f1', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#64748b'];
        $donutLabels = $c3['labels'] ?? [];
        $donutValues = $c3['values'] ?? [];
        $donutPcts   = $c3['percentages'] ?? [];

        $maxFlow   = max((float) $totalRevenue, (float) $totalExpenses, 1);
        $revWidth  = round(((float) $totalRevenue / $maxFlow) * 100);
        $expWidth  = round(((float) $totalExpenses / $maxFlow) * 100);

        $hour     = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $userName = auth()->user()?->name;

        $statusTone = fn ($s) => match (strtolower((string) $s)) {
            'pending'                 => 'amber',
            'processing'              => 'blue',
            'packaging', 'packed'     => 'violet',
            'shipped', 'dispatched'   => 'cyan',
            'delivered', 'completed'  => 'green',
            'cancelled', 'canceled', 'failed', 'refunded' => 'red',
            default                   => 'slate',
        };

        $chartPayload = [
            'one'   => $chartOne,
            'two'   => $chartTwo,
            'three' => ['labels' => $donutLabels, 'values' => $donutValues, 'percentages' => $donutPcts, 'colors' => $donutColors],
        ];
    @endphp

    <div id="comlink-dashboard" class="cl" data-charts="{{ json_encode($chartPayload) }}">
        <style>
            /* ============ Tokens (light) ============ */
            .cl {
                --cl-card: #ffffff;
                --cl-card-alt: #f8fafc;
                --cl-border: #e6ebf1;
                --cl-text: #0f172a;
                --cl-muted: #64748b;
                --cl-faint: #94a3b8;
                --cl-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 6px 20px -8px rgba(15, 23, 42, .08);
                --cl-brand: #0891b2;
                --cl-brand-2: #4f46e5;

                display: flex;
                flex-direction: column;
                gap: 20px;
                width: 100%;
                color: var(--cl-text);
                font-variant-numeric: tabular-nums;
            }
            .dark .cl {
                --cl-card: #0f1724;
                --cl-card-alt: #131d2d;
                --cl-border: rgba(255, 255, 255, .08);
                --cl-text: #f1f5f9;
                --cl-muted: #94a3b8;
                --cl-faint: #64748b;
                --cl-shadow: 0 1px 2px rgba(0, 0, 0, .3), 0 10px 28px -12px rgba(0, 0, 0, .55);
                --cl-brand: #22d3ee;
                --cl-brand-2: #818cf8;
            }
            .cl, .cl * { box-sizing: border-box; }

            /* Tone system: one colour drives icon chips, pills and deltas */
            .cl .t-cyan   { --t: #0e7490; } .dark .cl .t-cyan   { --t: #22d3ee; }
            .cl .t-blue   { --t: #1d4ed8; } .dark .cl .t-blue   { --t: #60a5fa; }
            .cl .t-green  { --t: #047857; } .dark .cl .t-green  { --t: #34d399; }
            .cl .t-violet { --t: #6d28d9; } .dark .cl .t-violet { --t: #a78bfa; }
            .cl .t-amber  { --t: #b45309; } .dark .cl .t-amber  { --t: #fbbf24; }
            .cl .t-red    { --t: #b91c1c; } .dark .cl .t-red    { --t: #f87171; }
            .cl .t-slate  { --t: #475569; } .dark .cl .t-slate  { --t: #cbd5e1; }

            /* ============ Header ============ */
            .cl-head {
                display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;
            }
            .cl-head h1 { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -.02em; }
            .cl-head p { margin: 4px 0 0; font-size: 13px; color: var(--cl-muted); }
            .cl-actions { display: flex; flex-wrap: wrap; gap: 10px; }
            .cl-btn {
                display: inline-flex; align-items: center; gap: 8px;
                padding: 9px 16px; border-radius: 10px;
                font-size: 13px; font-weight: 600; text-decoration: none;
                border: 1px solid var(--cl-border); background: var(--cl-card); color: var(--cl-text);
                box-shadow: var(--cl-shadow);
                transition: background .15s ease, border-color .15s ease;
            }
            .cl-btn:hover { border-color: var(--cl-brand); }
            .cl-btn:focus-visible, .cl-link:focus-visible { outline: 2px solid var(--cl-brand); outline-offset: 2px; }
            .cl-btn svg { width: 16px; height: 16px; }
            .cl-btn--primary {
                background: linear-gradient(135deg, #0891b2, #4f46e5);
                border-color: transparent; color: #fff;
            }
            .cl-btn--primary:hover { filter: brightness(1.08); border-color: transparent; }

            /* ============ KPI cards ============ */
            .cl-kpis { display: grid; gap: 14px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            @media (min-width: 768px)  { .cl-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
            @media (min-width: 1400px) { .cl-kpis { grid-template-columns: repeat(6, minmax(0, 1fr)); } }

            .cl-card {
                background: var(--cl-card);
                border: 1px solid var(--cl-border);
                border-radius: 16px;
                box-shadow: var(--cl-shadow);
            }
            .cl-kpi { padding: 16px; display: flex; flex-direction: column; gap: 12px; min-width: 0; }
            .cl-kpi-top { display: flex; align-items: center; gap: 10px; }
            .cl-chip {
                width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
                display: grid; place-items: center;
                color: var(--t);
                background: color-mix(in srgb, var(--t) 14%, transparent);
            }
            .cl-chip svg { width: 19px; height: 19px; }
            .cl-kpi-label { font-size: 13px; font-weight: 500; color: var(--cl-muted); }
            .cl-kpi-value {
                font-size: 24px; font-weight: 700; letter-spacing: -.02em; line-height: 1.1;
                white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            }
            .cl-kpi-value small { font-size: 13px; font-weight: 600; color: var(--cl-muted); letter-spacing: 0; margin-right: 2px; }
            .cl-kpi-note { font-size: 12px; color: var(--cl-faint); display: flex; flex-wrap: wrap; gap: 4px 8px; align-items: center; }
            .cl-kpi-note b { font-weight: 600; color: var(--t, var(--cl-muted)); }
            .cl-kpi--profit.is-up   { border-color: color-mix(in srgb, #10b981 40%, transparent); }
            .cl-kpi--profit.is-down { border-color: color-mix(in srgb, #ef4444 40%, transparent); }

            /* ============ Grids ============ */
            .cl-row { display: grid; gap: 16px; grid-template-columns: 1fr; }
            @media (min-width: 1100px) {
                .cl-row--trend  { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
                .cl-row--split  { grid-template-columns: minmax(0, 3fr) minmax(0, 2fr); }
            }
            .cl-panel { padding: 20px; display: flex; flex-direction: column; gap: 16px; min-width: 0; }
            .cl-panel-head h3 { margin: 0; font-size: 15px; font-weight: 650; }
            .cl-panel-head p { margin: 3px 0 0; font-size: 12px; color: var(--cl-muted); }
            .cl-canvas { position: relative; width: 100%; height: 280px; }
            .cl-canvas--donut { height: 210px; }

            /* ============ Financial overview (gradient card) ============ */
            .cl-fin {
                padding: 22px; border-radius: 16px; color: #fff; position: relative; overflow: hidden;
                background: linear-gradient(145deg, #0e7490 0%, #4f46e5 100%);
                box-shadow: 0 14px 34px -14px rgba(79, 70, 229, .55);
                display: flex; flex-direction: column; gap: 18px; min-width: 0;
            }
            .cl-fin::after {
                content: ''; position: absolute; right: -60px; top: -60px; width: 220px; height: 220px;
                border-radius: 50%; background: rgba(255, 255, 255, .09); pointer-events: none;
            }
            .cl-fin-title { font-size: 13px; font-weight: 500; opacity: .85; }
            .cl-fin-big { font-size: 30px; font-weight: 700; letter-spacing: -.02em; line-height: 1.1; margin-top: 4px; word-break: break-word; }
            .cl-fin-badge {
                align-self: flex-start; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 999px;
                background: rgba(255, 255, 255, .18);
            }
            .cl-fin-rows { display: flex; flex-direction: column; gap: 14px; position: relative; z-index: 1; }
            .cl-fin-row-top { display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 6px; }
            .cl-fin-row-top span:first-child { opacity: .85; }
            .cl-fin-row-top span:last-child { font-weight: 600; }
            .cl-track { height: 8px; border-radius: 999px; background: rgba(255, 255, 255, .2); overflow: hidden; }
            .cl-track > i { display: block; height: 100%; border-radius: 999px; background: #fff; }
            .cl-track.is-exp > i { background: #fda4af; }

            /* ============ Donut legend ============ */
            .cl-legend { display: flex; flex-direction: column; gap: 10px; }
            .cl-legend-row { display: flex; align-items: center; gap: 10px; font-size: 13px; }
            .cl-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
            .cl-legend-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .cl-legend-val { color: var(--cl-muted); font-size: 12px; }
            .cl-legend-pct { font-weight: 650; min-width: 46px; text-align: right; }
            .cl-empty { padding: 28px 12px; text-align: center; font-size: 13px; color: var(--cl-muted); }

            /* ============ Tables ============ */
            .cl-table-card { overflow: hidden; }
            .cl-table-head {
                padding: 18px 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
            }
            .cl-table-head h3 { margin: 0; font-size: 15px; font-weight: 650; }
            .cl-table-head p { margin: 3px 0 0; font-size: 12px; color: var(--cl-muted); }
            .cl-link {
                display: inline-flex; align-items: center; gap: 4px;
                font-size: 13px; font-weight: 600; color: var(--cl-brand); text-decoration: none;
                padding: 6px 10px; border-radius: 8px;
            }
            .cl-link:hover { background: color-mix(in srgb, var(--cl-brand) 10%, transparent); }
            .cl-link svg { width: 14px; height: 14px; }
            .cl-scroll { width: 100%; overflow-x: auto; }
            .cl-table { width: 100%; min-width: 760px; border-collapse: collapse; font-size: 13px; text-align: left; }
            .cl-table th {
                padding: 10px 20px; font-size: 12px; font-weight: 600; color: var(--cl-muted);
                background: var(--cl-card-alt); border-top: 1px solid var(--cl-border); border-bottom: 1px solid var(--cl-border);
                white-space: nowrap;
            }
            .cl-table td { padding: 13px 20px; border-bottom: 1px solid var(--cl-border); vertical-align: middle; }
            .cl-table tbody tr:last-child td { border-bottom: 0; }
            .cl-table tbody tr:hover td { background: var(--cl-card-alt); }
            .cl-r { text-align: right !important; }
            .cl-c { text-align: center !important; }
            .cl-strong { font-weight: 600; }
            .cl-sub { font-size: 12px; color: var(--cl-muted); margin-top: 2px; }
            .cl-prod { display: flex; align-items: center; gap: 12px; }
            .cl-prod img {
                width: 40px; height: 40px; border-radius: 10px; object-fit: cover; flex-shrink: 0;
                background: var(--cl-card-alt); border: 1px solid var(--cl-border);
            }
            .cl-prod-name { font-weight: 600; max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .cl-pill {
                display: inline-flex; align-items: center; gap: 6px;
                padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; white-space: nowrap;
                color: var(--t);
                background: color-mix(in srgb, var(--t) 13%, transparent);
            }
            .cl-pill i { width: 6px; height: 6px; border-radius: 50%; background: var(--t); }
            .cl-code {
                font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px;
                padding: 2px 8px; border-radius: 6px; color: var(--cl-muted);
                background: var(--cl-card-alt); border: 1px solid var(--cl-border);
            }
            .cl-row-btn {
                display: inline-block; padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;
                color: var(--cl-text); text-decoration: none; border: 1px solid var(--cl-border); background: var(--cl-card);
            }
            .cl-row-btn:hover { border-color: var(--cl-brand); color: var(--cl-brand); }
            .cl-row-btn:focus-visible { outline: 2px solid var(--cl-brand); outline-offset: 2px; }
        </style>

        {{-- ============ 1. Header ============ --}}
        <div class="cl-head">
            <div>
                <h1>{{ $greeting }}{{ $userName ? ', ' . \Illuminate\Support\Str::of($userName)->before(' ') : '' }}</h1>
                <p>Here's how ComLink is doing today, {{ now()->format('l, M d') }}.</p>
            </div>
            <div class="cl-actions">
                <a href="{{ $inventoryProductsUrl }}" class="cl-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Add inventory products
                </a>
                <a href="{{ $createGrnUrl }}" class="cl-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Create GRN
                </a>
                <a href="{{ $createProductUrl }}" class="cl-btn cl-btn--primary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add product
                </a>
            </div>
        </div>

        {{-- ============ 2. KPI cards ============ --}}
        <div class="cl-kpis">
            {{-- Our store --}}
            <div class="cl-card cl-kpi t-blue">
                <div class="cl-kpi-top">
                    <span class="cl-chip"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></span>
                    <span class="cl-kpi-label">Our store</span>
                </div>
                <div class="cl-kpi-value">{{ number_format($productsCount) }} <small>products</small></div>
                <div class="cl-kpi-note">
                    <span><b>{{ $categoriesCount }}</b> categories</span>
                    <span><b>{{ $subCategoriesCount }}</b> sub categories</span>
                </div>
            </div>

            {{-- Total sales --}}
            <div class="cl-card cl-kpi t-green">
                <div class="cl-kpi-top">
                    <span class="cl-chip"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                    <span class="cl-kpi-label">Total sales</span>
                </div>
                <div class="cl-kpi-value" title="Rs. {{ number_format($totalSales, 2) }}"><small>Rs.</small>{{ $compact($totalSales) }}</div>
                <div class="cl-kpi-note">From paid customer orders</div>
            </div>

            {{-- Orders --}}
            <div class="cl-card cl-kpi t-violet">
                <div class="cl-kpi-top">
                    <span class="cl-chip"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg></span>
                    <span class="cl-kpi-label">Orders</span>
                </div>
                <div class="cl-kpi-value">{{ number_format($ordersCount) }}</div>
                <div class="cl-kpi-note">
                    <span class="t-green"><b>{{ $paidOrdersCount }}</b> paid</span>
                    <span class="t-amber"><b>{{ $pendingOrdersCount }}</b> pending</span>
                </div>
            </div>

            {{-- Expenses --}}
            <div class="cl-card cl-kpi t-red">
                <div class="cl-kpi-top">
                    <span class="cl-chip"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 7l-10 10m0 0h8m-8 0V9"/></svg></span>
                    <span class="cl-kpi-label">Expenses</span>
                </div>
                <div class="cl-kpi-value" title="Rs. {{ number_format($totalExpenses, 2) }}"><small>Rs.</small>{{ $compact($totalExpenses) }}</div>
                <div class="cl-kpi-note">Purchase cost of GRN stock</div>
            </div>

            {{-- Revenue --}}
            <!-- <div class="cl-card cl-kpi t-cyan">
                <div class="cl-kpi-top">
                    <span class="cl-chip"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></span>
                    <span class="cl-kpi-label">Revenue</span>
                </div>
                <div class="cl-kpi-value" title="Rs. {{ number_format($totalRevenue, 2) }}"><small>Rs.</small>{{ $compact($totalRevenue) }}</div>
                <div class="cl-kpi-note">All-time income from sales</div>
            </div> -->

            {{-- Net profit --}}
            <div class="cl-card cl-kpi cl-kpi--profit {{ $isProfitable ? 'is-up t-green' : 'is-down t-red' }}">
                <div class="cl-kpi-top">
                    <span class="cl-chip"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isProfitable ? 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' : 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6' }}"/></svg></span>
                    <span class="cl-kpi-label">Net profit</span>
                </div>
                <div class="cl-kpi-value" style="color: var(--t);" title="Rs. {{ number_format($netProfit, 2) }}">{{ $isProfitable ? '+' : '' }}<small style="color: inherit;">Rs.</small>{{ $compact($netProfit) }}</div>
                <div class="cl-kpi-note">
                    <span class="cl-pill"><i></i>{{ $isProfitable ? 'Profitable' : 'Loss' }}</span>
                    <span>{{ $profitMargin }}% margin</span>
                </div>
            </div>
        </div>

        {{-- ============ 3. Trend + financial overview ============ --}}
        <div class="cl-row cl-row--trend">
            <div class="cl-card cl-panel">
                <div class="cl-panel-head">
                    <h3>Monthly sales trend</h3>
                    <p>Sales revenue across recent months</p>
                </div>
                <div class="cl-canvas"><canvas id="comlinkChartTwo" aria-label="Monthly sales trend line chart" role="img"></canvas></div>
            </div>

            <div class="cl-fin">
                <div>
                    <div class="cl-fin-title">Net profit</div>
                    <div class="cl-fin-big">{{ $isProfitable ? '+' : '' }}Rs. {{ number_format($netProfit, 2) }}</div>
                </div>
                <span class="cl-fin-badge">{{ $profitMargin }}% margin on revenue</span>
                <div class="cl-fin-rows">
                    <div>
                        <div class="cl-fin-row-top"><span>Revenue</span><span>Rs. {{ $compact($totalRevenue) }}</span></div>
                        <div class="cl-track"><i style="width: {{ $revWidth }}%"></i></div>
                    </div>
                    <div>
                        <div class="cl-fin-row-top"><span>Expenses</span><span>Rs. {{ $compact($totalExpenses) }}</span></div>
                        <div class="cl-track is-exp"><i style="width: {{ $expWidth }}%"></i></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ 4. Expense vs income + category share ============ --}}
        <div class="cl-row cl-row--split">
            <div class="cl-card cl-panel">
                <div class="cl-panel-head">
                    <h3>Stock expense vs income</h3>
                    <p>Monthly GRN purchase cost compared with sales income</p>
                </div>
                <div class="cl-canvas"><canvas id="comlinkChartOne" aria-label="Monthly stock expense versus sales income bar chart" role="img"></canvas></div>
            </div>

            <div class="cl-card cl-panel">
                <div class="cl-panel-head">
                    <h3>Sales by category</h3>
                    <p>Share of sales per category</p>
                </div>
                @if(count($donutLabels))
                    <div class="cl-canvas cl-canvas--donut"><canvas id="comlinkChartThree" aria-label="Category sales share donut chart" role="img"></canvas></div>
                    <div class="cl-legend">
                        @foreach($donutLabels as $i => $label)
                            <div class="cl-legend-row">
                                <span class="cl-dot" style="background: {{ $donutColors[$i % count($donutColors)] }}"></span>
                                <span class="cl-legend-name">{{ $label }}</span>
                                <span class="cl-legend-val">Rs. {{ $compact($donutValues[$i] ?? 0) }}</span>
                                <span class="cl-legend-pct">{{ $donutPcts[$i] ?? 0 }}%</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="cl-empty">No category sales yet. Paid orders will appear here.</div>
                @endif
            </div>
        </div>

        {{-- ============ SALES ANALYZER SECTION ============ --}}
        <div class="cl-card cl-panel" style="padding: 24px; gap: 20px;">
            <!-- Header with Title and Link to Sales Info -->
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; border-bottom: 1px solid var(--cl-border); padding-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="cl-chip t-cyan" style="width: 44px; height: 44px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 22px; height: 22px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <h2 style="margin: 0; font-size: 18px; font-weight: 700; letter-spacing: -.01em;">Sales Analyzer</h2>
                        <p style="margin: 3px 0 0; font-size: 13px; color: var(--cl-muted);">Real-time revenue performance, order economics, and top-selling hardware analysis</p>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <a href="{{ $salesAnalyzer['salesInfoUrl'] }}" class="cl-btn cl-btn--primary" style="padding: 7px 14px; font-size: 12px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        View Sales Info Table
                    </a>
                </div>
            </div>

            <!-- 4 Key Sales Insights Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
                <!-- 1. Average Order Value -->
                <div style="background: var(--cl-card-alt); border: 1px solid var(--cl-border); border-radius: 12px; padding: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--cl-muted);">Avg. Order Value (AOV)</span>
                        <span class="cl-chip t-blue" style="width: 28px; height: 28px;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></span>
                    </div>
                    <div style="font-size: 20px; font-weight: 800; color: var(--cl-text);">
                        Rs. {{ number_format($salesAnalyzer['avgOrderValue'], 2) }}
                    </div>
                    <div style="font-size: 11px; color: var(--cl-faint); margin-top: 4px;">
                        Average spend per paid customer
                    </div>
                </div>

                <!-- 2. Total Hardware Units Sold -->
                <div style="background: var(--cl-card-alt); border: 1px solid var(--cl-border); border-radius: 12px; padding: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--cl-muted);">Total Units Sold</span>
                        <span class="cl-chip t-green" style="width: 28px; height: 28px;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></span>
                    </div>
                    <div style="font-size: 20px; font-weight: 800; color: var(--cl-text);">
                        {{ $salesAnalyzer['totalPaidUnits'] }} <span style="font-size: 13px; font-weight: 600; color: var(--cl-muted);">Units</span>
                    </div>
                    <div style="font-size: 11px; color: var(--cl-faint); margin-top: 4px;">
                        Across {{ $salesAnalyzer['paidOrdersCount'] }} successful orders
                    </div>
                </div>

                <!-- 3. Highest Single Order -->
                <div style="background: var(--cl-card-alt); border: 1px solid var(--cl-border); border-radius: 12px; padding: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--cl-muted);">Highest Single Sale</span>
                        <span class="cl-chip t-violet" style="width: 28px; height: 28px;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg></span>
                    </div>
                    <div style="font-size: 20px; font-weight: 800; color: var(--cl-text);">
                        Rs. {{ number_format($salesAnalyzer['highestOrderAmount'], 2) }}
                    </div>
                    <div style="font-size: 11px; color: var(--cl-faint); margin-top: 4px;">
                        Order #{{ $salesAnalyzer['highestOrderNumber'] }}
                    </div>
                </div>

                <!-- 4. This Month's Sales -->
                <div style="background: var(--cl-card-alt); border: 1px solid var(--cl-border); border-radius: 12px; padding: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--cl-muted);">This Month's Sales</span>
                        <span class="cl-chip t-cyan" style="width: 28px; height: 28px;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></span>
                    </div>
                    <div style="font-size: 20px; font-weight: 800; color: var(--cl-brand);">
                        Rs. {{ number_format($salesAnalyzer['thisMonthSales'], 2) }}
                    </div>
                    <div style="font-size: 11px; color: var(--cl-faint); margin-top: 4px;">
                        Today: Rs. {{ number_format($salesAnalyzer['todaySales'], 2) }}
                    </div>
                </div>
            </div>

            <!-- Two Columns: Top Selling Products & Pipeline Fulfillment -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin-top: 4px;">
                <!-- Left: Top Selling Hardware Products -->
                <div style="border: 1px solid var(--cl-border); border-radius: 14px; padding: 16px; background: var(--cl-card);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                        <div>
                            <h4 style="margin: 0; font-size: 14px; font-weight: 700;">Top 5 Best-Selling Hardware</h4>
                            <p style="margin: 2px 0 0; font-size: 12px; color: var(--cl-muted);">Highest revenue generating products</p>
                        </div>
                        <span class="cl-pill t-cyan" style="font-size: 10px;">By Revenue</span>
                    </div>

                    @if(!empty($salesAnalyzer['topSellingProducts']))
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            @foreach($salesAnalyzer['topSellingProducts'] as $index => $prod)
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 10px; border-radius: 10px; background: var(--cl-card-alt); border: 1px solid var(--cl-border);">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <div style="width: 22px; height: 22px; border-radius: 50%; background: var(--cl-border); display: grid; place-items: center; font-size: 11px; font-weight: 700; flex-shrink: 0;">
                                            {{ $index + 1 }}
                                        </div>
                                        <img src="{{ $prod['image'] }}" alt="" onerror="this.src='/images/placeholder.png';" style="width: 34px; height: 34px; border-radius: 6px; object-fit: contain; background: #fff; padding: 2px; flex-shrink: 0;" />
                                        <div style="min-width: 0;">
                                            <div style="font-size: 12.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--cl-text);" title="{{ $prod['name'] }}">
                                                {{ $prod['name'] }}
                                            </div>
                                            <div style="font-size: 11px; color: var(--cl-muted);">
                                                {{ $prod['units'] }} {{ $prod['units'] === 1 ? 'unit' : 'units' }} sold • {{ $prod['category'] }}
                                            </div>
                                        </div>
                                    </div>
                                    <div style="text-align: right; flex-shrink: 0;">
                                        <div style="font-size: 13px; font-weight: 700; color: var(--cl-brand);">
                                            Rs. {{ number_format($prod['revenue'], 2) }}
                                        </div>
                                        <div style="font-size: 10.5px; color: var(--cl-muted); font-weight: 500;">
                                            {{ $prod['percentage'] }}% of sales
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="cl-empty" style="padding: 24px;">No sold hardware recorded yet.</div>
                    @endif
                </div>

                <!-- Right: Sales Order Status Pipeline -->
                <div style="border: 1px solid var(--cl-border); border-radius: 14px; padding: 16px; background: var(--cl-card);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                        <div>
                            <h4 style="margin: 0; font-size: 14px; font-weight: 700;">Paid Orders Fulfillment Pipeline</h4>
                            <p style="margin: 2px 0 0; font-size: 12px; color: var(--cl-muted);">Revenue by current fulfillment stage</p>
                        </div>
                        <span class="cl-pill t-green" style="font-size: 10px;">{{ $salesAnalyzer['paymentSuccessRate'] }}% Paid Rate</span>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @foreach($salesAnalyzer['salesPipeline'] as $pipe)
                            @php
                                $tone = match(strtolower($pipe['stage'])) {
                                    'processing' => 't-blue',
                                    'packaging'  => 't-violet',
                                    'shipping'   => 't-cyan',
                                    'delivered'  => 't-green',
                                    default      => 't-slate',
                                };
                            @endphp
                            <div style="padding: 10px 12px; border-radius: 10px; background: var(--cl-card-alt); border: 1px solid var(--cl-border);">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="cl-pill {{ $tone }}" style="font-size: 11px; padding: 2px 8px;">
                                            <i></i>{{ $pipe['stage'] }}
                                        </span>
                                        <span style="font-size: 12px; color: var(--cl-muted);">
                                            {{ $pipe['count'] }} {{ $pipe['count'] === 1 ? 'order' : 'orders' }}
                                        </span>
                                    </div>
                                    <span style="font-size: 13px; font-weight: 700; color: var(--cl-text);">
                                        Rs. {{ number_format($pipe['amount'], 2) }}
                                    </span>
                                </div>
                                <!-- Progress line -->
                                <div style="width: 100%; height: 6px; background: var(--cl-border); border-radius: 3px; overflow: hidden;">
                                    <div style="width: {{ $pipe['percentage'] }}%; height: 100%; background: var(--cl-brand); border-radius: 3px; transition: width .4s ease;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ 5. Latest orders ============ --}}
        <div class="cl-card cl-table-card">
            <div class="cl-table-head">
                <div>
                    <h3>Latest customer orders</h3>
                    <p>Recent orders from the storefront</p>
                </div>
                <a href="{{ $allOrdersUrl }}" class="cl-link">
                    View all orders
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="cl-scroll">
                <table class="cl-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th class="cl-r">Total</th>
                            <th class="cl-c">Payment</th>
                            <th class="cl-c">Status</th>
                            <th>Date</th>
                            <th class="cl-r">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestOrders as $ord)
                            @php
                                $isPaid = strcasecmp((string) $ord->payment_status, 'Paid') === 0;
                                $statusLabel = \Illuminate\Support\Str::headline((string) ($ord->status ?: 'Pending'));
                            @endphp
                            <tr>
                                <td><span class="cl-strong" style="color: var(--cl-brand);">{{ $ord->order_number ?: ('#' . $ord->id) }}</span></td>
                                <td>
                                    <div class="cl-strong">{{ $ord->customer_name ?: ($ord->user?->name ?? 'Guest customer') }}</div>
                                    <div class="cl-sub">{{ $ord->customer_phone ?: $ord->customer_email }}</div>
                                </td>
                                <td>{{ $ord->items->sum('quantity') }} {{ $ord->items->sum('quantity') === 1 ? 'item' : 'items' }}</td>
                                <td class="cl-r cl-strong">Rs. {{ number_format((float) $ord->total_amount, 2) }}</td>
                                <td class="cl-c">
                                    <span class="cl-pill {{ $isPaid ? 't-green' : 't-amber' }}"><i></i>{{ $isPaid ? 'Paid' : ($ord->payment_status ?: 'Pending') }}</span>
                                </td>
                                <td class="cl-c">
                                    <span class="cl-pill t-{{ $statusTone($ord->status ?: 'pending') }}"><i></i>{{ $statusLabel }}</span>
                                </td>
                                <td style="color: var(--cl-muted);">{{ $ord->created_at ? $ord->created_at->format('M d, Y · h:i A') : 'N/A' }}</td>
                                <td class="cl-r"><a class="cl-row-btn" href="{{ url('/admin/order-resource/orders/' . $ord->id) }}">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="cl-empty">No customer orders yet. New storefront orders will show up here.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ============ 6. Latest products ============ --}}
        <div class="cl-card cl-table-card">
            <div class="cl-table-head">
                <div>
                    <h3>Latest added products</h3>
                    <p>Recently added accessories and hardware</p>
                </div>
                <a href="{{ $allProductsUrl }}" class="cl-link">
                    View all products
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="cl-scroll">
                <table class="cl-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Code</th>
                            <th>Condition</th>
                            <th class="cl-r">Price</th>
                            <th class="cl-c">Stock</th>
                            <th>Added</th>
                            <th class="cl-r">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestProducts as $prod)
                            @php
                                $thumb = null;
                                if (!empty($prod->images)) {
                                    $imgs = is_array($prod->images) ? $prod->images : [];
                                    $first = $imgs[0] ?? null;
                                    if ($first) {
                                        $thumb = (str_starts_with($first, 'http') || str_starts_with($first, '/')) ? $first : asset('storage/' . $first);
                                    }
                                }
                                $thumb = $thumb ?: '/images/placeholder.png';
                                $mainCat = $prod->categories->firstWhere('parent_id', null);
                                $subCat = $prod->categories->firstWhere('parent_id', '!=', null);
                                $latestPrice = (float) ($prod->sale_price ?: $prod->price ?: 0);
                                $stock = (int) ($prod->quantity ?? $prod->inventory_stock ?? 0);
                                $stockTone = $stock <= 0 ? 'red' : ($stock <= 5 ? 'amber' : 'green');
                            @endphp
                            <tr>
                                <td>
                                    <div class="cl-prod">
                                        <img src="{{ $thumb }}" alt="" onerror="this.src='/images/placeholder.png';" />
                                        <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0;">
                                            <div class="cl-prod-name" title="{{ $prod->name }}">{{ $prod->name }}</div>
                                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 4px;">
                                                @if($mainCat)
                                                    <span class="cl-pill t-cyan" style="font-size: 10px; padding: 1px 6px;">{{ $mainCat->name }}</span>
                                                @endif
                                                @if($subCat)
                                                    <span class="cl-pill t-slate" style="font-size: 10px; padding: 1px 6px;">{{ $subCat->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="cl-code">{{ $prod->product_code ?? 'N/A' }}</span></td>
                                <td><span class="cl-pill t-slate">{{ $prod->condition ?? 'Brand new' }}</span></td>
                                <td class="cl-r cl-strong">Rs. {{ number_format($latestPrice, 2) }}</td>
                                <td class="cl-c">
                                    <span class="cl-pill t-{{ $stockTone }}"><i></i>{{ $stock > 0 ? $stock . ' in stock' : 'Out of stock' }}</span>
                                </td>
                                <td style="color: var(--cl-muted);">{{ $prod->created_at ? $prod->created_at->format('M d, Y') : 'N/A' }}</td>
                                <td class="cl-r"><a class="cl-row-btn" href="{{ url('/admin/product-resource/products/' . $prod->id . '/edit') }}">Edit</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="cl-empty">No products yet. Add your first product to get started.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============ Chart.js ============ --}}
    <script>
        (function () {
            var CHART_SRC = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js';

            function compactRs(v) {
                var n = Number(v);
                if (Math.abs(n) >= 1000000) return 'Rs ' + (n / 1000000).toFixed(1) + 'M';
                if (Math.abs(n) >= 1000) return 'Rs ' + (n / 1000).toFixed(0) + 'k';
                return 'Rs ' + n;
            }
            function money(v) {
                return 'Rs. ' + Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function draw() {
                var root = document.getElementById('comlink-dashboard');
                if (!root || typeof Chart === 'undefined') return;

                (window.__clCharts || []).forEach(function (c) { try { c.destroy(); } catch (e) {} });
                window.__clCharts = [];

                var data = JSON.parse(root.dataset.charts);
                var dark = document.documentElement.classList.contains('dark');
                var grid = dark ? 'rgba(255,255,255,.06)' : 'rgba(15,23,42,.06)';
                var tick = dark ? '#94a3b8' : '#64748b';
                var cardBg = dark ? '#0f1724' : '#ffffff';
                var textMain = dark ? '#f1f5f9' : '#0f172a';
                var brand = dark ? '#22d3ee' : '#0891b2';
                var indigo = dark ? '#818cf8' : '#6366f1';

                Chart.defaults.color = tick;
                Chart.defaults.font.family = getComputedStyle(root).fontFamily;

                var tooltip = {
                    backgroundColor: dark ? '#020617' : '#0f172a',
                    titleColor: '#f8fafc', bodyColor: '#e2e8f0',
                    padding: 10, cornerRadius: 8, displayColors: true, boxPadding: 4
                };
                var yAxis = {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: grid },
                    ticks: { color: tick, font: { size: 11 }, callback: compactRs }
                };
                var xAxis = { border: { display: false }, grid: { display: false }, ticks: { color: tick, font: { size: 11 } } };

                // Sales trend (area)
                var two = document.getElementById('comlinkChartTwo');
                if (two) {
                    window.__clCharts.push(new Chart(two, {
                        type: 'line',
                        data: {
                            labels: data.two.labels,
                            datasets: [{
                                label: 'Sales revenue',
                                data: data.two.sales,
                                borderColor: brand,
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 0,
                                pointHoverRadius: 6,
                                pointHoverBackgroundColor: brand,
                                pointHoverBorderColor: cardBg,
                                pointHoverBorderWidth: 3,
                                backgroundColor: function (ctx) {
                                    var area = ctx.chart.chartArea;
                                    if (!area) return 'transparent';
                                    var g = ctx.chart.ctx.createLinearGradient(0, area.top, 0, area.bottom);
                                    g.addColorStop(0, dark ? 'rgba(34,211,238,.30)' : 'rgba(8,145,178,.22)');
                                    g.addColorStop(1, 'rgba(8,145,178,0)');
                                    return g;
                                }
                            }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { display: false },
                                tooltip: Object.assign({}, tooltip, { callbacks: { label: function (c) { return ' ' + money(c.raw); } } })
                            },
                            scales: { x: xAxis, y: yAxis }
                        }
                    }));
                }

                // Expense vs income (grouped bars)
                var one = document.getElementById('comlinkChartOne');
                if (one) {
                    window.__clCharts.push(new Chart(one, {
                        type: 'bar',
                        data: {
                            labels: data.one.labels,
                            datasets: [
                                { label: 'Stock expenses (GRN)', data: data.one.expenses, backgroundColor: indigo, borderRadius: 6, borderSkipped: false, maxBarThickness: 26 },
                                { label: 'Sales income', data: data.one.incomes, backgroundColor: brand, borderRadius: 6, borderSkipped: false, maxBarThickness: 26 }
                            ]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { position: 'top', align: 'end', labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, boxHeight: 8, padding: 16, color: tick } },
                                tooltip: Object.assign({}, tooltip, { callbacks: { label: function (c) { return ' ' + c.dataset.label + ': ' + money(c.raw); } } })
                            },
                            scales: { x: xAxis, y: yAxis }
                        }
                    }));
                }

                // Category share (donut with centre total)
                var three = document.getElementById('comlinkChartThree');
                if (three) {
                    var total = (data.three.values || []).reduce(function (a, b) { return a + Number(b); }, 0);
                    var centre = {
                        id: 'clCentre',
                        afterDraw: function (chart) {
                            var m = chart.getDatasetMeta(0).data[0];
                            if (!m) return;
                            var c = chart.ctx;
                            c.save();
                            c.textAlign = 'center';
                            c.textBaseline = 'middle';
                            c.fillStyle = tick;
                            c.font = '500 12px ' + Chart.defaults.font.family;
                            c.fillText('Total sales', m.x, m.y - 12);
                            c.fillStyle = textMain;
                            c.font = '700 20px ' + Chart.defaults.font.family;
                            c.fillText(compactRs(total).replace('Rs ', 'Rs. '), m.x, m.y + 10);
                            c.restore();
                        }
                    };
                    window.__clCharts.push(new Chart(three, {
                        type: 'doughnut',
                        data: {
                            labels: data.three.labels,
                            datasets: [{
                                data: data.three.values,
                                backgroundColor: data.three.colors.slice(0, data.three.labels.length),
                                borderColor: cardBg, borderWidth: 3, hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false, cutout: '74%',
                            plugins: {
                                legend: { display: false },
                                tooltip: Object.assign({}, tooltip, { callbacks: { label: function (c) {
                                    var pct = (data.three.percentages || [])[c.dataIndex] || 0;
                                    return ' ' + c.label + ': ' + money(c.raw) + ' (' + pct + '%)';
                                } } })
                            }
                        },
                        plugins: [centre]
                    }));
                }
            }
            window.__clDraw = draw;

            function boot() {
                if (typeof Chart !== 'undefined') { draw(); return; }
                var existing = document.querySelector('script[data-comlink-chartjs]');
                if (existing) { existing.addEventListener('load', draw); return; }
                var s = document.createElement('script');
                s.src = CHART_SRC;
                s.setAttribute('data-comlink-chartjs', '1');
                s.onload = draw;
                document.head.appendChild(s);
            }
            window.__clBoot = boot;

            // Register global listeners once only
            if (!window.__clListeners) {
                window.__clListeners = true;
                document.addEventListener('livewire:navigated', function () { window.__clBoot && window.__clBoot(); });
                new MutationObserver(function () { window.__clDraw && window.__clDraw(); })
                    .observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', boot);
            } else {
                boot();
            }
        })();
    </script>
</x-filament-panels::page>