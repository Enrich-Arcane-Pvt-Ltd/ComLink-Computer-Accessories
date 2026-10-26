<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Receipt #{{ $order->order_number }} - ComLink Computer Accessories</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 15mm 15mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        body {
            background-color: #ffffff;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.45;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0891b2;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .brand-name {
            font-size: 22px;
            font-weight: 800;
            color: #131a26;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .brand-subtitle {
            font-size: 11px;
            font-weight: 700;
            color: #0891b2;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .store-contact {
            font-size: 9.5px;
            color: #64748b;
            margin-top: 4px;
            line-height: 1.4;
        }
        .receipt-title {
            text-align: right;
            vertical-align: top;
        }
        .receipt-title h1 {
            font-size: 20px;
            font-weight: 800;
            color: #0891b2;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .receipt-badge {
            display: inline-block;
            background-color: #ecfeff;
            color: #0891b2;
            border: 1px solid #a5f3fc;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9.5px;
            font-weight: 700;
            margin-top: 4px;
            text-transform: uppercase;
        }
        .order-meta-box {
            margin-top: 6px;
            font-size: 10px;
            color: #475569;
            line-height: 1.4;
        }

        /* 2-Column Info Table (Billing & Shipping) */
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-cell {
            width: 50%;
            vertical-align: top;
            padding: 10px 12px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        .info-title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0891b2;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 3px;
        }
        .info-line {
            font-size: 10px;
            color: #1e293b;
            margin-bottom: 2px;
        }
        .info-line strong {
            color: #0f172a;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #131a26;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            text-align: left;
        }
        .items-table th.text-center { text-align: center; }
        .items-table th.text-right { text-align: right; }
        .items-table td {
            padding: 10px;
            font-size: 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .items-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .item-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 10.5px;
        }
        .item-category {
            font-size: 9px;
            color: #64748b;
            margin-top: 1px;
        }

        /* Totals Area */
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .totals-left {
            width: 55%;
            vertical-align: top;
            padding-right: 20px;
        }
        .totals-right {
            width: 45%;
            vertical-align: top;
        }
        .calc-table {
            width: 100%;
            border-collapse: collapse;
        }
        .calc-table td {
            padding: 5px 8px;
            font-size: 10.5px;
        }
        .calc-label {
            color: #64748b;
            text-align: left;
        }
        .calc-val {
            text-align: right;
            font-weight: 600;
            color: #1e293b;
        }
        .calc-grand-total td {
            border-top: 2px solid #0891b2;
            padding-top: 8px;
            font-size: 13px;
            font-weight: 800;
            color: #0891b2;
        }

        .status-box {
            padding: 10px 12px;
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            font-size: 10px;
            color: #166534;
            line-height: 1.45;
        }

        /* Footer */
        .footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 14px;
            text-align: center;
            font-size: 9px;
            color: #64748b;
            line-height: 1.5;
        }
        .footer-highlight {
            font-weight: 700;
            color: #131a26;
            margin-bottom: 2px;
        }
    </style>
