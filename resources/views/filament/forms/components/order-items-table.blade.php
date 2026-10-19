<div class="rounded-xl border border-gray-700 bg-gray-900/60 overflow-hidden">
    @php
        $record = $getRecord();
        $items = $record ? $record->items : collect();
        $totalAmount = $record ? (float) $record->total_amount : 0;
    @endphp

    <table class="w-full text-left text-sm text-gray-200">
        <thead class="bg-gray-800/80 text-xs uppercase tracking-wider text-gray-400 border-b border-gray-700">
            <tr>
                <th scope="col" class="px-4 py-3 w-16 text-center font-bold">No</th>
                <th scope="col" class="px-4 py-3 font-bold">Product Name</th>
                <th scope="col" class="px-4 py-3 text-right font-bold w-44">Amount</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            @forelse($items as $index => $item)
                <tr class="hover:bg-gray-800/40 transition-colors">
                    <td class="px-4 py-3 text-center text-xs text-gray-400 font-mono font-medium">
                        {{ $index + 1 }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if(!empty($item->product_image))
                                <img 
                                    src="{{ (str_starts_with($item->product_image, 'http') || str_starts_with($item->product_image, '/')) ? $item->product_image : asset('storage/' . $item->product_image) }}" 
                                    alt="{{ $item->product_name }}"
                                    class="w-10 h-10 rounded-lg object-cover bg-gray-800 border border-gray-700 shrink-0"
                                    onerror="this.src='/images/placeholder.png'"
                                />
                            @endif
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-100 text-xs sm:text-sm">
                                    {{ $item->product_name }}
                                </div>
                                <div class="text-[11px] text-cyan-400 font-medium mt-0.5">
                                    Rs. {{ number_format((float) $item->unit_price, 2) }} × {{ $item->quantity }} {{ $item->quantity === 1 ? 'unit' : 'units' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-xs sm:text-sm text-gray-100 font-mono">
                        Rs. {{ number_format((float) $item->total_price, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-6 text-center text-xs text-gray-400">
                        No products added to this order yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot class="bg-gray-800/90 border-t-2 border-gray-700 font-bold">
            <tr>
                <td colspan="2" class="px-4 py-3.5 text-right text-xs uppercase tracking-wider text-gray-300">
                    Total Amount of Full Order
                </td>
                <td class="px-4 py-3.5 text-right text-sm text-cyan-400 font-mono font-black">
                    Rs. {{ number_format($totalAmount, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
