@php
    $record = $getRecord();
    $report = app(\App\Services\StockManagementService::class)->generateReportForProduct($record);
    $activeBatch = $report['summary']['active_batch_code'] ?? null;
    $totalBatches = $report['summary']['total_batches_count'] ?? 0;
    $endedBatches = $report['summary']['ended_batches_count'] ?? 0;
@endphp

<div style="display: flex; flex-direction: column; gap: 3px; padding: 2px 0;">
    <div style="display: flex; align-items: center; gap: 6px;">
        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 7px; border-radius: 6px; font-size: 11px; font-weight: 700; background-color: rgba(99, 102, 241, 0.12); color: #6366f1; border: 1px solid rgba(99, 102, 241, 0.28);">
            {{ $totalBatches }} {{ $totalBatches === 1 ? 'Batch' : 'Batches' }}
        </span>
        @if($endedBatches > 0)
            <span style="font-size: 10px; color: #ef4444; font-weight: 600;" title="{{ $endedBatches }} batches ended">
                ({{ $endedBatches }} ended)
            </span>
        @endif
    </div>

    @php
        $activeCodes = $report['summary']['active_batch_codes'] ?? [];
        $activeCount = count($activeCodes);
    @endphp

    @if($activeCount > 0)
        <div style="display: flex; align-items: center; gap: 4px; font-size: 11px; margin-top: 2px;">
            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background-color: #10b981; box-shadow: 0 0 4px #10b981;"></span>
            <span style="font-size: 10px; font-weight: 600; color: #64748b;">Active:</span>
            @if($activeCount === 1)
                <span style="font-family: monospace; font-size: 11px; font-weight: 700; color: #10b981;">
                    {{ $activeCodes[0] }}
                </span>
            @else
                <span style="font-size: 11px; font-weight: 700; color: #10b981;" title="{{ implode(', ', $activeCodes) }}">
                    {{ $activeCount }} Colors Active
                </span>
            @endif
        </div>
    @else
        <div style="font-size: 10px; color: #94a3b8; font-style: italic; margin-top: 2px;">
            No active stock
        </div>
    @endif
</div>
