<x-filament-panels::page>
    {{ $this->form }}

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
