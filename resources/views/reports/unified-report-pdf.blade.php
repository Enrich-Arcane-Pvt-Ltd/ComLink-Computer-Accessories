<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $data['title'] }} - ComLink</title>
    <style>
        @page { size: A4 landscape; margin: 10mm 12mm 12mm 12mm; }
        * { box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; }
        body { margin: 0; padding: 0; color: #0f172a; background: #fff; font-size: 10px; line-height: 1.4; }
        @media screen {
            body { background: #0b1120; padding: 24px; }
            .page-container { max-width: 1180px; margin: 0 auto; background: #fff; padding: 26px; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.4); }
            .screen-actions { max-width: 1180px; margin: 0 auto 14px auto; display: flex; justify-content: space-between; align-items: center; }
            .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 12px; text-decoration: none; cursor: pointer; border: none; background: #0284c7; color: #fff; }
            .btn-dark { background: #1e293b; color: #e2e8f0; border: 1px solid #334155; }
        }
        @media print {
            .screen-actions { display: none !important; }
            html, body {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .page-container {
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            table.data { width: 100% !important; }
            table.data thead { display: table-header-group; }
            table.data tr { page-break-inside: avoid; break-inside: avoid; }
        }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 14px; display: table; width: 100%; }
        .h-left { display: table-cell; vertical-align: top; width: 65%; }
        .h-right { display: table-cell; vertical-align: top; width: 35%; text-align: right; }
        .brand { font-size: 18px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; }
        .sub { font-size: 10px; color: #64748b; margin: 2px 0 6px 0; }
        .title-badge { display: inline-block; background: #0284c7; color: #fff; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .scope-box { margin-top: 6px; font-size: 9.5px; color: #334155; background: #f1f5f9; padding: 4px 8px; border-radius: 4px; display: inline-block; }
        .kpi-row { display: table; width: 100%; table-layout: fixed; margin-bottom: 14px; border-collapse: separate; border-spacing: 8px 0; margin-left: -8px; margin-right: -8px; }
        .kpi-card { display: table-cell; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; }
        .kpi-label { font-size: 8.5px; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.4px; }
        .kpi-val { font-size: 13px; font-weight: 800; color: #0f172a; margin-top: 3px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.data th { background: #0f172a; color: #fff; text-align: left; padding: 6px 7px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px; }
        table.data td { padding: 5px 7px; border-bottom: 1px solid #e2e8f0; font-size: 9.2px; color: #1e293b; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #f8fafc; }
        .footer { margin-top: 14px; padding-top: 8px; border-top: 1px solid #cbd5e1; font-size: 8.5px; color: #64748b; display: table; width: 100%; }
    </style>
</head>
<body>
@if(!empty($isPrint))
    <div class="screen-actions">
        <span style="color:#cbd5e1;font-size:12px;font-weight:600;">ComLink Report Preview — {{ $data['title'] }}</span>
        <div style="display:flex;gap:8px;">
            <button onclick="window.print()" class="btn">Print Report</button>
            <button onclick="window.close()" class="btn btn-dark">Close</button>
        </div>
    </div>
@endif

<div class="page-container">
    <div class="header">
        <div class="h-left">
            <h1 class="brand">ComLink Computer Accessories</h1>
            <p class="sub">{{ $data['subtitle'] }}</p>
            <span class="title-badge">{{ $data['title'] }}</span>
            <br>
            <div class="scope-box"><strong>Active Scope:</strong> {{ $data['filter_description'] }}</div>
        </div>
        <div class="h-right">
            <div style="font-size:9.5px;color:#475569;"><strong>Generated:</strong> {{ $generatedAt }}</div>
            <div style="font-size:9.5px;color:#475569;margin-top:2px;"><strong>Total Rows:</strong> {{ number_format(count($data['rows'])) }}</div>
        </div>
    </div>

    @if(!empty($data['summary_cards']))
        <div class="kpi-row">
            @foreach($data['summary_cards'] as $card)
                <div class="kpi-card">
                    <div class="kpi-label">{{ $card['label'] }}</div>
                    <div class="kpi-val">{{ $card['value'] }}</div>
                </div>
            @endforeach
        </div>
    @endif

    <table class="data">
        <thead>
            <tr>
                <th style="width:28px;">#</th>
                @foreach($data['columns'] as $col)
                    <th>{{ $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($data['rows'] as $idx => $row)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($data['columns']) + 1 }}" style="text-align:center;padding:18px;color:#64748b;">
                        No records match the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div style="display:table-cell;">ComLink Computer Accessories — Confidential Management Report</div>
        <div style="display:table-cell;text-align:right;">Generated via ComLink Reports Center</div>
    </div>
</div>
</body>
</html>
