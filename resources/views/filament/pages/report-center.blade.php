<x-filament-panels::page>
    <div class="cl-rc">
        <style>
            .cl-rc {
                --rc-card: #ffffff;
                --rc-card-alt: #f8fafc;
                --rc-border: #e2e8f0;
                --rc-text: #0f172a;
                --rc-muted: #64748b;
                --rc-faint: #94a3b8;
                --rc-brand: #0891b2;
                --rc-brand-hover: #0e7490;
                --rc-shadow: 0 1px 3px rgba(15, 23, 42, .05), 0 8px 24px -10px rgba(15, 23, 42, .08);

                display: flex;
                flex-direction: column;
                gap: 20px;
                width: 100%;
                color: var(--rc-text);
                font-variant-numeric: tabular-nums;
            }
            .dark .cl-rc {
                --rc-card: #0f1724;
                --rc-card-alt: #162032;
                --rc-border: rgba(255, 255, 255, 0.09);
                --rc-text: #f1f5f9;
                --rc-muted: #94a3b8;
                --rc-faint: #64748b;
                --rc-brand: #22d3ee;
                --rc-brand-hover: #06b6d4;
                --rc-shadow: 0 2px 6px rgba(0, 0, 0, .25);
            }

            .rc-panel {
                background: var(--rc-card);
                border: 1px solid var(--rc-border);
                border-radius: 14px;
                box-shadow: var(--rc-shadow);
                overflow: hidden;
            }
            .rc-header {
                padding: 18px 22px;
                border-bottom: 1px solid var(--rc-border);
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
            }
            .rc-title {
                margin: 0;
                font-size: 17px;
                font-weight: 800;
                letter-spacing: -0.01em;
                color: var(--rc-text);
            }
            .rc-subtitle {
                margin: 3px 0 0;
                font-size: 12.5px;
                color: var(--rc-muted);
            }
            .rc-actions {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 10px;
            }
            .rc-btn {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                padding: 8px 14px;
                border-radius: 9px;
                font-size: 12.5px;
                font-weight: 600;
                text-decoration: none;
                cursor: pointer;
                border: 1px solid var(--rc-border);
                background: var(--rc-card-alt);
                color: var(--rc-text);
                transition: all .15s ease;
            }
            .rc-btn:hover {
                border-color: var(--rc-brand);
                color: var(--rc-brand);
            }
            .rc-btn--primary {
                background: #0284c7;
                border-color: #0284c7;
                color: #ffffff;
            }
            .rc-btn--primary:hover {
                background: #0369a1;
                border-color: #0369a1;
                color: #ffffff;
            }

            .rc-filters-wrap {
                padding: 16px 22px;
                background: var(--rc-card-alt);
            }
            .rc-filters-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(175px, 1fr));
                gap: 12px;
            }
            .rc-field label {
                display: block;
                font-size: 10.5px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: var(--rc-muted);
                margin-bottom: 5px;
            }
            .rc-select, .rc-date {
                width: 100%;
                font-size: 12.5px;
                font-weight: 500;
                border-radius: 9px;
                border: 1px solid var(--rc-border);
                background-color: var(--rc-card);
                background-repeat: no-repeat;
                background-position: right 0.6rem center;
                background-size: 1.1em 1.1em;
                color: var(--rc-text);
                padding: 7px 2rem 7px 10px;
                outline: none;
                transition: border-color .15s ease;
            }
            .rc-date {
                padding-right: 10px;
            }
            .rc-select:focus, .rc-date:focus {
                border-color: var(--rc-brand);
            }
            .rc-combo-wrap {
                position: relative;
            }
            .rc-combo-trigger {
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 6px;
                font-size: 12.5px;
                font-weight: 500;
                border-radius: 9px;
                border: 1px solid var(--rc-border);
                background-color: var(--rc-card);
                color: var(--rc-text);
                padding: 7px 10px;
                cursor: pointer;
                text-align: left;
                transition: border-color .15s ease;
            }
            .rc-combo-trigger:hover, .rc-combo-trigger:focus {
                border-color: var(--rc-brand);
                outline: none;
            }
            .rc-combo-menu {
                position: absolute;
                top: calc(100% + 6px);
                left: 0;
                right: 0;
                min-width: 260px;
                z-index: 50;
                background: var(--rc-card);
                border: 1px solid var(--rc-border);
                border-radius: 10px;
                box-shadow: 0 12px 30px rgba(0, 0, 0, 0.28);
                overflow: hidden;
            }
            .rc-combo-search {
                width: 100%;
                border: none;
                border-bottom: 1px solid var(--rc-border);
                background: var(--rc-card-alt);
                color: var(--rc-text);
                font-size: 12.5px;
                padding: 8px 11px;
                outline: none;
            }
            .rc-combo-list {
                max-height: 240px;
                overflow-y: auto;
            }
            .rc-combo-opt {
                width: 100%;
                text-align: left;
                padding: 7px 11px;
                font-size: 12px;
                color: var(--rc-text);
                background: transparent;
                border: none;
                cursor: pointer;
                display: flex;
                justify-content: space-between;
                gap: 8px;
            }
            .rc-combo-opt:hover, .rc-combo-opt.is-active {
                background: color-mix(in srgb, var(--rc-brand) 14%, transparent);
                color: var(--rc-brand);
            }

            .rc-scope-bar {
                margin-top: 14px;
                padding-top: 12px;
                border-top: 1px solid var(--rc-border);
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                font-size: 12px;
            }
            .rc-scope-pill {
                display: inline-block;
                padding: 3px 10px;
                border-radius: 7px;
                background: color-mix(in srgb, var(--rc-brand) 12%, transparent);
                color: var(--rc-brand);
                font-weight: 700;
                font-size: 11.5px;
            }

            .rc-kpis {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
                gap: 14px;
            }
            .rc-kpi {
                --t: #64748b;
                background: var(--rc-card);
                border: 1px solid var(--rc-border);
                border-left: 4px solid var(--t);
                border-radius: 12px;
                padding: 15px 18px;
                box-shadow: var(--rc-shadow);
            }
            .rc-kpi.t-slate  { --t: #64748b; }
            .rc-kpi.t-cyan   { --t: #06b6d4; }
            .rc-kpi.t-blue   { --t: #3b82f6; }
            .rc-kpi.t-violet { --t: #8b5cf6; }
            .rc-kpi.t-green  { --t: #10b981; }
            .rc-kpi.t-amber  { --t: #f59e0b; }
            .rc-kpi.t-red    { --t: #ef4444; }
            .rc-kpi-label {
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: var(--rc-muted);
            }
            .rc-kpi-val {
                margin-top: 6px;
                font-size: 21px;
                font-weight: 800;
                color: var(--rc-text);
                letter-spacing: -0.02em;
            }

            .rc-table-head {
                padding: 14px 20px;
                background: var(--rc-card-alt);
                border-bottom: 1px solid var(--rc-border);
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
            }
            .rc-badge {
                display: inline-flex;
                align-items: center;
                padding: 2px 9px;
                border-radius: 999px;
                background: color-mix(in srgb, var(--rc-brand) 12%, transparent);
                color: var(--rc-brand);
                font-size: 11px;
                font-weight: 700;
                margin-left: 8px;
            }
            .rc-scroll {
                overflow-x: auto;
                max-height: 560px;
                overflow-y: auto;
            }
            .rc-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 12.5px;
                text-align: left;
            }
            .rc-table thead th {
                position: sticky;
                top: 0;
                z-index: 2;
                background: var(--rc-card-alt);
                color: var(--rc-muted);
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                padding: 11px 14px;
                border-bottom: 1px solid var(--rc-border);
                white-space: nowrap;
            }
            .rc-table tbody td {
                padding: 10px 14px;
                border-bottom: 1px solid var(--rc-border);
                color: var(--rc-text);
                white-space: nowrap;
            }
            .rc-table tbody tr:hover td {
                background: color-mix(in srgb, var(--rc-brand) 4%, transparent);
            }
        </style>

        {{-- Top Header + 6-Axis Filters --}}
        <div class="rc-panel" style="overflow: visible;">
            <div class="rc-header">
                <div>
                    <h2 class="rc-title">{{ $report['title'] }}</h2>
                    <p class="rc-subtitle">{{ $report['subtitle'] }}</p>
                </div>

                <div class="rc-actions">
                    <a href="{{ $printUrl }}" target="_blank" class="rc-btn">
                        <x-heroicon-o-printer style="width:15px;height:15px;" />
                        Print View
                    </a>
                    <a href="{{ $pdfUrl }}" class="rc-btn rc-btn--primary">
                        <x-heroicon-o-arrow-down-tray style="width:15px;height:15px;" />
                        Download Report (PDF)
                    </a>
                </div>
            </div>

            <div class="rc-filters-wrap">
                <div class="rc-filters-grid">
                    <div class="rc-field">
                        <label>Category</label>
                        <select wire:model.live="categoryId" class="rc-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rc-field">
                        <label>SubCategory</label>
                        <select wire:model.live="subCategoryId" class="rc-select">
                            <option value="">All Subcategories</option>
                            @foreach($subCategories as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @php
                        $productOptions = $products->map(fn ($p) => [
                            'id' => $p->id,
                            'name' => $p->name,
                            'code' => $p->product_code ?: '',
                            'label' => $p->name . ($p->product_code ? " ({$p->product_code})" : ''),
                        ])->values()->all();
                    @endphp
                    <div class="rc-field"
                        wire:key="prod-combo-{{ $categoryId ?? 'all' }}-{{ $subCategoryId ?? 'all' }}-{{ $supplierId ?? 'all' }}-{{ count($products) }}"
                        x-data="{
                            open: false,
                            search: '',
                            selectedId: @entangle('productId').live,
                            options: @js($productOptions),
                            get selectedLabel() {
                                if (!this.selectedId) return 'All Products (' + this.options.length + ')';
                                const found = this.options.find(o => String(o.id) === String(this.selectedId));
                                return found ? found.label : 'All Products (' + this.options.length + ')';
                            },
                            get filtered() {
                                const q = this.search.trim().toLowerCase();
                                if (!q) return this.options.slice(0, 80);
                                return this.options.filter(o =>
                                    o.name.toLowerCase().includes(q) || o.code.toLowerCase().includes(q)
                                ).slice(0, 80);
                            },
                            pick(id) {
                                this.selectedId = id;
                                this.open = false;
                                this.search = '';
                            }
                        }"
                        @click.outside="open = false"
                    >
                        <label>Product</label>
                        <div class="rc-combo-wrap">
                            <button type="button" class="rc-combo-trigger" @click="open = !open; if (open) $nextTick(() => $refs.prodSearch.focus())">
                                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="selectedLabel"></span>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px;flex-shrink:0;opacity:0.65;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open" x-transition.opacity style="display:none;" class="rc-combo-menu">
                                <input
                                    x-ref="prodSearch"
                                    x-model="search"
                                    type="text"
                                    placeholder="Type product name or code..."
                                    class="rc-combo-search"
                                />
                                <div class="rc-combo-list">
                                    <button type="button" class="rc-combo-opt" :class="{ 'is-active': !selectedId }" @click="pick(null)">
                                        <span>All Products (<span x-text="options.length"></span>)</span>
                                    </button>
                                    <template x-for="opt in filtered" :key="opt.id">
                                        <button type="button" class="rc-combo-opt" :class="{ 'is-active': String(selectedId) === String(opt.id) }" @click="pick(opt.id)">
                                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="opt.name"></span>
                                            <span x-show="opt.code" style="opacity:0.6;font-size:11px;flex-shrink:0;" x-text="opt.code"></span>
                                        </button>
                                    </template>
                                    <div x-show="filtered.length === 0" style="padding:10px 12px;font-size:12px;color:var(--rc-muted);text-align:center;">
                                        No matching products
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rc-field">
                        <label>Supplier</label>
                        <select wire:model.live="supplierId" class="rc-select">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rc-field">
                        <label>Product Type</label>
                        <select wire:model.live="productType" class="rc-select">
                            <option value="all">All Products</option>
                            <option value="sold">Sold Products Only</option>
                            <option value="unsold">Unsold Products Only</option>
                        </select>
                    </div>

                    <div class="rc-field">
                        <label>Time Period</label>
                        <select wire:model.live="period" class="rc-select">
                            <option value="all">All Time</option>
                            <option value="daily">Daily (Today)</option>
                            <option value="weekly">Weekly (Last 7 Days)</option>
                            <option value="monthly">Monthly (This Month)</option>
                            <option value="annually">Annually (This Year)</option>
                            <option value="custom">Selected Custom Range</option>
                        </select>
                    </div>
                </div>

                <div class="rc-scope-bar">
                    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;">
                        @if($period === 'custom')
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="color:var(--rc-muted);">From:</span>
                                <input type="date" wire:model.live="startDate" class="rc-date" style="width:auto;" />
                                <span style="color:var(--rc-muted);">To:</span>
                                <input type="date" wire:model.live="endDate" class="rc-date" style="width:auto;" />
                            </div>
                        @endif

                        @if($categoryId || $subCategoryId || $productId || $supplierId || $productType !== 'all' || $period !== 'all')
                            <button type="button" wire:click="resetFilters" class="rc-btn" style="padding:5px 10px;font-size:11.5px;">
                                <x-heroicon-o-x-mark style="width:13px;height:13px;" />
                                Reset Filters
                            </button>
                        @endif
                    </div>

                    <div>
                        <span style="color:var(--rc-muted);">Active Scope:</span>
                        <span class="rc-scope-pill">{{ $report['filter_description'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary KPI Cards --}}
        @if(!empty($report['summary_cards']))
            <div class="rc-kpis">
                @foreach($report['summary_cards'] as $card)
                    <div class="rc-kpi t-{{ $card['tone'] ?? 'slate' }}">
                        <div class="rc-kpi-label">{{ $card['label'] }}</div>
                        <div class="rc-kpi-val">{{ $card['value'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Report Data Table --}}
        <div class="rc-panel">
            <div class="rc-table-head">
                <div style="font-size:13.5px;font-weight:700;color:var(--rc-text);">
                    Report Preview
                    <span class="rc-badge">{{ number_format(count($report['rows'])) }} Records</span>
                </div>
                @if(count($report['rows']) > 100)
                    <span style="font-size:11.5px;color:var(--rc-muted);">
                        Showing first 100 rows in preview (PDF / Print includes all {{ number_format(count($report['rows'])) }} rows)
                    </span>
                @endif
            </div>

            <div class="rc-scroll">
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th style="width:42px;text-align:center;">#</th>
                            @foreach($report['columns'] as $col)
                                <th>{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(array_slice($report['rows'], 0, 100) as $idx => $row)
                            <tr>
                                <td style="text-align:center;color:var(--rc-faint);">{{ $idx + 1 }}</td>
                                @foreach($row as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($report['columns']) + 1 }}" style="text-align:center;padding:36px;color:var(--rc-muted);">
                                    No records found for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
