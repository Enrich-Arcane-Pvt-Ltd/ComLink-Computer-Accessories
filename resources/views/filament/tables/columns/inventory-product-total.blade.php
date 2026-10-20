<div class="py-1">
    @if($getRecord())
        <div class="font-bold font-mono text-cyan-400 text-sm">
            Rs. {{ number_format((float) $getRecord()->total, 2) }}
        </div>
        <div class="text-[11px] text-gray-400 font-mono mt-0.5">
            Rs. {{ number_format((float) $getRecord()->price_per_unit, 2) }} × {{ $getRecord()->product_quantity }} {{ $getRecord()->product_quantity === 1 ? 'unit' : 'units' }}
        </div>
    @endif
</div>