</head>
<body>

    <!-- Header Table -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align: top; width: 60%;">
                <div class="brand-name">ComLink Computer Accessories</div>
                <div class="brand-subtitle">Premium Hardware, Systems & IT Accessories</div>
                <div class="store-contact">
                    Hotline: +94 77 123 4567 | Support: info@comlink.lk<br/>
                    Website: www.comlink.lk | Sri Lanka
                </div>
            </td>
            <td class="receipt-title">
                <h1>Official Receipt</h1>
                <div class="receipt-badge">Order #{{ $order->order_number }}</div>
                <div class="order-meta-box">
                    <strong>Date:</strong> {{ $order->created_at ? $order->created_at->format('M d, Y h:i A') : 'N/A' }}<br/>
                    <strong>Order Status:</strong> {{ strtoupper($order->status ?? 'PROCESSING') }}<br/>
                    <strong>Payment Status:</strong> {{ strtoupper($order->payment_status ?? 'PAID') }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Billing & Shipping Information -->
    <table class="info-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="info-cell" style="margin-right: 8px;">
                <div class="info-title">Customer & Delivery Information</div>
                <div class="info-line"><strong>Recipient:</strong> {{ $order->customer_name }}</div>
                <div class="info-line"><strong>Address:</strong> {{ $order->shipping_address_line_1 }}{{ $order->shipping_address_line_2 ? ', ' . $order->shipping_address_line_2 : '' }}</div>
                <div class="info-line"><strong>City / Region:</strong> {{ $order->shipping_city }}{{ $order->shipping_postal_code ? ' (' . $order->shipping_postal_code . ')' : '' }}</div>
                <div class="info-line"><strong>Contact Phone:</strong> {{ $order->customer_phone }}{{ $order->customer_secondary_phone ? ' / ' . $order->customer_secondary_phone : '' }}</div>
                <div class="info-line"><strong>Email:</strong> {{ $order->customer_email }}</div>
            </td>
            <td style="width: 2%;"></td>
            <td class="info-cell">
                <div class="info-title">Courier & Dispatch Summary</div>
                <div class="info-line"><strong>Courier Partner:</strong> {{ $order->courier_name ?: 'Pronto Courier Service' }}</div>
                <div class="info-line"><strong>Tracking Code:</strong> {{ $order->tracking_number ?: ('PRN-LK-' . $order->id) }}</div>
                <div class="info-line"><strong>Delivery Estimate:</strong> 2 - 4 Business Days</div>
                <div class="info-line"><strong>Handling:</strong> Secure Hardware Fragile Packaging</div>
            </td>
        </tr>
    </table>

    <!-- Purchased Products Items Table -->
    <table class="items-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 55%;">Product Description</th>
                <th style="width: 15%;" class="text-right">Unit Price</th>
                <th style="width: 10%;" class="text-center">Qty</th>
                <th style="width: 15%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
                <tr>
                    <td class="text-center" style="color: #64748b;">{{ $index + 1 }}</td>
                    <td>
                        <div class="item-name">{{ $item->product_name }}</div>
                        @if($item->category_name)
                            <div class="item-category">Category: {{ $item->category_name }}</div>
                        @endif
                    </td>
                    <td class="text-right">{{ $currency }}{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="text-center" style="font-weight: 700;">{{ $item->quantity }}</td>
                    <td class="text-right" style="font-weight: 700; color: #0891b2;">
                        {{ $currency }}{{ number_format((float) $item->total_price, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals Table -->
    <table class="totals-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="totals-left">
                <div class="status-box">
                    <strong style="color: #15803d;">Payment & Warranty Notice:</strong><br/>
                    All items are brand new, genuine products covered by standard manufacturer warranty.
                    Please retain this printed or digital receipt alongside original product packaging for any warranty claims.
                </div>
            </td>
            <td class="totals-right">
                <table class="calc-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="calc-label">Items Subtotal:</td>
                        <td class="calc-val">{{ $currency }}{{ number_format((float) $order->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="calc-label">Shipping & Handling:</td>
                        <td class="calc-val" style="color: #16a34a;">
                            @if((float) $order->shipping_fee > 0)
                                {{ $currency }}{{ number_format((float) $order->shipping_fee, 2) }}
                            @else
                                Islandwide Free
                            @endif
                        </td>
                    </tr>
                    @if((float) $order->discount_amount > 0)
                        <tr>
                            <td class="calc-label">Discount:</td>
                            <td class="calc-val" style="color: #dc2626;">- {{ $currency }}{{ number_format((float) $order->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="calc-grand-total">
                        <td>Total Amount:</td>
                        <td style="text-align: right;">{{ $currency }}{{ number_format((float) $order->total_amount, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-highlight">Thank you for your purchase with ComLink Computer Accessories!</div>
        This is an official computer-generated receipt for Order #{{ $order->order_number }}. No physical signature is required.<br/>
        For questions or after-sales assistance, reach us at support@comlink.lk or call +94 77 123 4567.
    </div>

</body>
</html>
