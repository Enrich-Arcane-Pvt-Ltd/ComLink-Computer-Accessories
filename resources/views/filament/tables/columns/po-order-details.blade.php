<div class="space-y-1.5 py-1 max-w-md">
    @if($getRecord() && $getRecord()->items)
        @foreach($getRecord()->items as $item)
            <div class="border-b border-gray-700/50 last:border-b-0 pb-1.5 last:pb-0">
                <div class="font-semibold text-gray-100 text-xs sm:text-sm">
                    {{ $item->product_name }} 
                    <span class="text-cyan-400 font-bold ml-1 font-mono">× {{ $item->product_quantity }}</span>
                </div>
                <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-400">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider {{ $item->product_condition === 'Brandnew' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-amber-950 text-amber-400 border border-amber-800' }}">
                        {{ $item->product_condition }}
                    </span>
                    <span class="font-mono text-[11px] text-gray-400">{{ $item->product_code }}</span>
                </div>
            </div>
        @endforeach
    @endif
</div>
