<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
        .muted { color: #555; font-size: 11px; margin-bottom: 12px; }

        .grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .grid td { border: 1px solid #ddd; padding: 10px; vertical-align: top; }
        .kpi-label { color: #555; font-size: 11px; }
        .kpi-value { font-size: 16px; font-weight: bold; margin-top: 2px; }

        h3 { font-size: 13px; margin: 14px 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #f3f3f3; text-align: left; }
    </style>
</head>
<body>

    <div class="title">Financial Report (Analytics)</div>
    <div class="muted">
        Generated: {{ $generatedAt->format('F j, Y g:i A') }}
    </div>

    {{-- KPI Summary --}}
    <table class="grid">
        <tr>
            <td>
                <div class="kpi-label">Total Paid Revenue</div>
                <div class="kpi-value">₱{{ number_format($stats['total_paid_revenue'] ?? 0, 2) }}</div>
            </td>
            <td>
                <div class="kpi-label">Revenue (Last 30 Days)</div>
                <div class="kpi-value">₱{{ number_format($stats['revenue_30_days'] ?? 0, 2) }}</div>
            </td>
            <td>
                <div class="kpi-label">Enrollments (All / Active)</div>
                <div class="kpi-value">{{ $stats['total_enrollments'] ?? 0 }} / {{ $stats['active_enrollments'] ?? 0 }}</div>
            </td>
            <td>
                <div class="kpi-label">Inventory Value</div>
                <div class="kpi-value">₱{{ number_format($stats['total_inventory_value'] ?? 0, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- Tables instead of charts (PDF-friendly) --}}
    <h3>Revenue Trend (Last 30 Days)</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($chartData['revenueTrend']['labels'] ?? []) as $i => $date)
                <tr>
                    <td>{{ $date }}</td>
                    <td>₱{{ number_format((float)($chartData['revenueTrend']['values'][$i] ?? 0), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Revenue by Payment Method</h3>
    <table>
        <thead>
            <tr>
                <th>Method</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($chartData['revenueByMethod']['labels'] ?? []) as $i => $method)
                <tr>
                    <td>{{ $method }}</td>
                    <td>₱{{ number_format((float)($chartData['revenueByMethod']['values'][$i] ?? 0), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Enrollment Trend (Last 30 Days)</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Enrollments</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($chartData['enrollmentTrend']['labels'] ?? []) as $i => $date)
                <tr>
                    <td>{{ $date }}</td>
                    <td>{{ (int)($chartData['enrollmentTrend']['values'][$i] ?? 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Enrollment Package Popularity</h3>
    <table>
        <thead>
            <tr>
                <th>Package (Sessions)</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($chartData['packagePopularity']['labels'] ?? []) as $i => $pkg)
                <tr>
                    <td>{{ $pkg }}</td>
                    <td>{{ (int)($chartData['packagePopularity']['values'][$i] ?? 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
