@php
    $record = $getRecord();
    $storeProduct = $record?->storefrontProduct;
    $origPrice = $storeProduct ? (float) $storeProduct->price : 0.0;
    $salePrice = ($storeProduct && $storeProduct->sale_price !== null && (float) $storeProduct->sale_price > 0)
        ? (float) $storeProduct->sale_price
        : null;
    $latestSelling = $salePrice ?? $origPrice;
    $hasDiscount = $salePrice !== null && $origPrice > 0 && $salePrice < $origPrice;
    $discountPct = $hasDiscount ? (int) round((($origPrice - $salePrice) / $origPrice) * 100) : 0;

    $latestGrn = $record?->latest_grn_price;
    $margin = ($latestSelling > 0 && $latestGrn !== null) ? ($latestSelling - $latestGrn) : null;
@endphp

<div style="display: flex; flex-direction: column; min-width: 0; padding: 2px 0; gap: 3px;">
    @if($latestSelling > 0)
        <div style="display: flex; align-items: baseline; gap: 5px; flex-wrap: wrap;">
            <span style="font-size: 10px; font-weight: 600; text-transform: uppercase; color: #64748b;">Web:</span>
            <span style="font-size: 12px; font-weight: 800; font-family: monospace; color: #06b6d4;">
                Rs. {{ number_format((float) $latestSelling, 2) }}
            </span>
            @if($hasDiscount)
                <span style="font-size: 10px; text-decoration: line-through; color: #64748b;">
                    Rs. {{ number_format((float) $origPrice, 2) }}
                </span>
                <span style="font-size: 10px; font-weight: 700; color: #10b981; background: rgba(16,185,129,0.12); padding: 0 4px; border-radius: 3px;">
                    -{{ $discountPct }}%
                </span>
            @endif
        </div>
    @else
        <span style="font-size: 11px; color: #f59e0b; font-weight: 600;">
            Set Web Price
        </span>
    @endif

    @if($latestGrn !== null)
        <div style="display: flex; align-items: baseline; gap: 4px; font-size: 11px;">
            <span style="font-size: 10px; font-weight: 600; text-transform: uppercase; color: #64748b;">GRN:</span>
            <span style="font-family: monospace; color: #94a3b8; font-weight: 600;">
                Rs. {{ number_format((float) $latestGrn, 2) }}
            </span>
            @if($margin !== null && $margin > 0)
                <span style="font-size: 10px; font-weight: 700; color: #10b981;">
                    (+Rs. {{ number_format((float) $margin, 0) }})
                </span>
            @endif
        </div>
    @endif
</div>
