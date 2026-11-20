<x-filament-panels::page>
    <div class="cl-pc">
        <style>
            .cl-pc {
                --pc-card: #ffffff;
                --pc-card-alt: #f8fafc;
                --pc-border: #e2e8f0;
                --pc-text: #0f172a;
                --pc-muted: #64748b;
                --pc-faint: #94a3b8;
                --pc-brand: #0891b2;
                --pc-shadow: 0 1px 3px rgba(15, 23, 42, .05), 0 8px 24px -10px rgba(15, 23, 42, .08);

                display: flex;
                flex-direction: column;
                gap: 20px;
                width: 100%;
                color: var(--pc-text);
                font-variant-numeric: tabular-nums;
            }
            .dark .cl-pc {
                --pc-card: #0f1724;
                --pc-card-alt: #162032;
                --pc-border: rgba(255, 255, 255, 0.09);
                --pc-text: #f1f5f9;
                --pc-muted: #94a3b8;
                --pc-faint: #64748b;
                --pc-brand: #22d3ee;
                --pc-shadow: 0 2px 6px rgba(0, 0, 0, .25);
            }
            .pc-panel {
                background: var(--pc-card);
                border: 1px solid var(--pc-border);
                border-radius: 14px;
                box-shadow: var(--pc-shadow);
                overflow: hidden;
            }
            .pc-head {
                padding: 18px 22px;
                border-bottom: 1px solid var(--pc-border);
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
            }
            .pc-step-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 26px;
                height: 26px;
                border-radius: 8px;
                background: color-mix(in srgb, var(--pc-brand) 15%, transparent);
                color: var(--pc-brand);
                font-size: 13px;
                font-weight: 800;
                margin-right: 10px;
            }
            .pc-title {
                margin: 0;
                font-size: 16px;
                font-weight: 800;
                color: var(--pc-text);
                display: flex;
                align-items: center;
            }
            .pc-sub {
                margin: 4px 0 0 36px;
                font-size: 12.5px;
                color: var(--pc-muted);
            }
            .pc-body {
                padding: 18px 22px;
                background: var(--pc-card-alt);
            }
            .pc-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
                gap: 14px;
            }
            .pc-field label {
                display: block;
                font-size: 10.5px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: var(--pc-muted);
                margin-bottom: 5px;
            }
            .pc-select {
                width: 100%;
                font-size: 12.5px;
                font-weight: 500;
                border-radius: 9px;
                border: 1px solid var(--pc-border);
                background-color: var(--pc-card);
                background-repeat: no-repeat;
                background-position: right 0.6rem center;
                background-size: 1.1em 1.1em;
                color: var(--pc-text);
                padding: 7px 2rem 7px 10px;
                outline: none;
            }
            .pc-btn {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                padding: 9px 16px;
                border-radius: 9px;
                font-size: 12.5px;
                font-weight: 700;
                text-decoration: none;
                cursor: pointer;
                border: 1px solid var(--pc-border);
                background: var(--pc-card);
                color: var(--pc-text);
                transition: all .15s ease;
            }
            .pc-btn--primary {
                background: #0284c7;
                border-color: #0284c7;
                color: #ffffff;
            }
            .pc-btn--primary:hover { background: #0369a1; }
            .pc-btn--success {
                background: #059669;
                border-color: #059669;
                color: #ffffff;
            }
            .pc-btn--success:hover { background: #047857; }
            .pc-upload-box {
                border: 2px dashed var(--pc-border);
                border-radius: 12px;
                padding: 24px;
                text-align: center;
                background: var(--pc-card);
            }
            .pc-kpis {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(175px, 1fr));
                gap: 12px;
                margin-top: 16px;
            }
            .pc-kpi {
                --t: #64748b;
                background: var(--pc-card);
                border: 1px solid var(--pc-border);
                border-left: 4px solid var(--t);
                border-radius: 10px;
                padding: 12px 15px;
            }
            .pc-kpi.t-green { --t: #10b981; }
            .pc-kpi.t-amber { --t: #f59e0b; }
            .pc-kpi.t-red   { --t: #ef4444; }
            .pc-kpi.t-slate { --t: #64748b; }
            .pc-table-wrap {
                overflow-x: auto;
                max-height: 460px;
                overflow-y: auto;
                border-top: 1px solid var(--pc-border);
                background: var(--pc-card);
            }
            .pc-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 12.5px;
                text-align: left;
            }
            .pc-table th {
                position: sticky;
                top: 0;
                background: var(--pc-card-alt);
                color: var(--pc-muted);
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                padding: 10px 14px;
                border-bottom: 1px solid var(--pc-border);
                white-space: nowrap;
            }
            .pc-table td {
                padding: 9px 14px;
                border-bottom: 1px solid var(--pc-border);
                white-space: nowrap;
            }
            .pc-alert {
                margin-top: 14px;
                padding: 12px 16px;
                border-radius: 10px;
                font-size: 12px;
            }
            .pc-alert--err {
                background: rgba(239, 68, 68, 0.1);
                border: 1px solid rgba(239, 68, 68, 0.3);
                color: #ef4444;
            }
            .pc-alert--warn {
                background: rgba(245, 158, 11, 0.1);
                border: 1px solid rgba(245, 158, 11, 0.3);
                color: #f59e0b;
            }
        </style>

        {{-- STEP 1: EXPORT PRODUCTS TO EXCEL --}}
        <div class="pc-panel">
            <div class="pc-head">
                <div>
                    <h2 class="pc-title">
                        <span class="pc-step-badge">1</span>
                        Export Website Products to Excel (.xlsx)
                    </h2>
                    <p class="pc-sub">
                        Download your live products with Original Price, Latest Price, and auto-calculating Discount % formula. Edit in Excel &amp; save.
                    </p>
                </div>

                <a href="{{ $exportUrl }}" class="pc-btn pc-btn--primary">
                    <x-heroicon-o-arrow-down-tray style="width:16px;height:16px;" />
                    Download Excel Sheet ({{ number_format($matchingCount) }} Products)
                </a>
            </div>

            <div class="pc-body">
                <div class="pc-grid">
                    <div class="pc-field">
                        <label>Web Visibility Scope</label>
                        <select wire:model.live="visibility" class="pc-select">
                            <option value="visible">Web Visible Products Only (Default)</option>
                            <option value="all">All Products (Visible + Hidden)</option>
                            <option value="hidden">Hidden Products Only</option>
                        </select>
                    </div>

                    <div class="pc-field">
                        <label>Category</label>
                        <select wire:model.live="categoryId" class="pc-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pc-field">
                        <label>SubCategory</label>
                        <select wire:model.live="subCategoryId" class="pc-select">
                            <option value="">All Subcategories</option>
                            @foreach($subCategories as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pc-field">
                        <label>Supplier</label>
                        <select wire:model.live="supplierId" class="pc-select">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 2: UPLOAD EDITED EXCEL & PREVIEW DIFF --}}
        <div class="pc-panel">
            <div class="pc-head">
                <div>
                    <h2 class="pc-title">
                        <span class="pc-step-badge">2</span>
                        Import Edited Excel &amp; Sync Prices
                    </h2>
                    <p class="pc-sub">
                        Upload your edited <strong>.xlsx</strong> (or <strong>.csv</strong>) sheet. Discount % is recalculated automatically, and all changes are previewed before saving.
                    </p>
                </div>

                @if($previewResult)
                    <div style="display:flex;gap:10px;align-items:center;">
                        <button type="button" wire:click="clearUpload" class="pc-btn">
                            Clear File
                        </button>
                        @if(!empty($previewResult['changes']) && empty($previewResult['errors']))
                            <button
                                type="button"
                                wire:click="applyImportedChanges"
                                wire:confirm="Are you sure you want to update {{ count($previewResult['changes']) }} product/variant prices on the live website and admin panel?"
                                class="pc-btn pc-btn--success"
                            >
                                <x-heroicon-o-check-circle style="width:16px;height:16px;" />
                                Apply &amp; Sync {{ count($previewResult['changes']) }} Changes
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            <div class="pc-body">
                <div class="pc-upload-box">
                    <input
                        type="file"
                        wire:model="excelFile"
                        accept=".xlsx,.csv"
                        style="font-size:13px;color:var(--pc-text);"
                    />
                    <div wire:loading wire:target="excelFile" style="margin-top:8px;font-size:12px;color:var(--pc-brand);font-weight:600;">
                        Reading &amp; validating Excel rows...
                    </div>
                    @error('excelFile')
                        <p style="margin-top:8px;font-size:12px;color:#ef4444;font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>

                @if($previewResult)
                    <div class="pc-kpis">
                        <div class="pc-kpi t-slate">
                            <div style="font-size:10.5px;font-weight:700;color:var(--pc-muted);text-transform:uppercase;">Rows Scanned</div>
                            <div style="font-size:20px;font-weight:800;margin-top:4px;">{{ number_format($previewResult['total_rows']) }}</div>
                        </div>
                        <div class="pc-kpi t-green">
                            <div style="font-size:10.5px;font-weight:700;color:var(--pc-muted);text-transform:uppercase;">Changes Detected</div>
                            <div style="font-size:20px;font-weight:800;margin-top:4px;color:#10b981;">{{ number_format(count($previewResult['changes'])) }}</div>
                        </div>
                        <div class="pc-kpi t-amber">
                            <div style="font-size:10.5px;font-weight:700;color:var(--pc-muted);text-transform:uppercase;">Below Cost Warnings</div>
                            <div style="font-size:20px;font-weight:800;margin-top:4px;color:#f59e0b;">{{ number_format(count($previewResult['warnings'])) }}</div>
                        </div>
                        <div class="pc-kpi t-red">
                            <div style="font-size:10.5px;font-weight:700;color:var(--pc-muted);text-transform:uppercase;">Errors</div>
                            <div style="font-size:20px;font-weight:800;margin-top:4px;color:#ef4444;">{{ number_format(count($previewResult['errors'])) }}</div>
                        </div>
                    </div>

                    @if(!empty($previewResult['errors']))
                        <div class="pc-alert pc-alert--err">
                            <strong>Please fix these errors in Excel before applying:</strong>
                            <ul style="margin:6px 0 0 18px;list-style:disc;">
                                @foreach(array_slice($previewResult['errors'], 0, 15) as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!empty($previewResult['warnings']))
                        <div class="pc-alert pc-alert--warn">
                            <strong>Warning (Latest Price is below GRN Cost):</strong>
                            <ul style="margin:6px 0 0 18px;list-style:disc;">
                                @foreach(array_slice($previewResult['warnings'], 0, 10) as $warn)
                                    <li>{{ $warn }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endif
            </div>

            @if(!empty($previewResult['changes']))
                <div class="pc-table-wrap">
                    <table class="pc-table">
                        <thead>
                            <tr>
                                <th>Row</th>
                                <th>Product</th>
                                <th>Variant</th>
                                <th>GRN Cost</th>
                                <th>Original Price (Old → New)</th>
                                <th>Latest Price (Old → New)</th>
                                <th>Discount % (Auto)</th>
                                <th>Web Visible</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previewResult['changes'] as $chg)
                                <tr>
                                    <td style="color:var(--pc-faint);">#{{ $chg['row'] }}</td>
                                    <td>
                                        <div style="font-weight:700;">{{ $chg['product_name'] }}</div>
                                        <div style="font-size:11px;color:var(--pc-muted);">{{ $chg['product_code'] }}</div>
                                    </td>
                                    <td>{{ $chg['variant_name'] }}</td>
                                    <td style="color:var(--pc-muted);">Rs. {{ number_format($chg['grn_cost'], 2) }}</td>
                                    <td>
                                        <span style="color:var(--pc-muted);text-decoration:line-through;">Rs. {{ number_format($chg['old_orig'], 2) }}</span>
                                        <span style="margin:0 4px;">→</span>
                                        <strong style="color:var(--pc-text);">Rs. {{ number_format($chg['new_orig'], 2) }}</strong>
                                    </td>
                                    <td>
                                        <span style="color:var(--pc-muted);text-decoration:line-through;">Rs. {{ number_format($chg['old_latest'], 2) }}</span>
                                        <span style="margin:0 4px;">→</span>
                                        <strong style="color:{{ $chg['is_below_cost'] ? '#f59e0b' : '#10b981' }};">
                                            Rs. {{ number_format($chg['new_latest'], 2) }}
                                        </strong>
                                    </td>
                                    <td>
                                        <span style="color:var(--pc-muted);">{{ $chg['old_discount'] }}%</span>
                                        <span style="margin:0 4px;">→</span>
                                        <strong style="color:var(--pc-brand);">{{ $chg['new_discount'] }}%</strong>
                                    </td>
                                    <td>
                                        {{ $chg['old_visible'] ? 'Yes' : 'No' }} →
                                        <strong>{{ $chg['new_visible'] ? 'Yes' : 'No' }}</strong>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
