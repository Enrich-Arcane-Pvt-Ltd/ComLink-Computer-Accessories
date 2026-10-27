<div class="crm-wrapper">
    <style>
        .crm-wrapper {
            --crm-bg-base: #ffffff;
            --crm-bg-card: #f8fafc;
            --crm-border: #e2e8f0;
            --crm-border-subtle: #edf2f7;
            --crm-text-primary: #0f172a;
            --crm-text-secondary: #334155;
            --crm-text-muted: #64748b;
            --crm-text-faint: #94a3b8;
            --crm-accent: #0284c7;
            --crm-accent-soft: rgba(2, 132, 199, 0.1);
            --crm-success: #16a34a;
            --crm-success-soft: rgba(22, 163, 74, 0.1);
            --crm-th-bg: #f1f5f9;
            --crm-tr-hover: #f8fafc;
            
            font-family: inherit;
            color: var(--crm-text-primary);
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            width: 100%;
            box-sizing: border-box;
        }

        .dark .crm-wrapper,
        :is(.dark) .crm-wrapper {
            --crm-bg-base: #111622;
            --crm-bg-card: #182030;
            --crm-border: #2a3547;
            --crm-border-subtle: #212a39;
            --crm-text-primary: #f8fafc;
            --crm-text-secondary: #cbd5e1;
            --crm-text-muted: #94a3b8;
            --crm-text-faint: #64748b;
            --crm-accent: #38bdf8;
            --crm-accent-soft: rgba(56, 189, 248, 0.12);
            --crm-success: #34d399;
            --crm-success-soft: rgba(52, 211, 153, 0.12);
            --crm-th-bg: #1e2738;
            --crm-tr-hover: #1f293d;
        }

        .crm-wrapper * {
            box-sizing: border-box;
        }

        /* Top Header */
        .crm-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--crm-border);
        }

        .crm-brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .crm-logo {
            height: 44px;
            width: auto;
            object-fit: contain;
            border-radius: 6px;
        }

        .crm-company-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--crm-text-primary);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin: 0;
            line-height: 1.2;
        }

        .crm-company-sub {
            font-size: 0.75rem;
            color: var(--crm-text-muted);
            margin: 0.25rem 0 0 0;
        }

        .crm-header-meta {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.25rem;
        }

        .crm-paid-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--crm-success-soft);
            color: var(--crm-success);
            border: 1px solid rgba(52, 211, 153, 0.3);
        }

        .crm-order-num {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--crm-text-primary);
            margin: 0.15rem 0 0 0;
        }

        .crm-order-date {
            font-size: 0.72rem;
            color: var(--crm-text-muted);
            margin: 0;
        }

        /* 2-Col Cards Grid */
        .crm-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
        }

        .crm-card {
            background-color: var(--crm-bg-card) !important;
            border: 1px solid var(--crm-border);
            border-radius: 12px;
            padding: 1rem 1.15rem;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .crm-card-label {
            font-size: 0.68rem;
            font-weight: 800;
            color: var(--crm-accent);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.2rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .crm-card-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--crm-text-primary);
            margin: 0;
        }

        .crm-card-line {
            font-size: 0.78rem;
            color: var(--crm-text-secondary);
            margin: 0;
            display: flex;
            align-items: flex-start;
            gap: 0.4rem;
            line-height: 1.4;
        }

        .crm-card-line-muted {
            font-size: 0.78rem;
            color: var(--crm-text-muted);
            margin: 0;
            display: flex;
            align-items: flex-start;
            gap: 0.4rem;
            line-height: 1.4;
        }

        .crm-tracking-pill {
            display: inline-block;
            font-family: monospace;
            font-weight: 600;
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
            background: var(--crm-accent-soft);
            color: var(--crm-accent);
            border: 1px solid rgba(56, 189, 248, 0.25);
            font-size: 0.75rem;
        }

        .crm-status-pill {
            font-weight: 700;
            color: var(--crm-accent);
        }

        /* Products Table */
        .crm-table-container {
            border: 1px solid var(--crm-border);
            border-radius: 12px;
            overflow: hidden;
            background-color: var(--crm-bg-base);
        }

        .crm-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.8rem;
        }

        .crm-table th {
            background-color: var(--crm-th-bg) !important;
            color: var(--crm-text-muted) !important;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--crm-border);
        }

        .crm-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--crm-border-subtle);
            color: var(--crm-text-secondary);
            vertical-align: middle;
        }

        .crm-table tbody tr:last-child td {
            border-bottom: none;
        }

        .crm-table tbody tr:hover {
            background-color: var(--crm-tr-hover);
        }

        .crm-prod-name {
            font-weight: 700;
            color: var(--crm-text-primary);
            font-size: 0.83rem;
            display: block;
        }

        .crm-prod-cat {
            font-size: 0.7rem;
            color: var(--crm-text-muted);
            margin-top: 0.15rem;
            display: block;
        }

        .crm-qty-badge {
            display: inline-block;
            min-width: 26px;
            text-align: center;
            padding: 0.15rem 0.4rem;
            border-radius: 6px;
            background: var(--crm-accent-soft);
            color: var(--crm-accent);
            font-weight: 700;
            font-size: 0.75rem;
        }

        .crm-amount {
            font-weight: 700;
            color: var(--crm-accent);
            font-size: 0.85rem;
        }

        /* Totals Block */
        .crm-totals-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 0.25rem;
        }

        .crm-totals-card {
            background-color: var(--crm-bg-card) !important;
            border: 1px solid var(--crm-border);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            width: 100%;
            max-width: 320px;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .crm-totals-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: var(--crm-text-muted);
        }

        .crm-totals-val {
            font-weight: 600;
            color: var(--crm-text-secondary);
        }

        .crm-free-badge {
            color: var(--crm-success);
            font-weight: 700;
            background: var(--crm-success-soft);
            padding: 0.1rem 0.45rem;
            border-radius: 4px;
            font-size: 0.72rem;
        }

        .crm-totals-divider {
            height: 1px;
            background-color: var(--crm-border);
            margin: 0.35rem 0;
        }

        .crm-total-final {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.95rem;
            font-weight: 800;
        }

        .crm-total-final-label {
            color: var(--crm-text-primary);
        }

        .crm-total-final-val {
            color: var(--crm-accent);
            font-size: 1.05rem;
            font-weight: 800;
        }

        /* Bottom Action Bar */
        .crm-footer-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding-top: 1rem;
            border-top: 1px solid var(--crm-border);
        }

        .crm-receipt-notice {
            font-size: 0.73rem;
            color: var(--crm-text-muted);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .crm-download-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.6rem 1.15rem;
            border-radius: 8px;
            background: #0284c7;
            color: #ffffff !important;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-decoration: none !important;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.3);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .crm-download-btn:hover {
            background: #0369a1;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4);
            color: #ffffff !important;
        }

        .crm-download-btn svg {
            width: 15px;
            height: 15px;
        }
    </style>

    <!-- 1. Header Section -->
    <div class="crm-header">
        <div class="crm-brand">
            <img src="{{ asset('images/Comlink_logo.png') }}" alt="ComLink" class="crm-logo">
            <div>
                <h3 class="crm-company-title">ComLink Computer Accessories</h3>
                <p class="crm-company-sub">Hotline: +94 77 123 4567 | support@comlink.lk</p>
            </div>
        </div>

        <div class="crm-header-meta">
            <span class="crm-paid-badge">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Payment: {{ $record->payment_status ?: 'Paid' }}
            </span>
            <p class="crm-order-num">Order #{{ $record->order_number }}</p>
            <p class="crm-order-date">{{ $record->created_at ? $record->created_at->format('M d, Y · h:i A') : 'N/A' }}</p>
        </div>
    </div>

    <!-- 2. Customer & Fulfillment Grid -->
    <div class="crm-grid">
        <!-- Customer Details -->
        <div class="crm-card">
            <div class="crm-card-label">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Customer Details</span>
            </div>
            <p class="crm-card-name">{{ $record->customer_name ?: ($record->user?->name ?? 'Guest Customer') }}</p>
            <p class="crm-card-line">
                <span>📞</span>
                <span>{{ $record->customer_phone ?: ($record->customer_secondary_phone ?: 'No phone provided') }}</span>
            </p>
            @if($record->customer_email)
                <p class="crm-card-line-muted">
                    <span>✉️</span>
                    <span>{{ $record->customer_email }}</span>
                </p>
            @endif
            <p class="crm-card-line-muted">
                <span>📍</span>
                <span>
                    {{ $record->shipping_address_line_1 ?: 'N/A' }}{{ $record->shipping_address_line_2 ? ', ' . $record->shipping_address_line_2 : '' }},
                    {{ $record->shipping_city ?: '' }}
                    @if($record->shipping_postal_code)
                        ({{ $record->shipping_postal_code }})
                    @endif
                </span>
            </p>
        </div>

        <!-- Fulfillment & Courier -->
        <div class="crm-card">
            <div class="crm-card-label">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                <span>Fulfillment & Courier</span>
            </div>
            <p class="crm-card-name">Courier: {{ $record->courier_name ?: 'Pronto Courier Service' }}</p>
            <p class="crm-card-line">
                <span>Tracking:</span>
                <span class="crm-tracking-pill">{{ $record->tracking_number ?: ('PRN-LK-' . $record->id) }}</span>
            </p>
            <p class="crm-card-line">
                <span>Status:</span>
                <span class="crm-status-pill">{{ $record->status ?: 'Processing' }}</span>
            </p>
            <p class="crm-card-line-muted">
                <span>Payment:</span>
                <span>{{ $record->payment_method ?: 'Stripe Card Payment' }}</span>
            </p>
        </div>
    </div>

    <!-- 3. Items Table -->
    <div class="crm-table-container">
        <table class="crm-table">
            <thead>
                <tr>
                    <th style="width: 48px; text-align: center;">#</th>
                    <th>Product Description</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: center; width: 70px;">Qty</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($record->items as $i => $item)
                    <tr>
                        <td style="text-align: center; color: var(--crm-text-muted);">{{ $i + 1 }}</td>
                        <td>
                            <span class="crm-prod-name">{{ $item->product_name }}</span>
                            @if($item->category_name)
                                <span class="crm-prod-cat">{{ $item->category_name }}</span>
                            @endif
                        </td>
                        <td style="text-align: right; font-variant-numeric: tabular-nums;">
                            Rs. {{ number_format((float) $item->unit_price, 2) }}
                        </td>
                        <td style="text-align: center;">
                            <span class="crm-qty-badge">{{ $item->quantity }}</span>
                        </td>
                        <td style="text-align: right; font-variant-numeric: tabular-nums;">
                            <span class="crm-amount">Rs. {{ number_format((float) $item->total_price, 2) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 1.5rem; color: var(--crm-text-muted);">
                            No products found in this order record.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- 4. Calculation Totals -->
    <div class="crm-totals-container">
        <div class="crm-totals-card">
            <div class="crm-totals-row">
                <span>Subtotal:</span>
                <span class="crm-totals-val">Rs. {{ number_format((float) $record->subtotal, 2) }}</span>
            </div>
            <div class="crm-totals-row">
                <span>Shipping:</span>
                <span>
                    @if((float) $record->shipping_fee > 0)
                        <span class="crm-totals-val">Rs. {{ number_format((float) $record->shipping_fee, 2) }}</span>
                    @else
                        <span class="crm-free-badge">Islandwide Free</span>
                    @endif
                </span>
            </div>
            @if((float) $record->discount_amount > 0)
                <div class="crm-totals-row" style="color: #ef4444;">
                    <span>Discount:</span>
                    <span style="font-weight: 700;">- Rs. {{ number_format((float) $record->discount_amount, 2) }}</span>
                </div>
            @endif
            <div class="crm-totals-divider"></div>
            <div class="crm-total-final">
                <span class="crm-total-final-label">Total Amount:</span>
                <span class="crm-total-final-val">Rs. {{ number_format((float) $record->total_amount, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- 5. Download Action Bar -->
    <div class="crm-footer-bar">
        <p class="crm-receipt-notice">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span>Official computer-generated receipt for Order #{{ $record->order_number }}.</span>
        </p>

        <a
            href="{{ route('orders.receipt', ['order' => $record->id]) }}"
            target="_blank"
            rel="noopener noreferrer"
            class="crm-download-btn"
        >
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            <span>Download PDF</span>
        </a>
    </div>
</div>
