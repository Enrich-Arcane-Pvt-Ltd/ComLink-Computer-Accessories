<div class="flex flex-col py-1">
    @php
        $currency = config('app.currency_symbol', 'Rs. ');
        $orig = (float) $getRecord()->price;
        $sale = $getRecord()->sale_price !== null ? (float) $getRecord()->sale_price : null;
        $hasDiscount = $sale !== null && $sale < $orig;
        $discountPct = $hasDiscount && $orig > 0 ? round((($orig - $sale) / $orig) * 100) : 0;
        $displayLatest = $sale !== null ? $sale : $orig;
    @endphp
    <span class="font-bold text-sm text-gray-900 dark:text-gray-100">
        {{ $currency }}{{ number_format($displayLatest, 2) }}
    </span>
    @if($hasDiscount)
        <div class="flex items-center gap-1.5 text-xs mt-0.5">
            <span class="line-through text-gray-400 dark:text-gray-500 text-[11px]">
                {{ $currency }}{{ number_format($orig, 2) }}
            </span>
            <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950 px-1 rounded">
                -{{ $discountPct }}%
            </span>
        </div>
    @endif
</div>
