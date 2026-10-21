@php
    $product = $getRecord();
    $sold = (int) $product->sold_count;
    $variantSales = [];
    if ($product->hasColorVariants() && $sold > 0 && $product->variants->isNotEmpty()) {
        foreach ($product->variants as $variant) {
            $vSold = (int) \App\Models\OrderItem::where(function ($q) use ($product, $variant) {
                $q->where('product_id', $product->id)
                  ->where(function ($sq) use ($variant) {
                      $sq->where('variant_id', $variant->id)
                         ->orWhere('color', $variant->color_name);
                  });
            })
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'Paid'))
            ->sum('quantity');

            if ($vSold > 0) {
                $variantSales[$variant->color_name] = $vSold;
            }
        }
    }
@endphp

<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2px 0;">
    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 700; {{ $sold > 0 ? 'background-color: rgba(99, 102, 241, 0.15); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.3);' : 'background-color: rgba(113, 113, 122, 0.15); color: #a1a1aa; border: 1px solid rgba(113, 113, 122, 0.25);' }}">
        {{ number_format($sold) }} {{ $sold === 1 ? 'unit' : 'units' }}
    </span>
    @if(!empty($variantSales))
        <div style="display: flex; align-items: center; justify-content: center; gap: 4px; margin-top: 4px; flex-wrap: wrap;">
            @foreach($variantSales as $cName => $cSold)
                <span style="font-size: 10px; font-weight: 600; padding: 1px 4px; border-radius: 4px; background: rgba(129, 140, 248, 0.1); color: #818cf8; border: 1px solid rgba(129, 140, 248, 0.25);">
                    {{ $cName }}: {{ $cSold }}
                </span>
            @endforeach
        </div>
    @endif
</div>
