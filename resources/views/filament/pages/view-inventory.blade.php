<x-filament-panels::page>
    {{ $this->form }}

    @php
        $storefront = $record->storefrontProduct;
        $hasVariants = $storefront && $storefront->hasColorVariants() && $storefront->variants->isNotEmpty();
    @endphp

    @if($hasVariants)
        <div style="margin-top: 24px; padding: 20px; background: #0f172a; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.06); flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 10px; background: rgba(6, 182, 212, 0.15); color: #22d3ee;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                    </span>
                    <div>
                        <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #f8fafc;">
                            Color Variations — Inventory Product Information ({{ $storefront->variants->count() }} Variations)
                        </h3>
                        <p style="margin: 2px 0 0; font-size: 12px; color: #94a3b8;">
                            Detailed warehouse inventory stock, GRN procurement unit cost, and storefront pricing per color option.
                        </p>
                    </div>
                </div>
                <span style="font-size: 11px; padding: 4px 10px; border-radius: 9999px; background: rgba(6, 182, 212, 0.12); color: #22d3ee; font-weight: 600; border: 1px solid rgba(6, 182, 212, 0.25);">
                    Variable Product
                </span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px;">
                @foreach($storefront->variants as $variant)
                    @php
                        $vOrig = (float) ($variant->price ?? 0);
                        $vSale = $variant->sale_price !== null && (float) $variant->sale_price > 0 ? (float) $variant->sale_price : $vOrig;
                        $vGrn = (float) ($variant->grn_price ?: $storefront->grn_price ?: 0);
                        $vDisc = ($vOrig > 0 && $vSale < $vOrig) ? (int) round((($vOrig - $vSale) / $vOrig) * 100) : (int) ($variant->discount_percentage ?? 0);
                        $vStock = (int) ($variant->inventory_stock ?? $variant->stock ?? 0);
                        $vMargin = $vSale > 0 ? round((($vSale - $vGrn) / $vSale) * 100, 1) : 0;
                        $vImg = $variant->image ? ((str_starts_with($variant->image, 'http') || str_starts_with($variant->image, '/')) ? $variant->image : asset('storage/' . $variant->image)) : null;
                    @endphp
                    <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.07); border-radius: 12px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                @if($vImg)
                                    <img src="{{ $vImg }}" alt="{{ $variant->color_name }}" style="width: 48px; height: 48px; border-radius: 10px; object-fit: cover; border: 1px solid rgba(255,255,255,0.1); background: #1e293b;">
                                @else
                                    <div style="width: 48px; height: 48px; border-radius: 10px; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); display: grid; place-items: center; color: #64748b;">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 22px; height: 22px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-size: 14px; font-weight: 700; color: #f8fafc;">{{ $variant->color_name ?: 'Color Variant' }}</span>
                                        <span style="font-size: 10.5px; padding: 1px 6px; border-radius: 6px; background: rgba(6, 182, 212, 0.15); color: #22d3ee; font-weight: 600;">{{ $variant->condition ?: 'Brand New' }}</span>
                                    </div>
                                    <div style="font-size: 12px; color: #94a3b8; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $variant->variant_name ?: "{$storefront->name} - {$variant->color_name}" }}">
                                        {{ $variant->variant_name ?: "{$storefront->name} - {$variant->color_name}" }}
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span style="display: inline-block; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; background: {{ $vStock > 0 ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' }}; color: {{ $vStock > 0 ? '#34d399' : '#f87171' }};">
                                        {{ $vStock }} in stock
                                    </span>
                                </div>
                            </div>

                            {{-- 4 Core Metrics --}}
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                                <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 8px; padding: 10px;">
                                    <div style="font-size: 10.5px; color: #94a3b8; font-weight: 600; text-transform: uppercase;">GRN Unit Cost</div>
                                    <div style="font-size: 14px; font-weight: 700; color: #a78bfa; margin-top: 3px;">
                                        Rs. {{ number_format($vGrn, 2) }}
                                    </div>
                                </div>

                                <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 8px; padding: 10px;">
                                    <div style="font-size: 10.5px; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Original Price</div>
                                    <div style="font-size: 14px; font-weight: 700; color: #cbd5e1; margin-top: 3px;">
                                        Rs. {{ number_format($vOrig, 2) }}
                                    </div>
                                </div>

                                <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 8px; padding: 10px;">
                                    <div style="font-size: 10.5px; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Discount Percentage</div>
                                    <div style="font-size: 14px; font-weight: 700; color: {{ $vDisc > 0 ? '#f59e0b' : '#64748b' }}; margin-top: 3px;">
                                        {{ $vDisc }}% OFF
                                    </div>
                                </div>

                                <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 8px; padding: 10px;">
                                    <div style="font-size: 10.5px; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Latest Selling Price</div>
                                    <div style="font-size: 14px; font-weight: 700; color: #22d3ee; margin-top: 3px;">
                                        Rs. {{ number_format($vSale, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 12px; padding-top: 8px; border-top: 1px solid rgba(255, 255, 255, 0.04); display: flex; align-items: center; justify-content: space-between; font-size: 11px; color: #64748b;">
                            <span>Net Profit Margin: <strong style="color: {{ $vMargin >= 0 ? '#34d399' : '#f87171' }};">{{ $vMargin }}%</strong></span>
                            <span>Unit Profit: <strong style="color: {{ ($vSale - $vGrn) >= 0 ? '#34d399' : '#f87171' }};">Rs. {{ number_format(max(0, $vSale - $vGrn), 2) }}</strong></span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.08);">
        <h2 style="font-size: 16px; font-weight: 700; color: #f1f5f9; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
            <svg style="width: 20px; height: 20px; color: #06b6d4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Complete Stock-Wise History & Sales Attribution
        </h2>
        
        @include('filament.pages.stock-report-modal', [
            'reportData' => app(\App\Services\StockManagementService::class)->generateReportForProduct($record),
        ])
    </div>
</x-filament-panels::page>
