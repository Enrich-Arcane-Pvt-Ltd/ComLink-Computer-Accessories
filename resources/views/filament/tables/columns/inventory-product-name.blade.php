<div class="py-1 min-w-[180px]">
    @if($getRecord())
        <div class="font-bold text-gray-100 text-sm leading-tight">
            {{ $getRecord()->product_name }}
        </div>
        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $getRecord()->product_condition === 'Brandnew' ? 'bg-emerald-950/90 text-emerald-400 border border-emerald-800/60' : 'bg-amber-950/90 text-amber-400 border border-amber-800/60' }}">
                {{ $getRecord()->product_condition }}
            </span>
            <span class="font-mono text-[11px] text-cyan-400 bg-cyan-950/60 px-1.5 py-0.5 rounded border border-cyan-800/50">
                Code: {{ $getRecord()->product_code }}
            </span>
        </div>
    @endif
</div>
