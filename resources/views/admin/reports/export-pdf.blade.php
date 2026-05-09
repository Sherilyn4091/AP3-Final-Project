{{-- resources/views/admin/reports/export-pdf.blade.php --}}
@php
    $stats = $stats ?? [];
    $chartData = $chartData ?? [];
    $riskResult = $riskResult ?? [];
    $riskSummary = $riskResult['summary'] ?? [];
    $riskStudents = collect($riskResult['students'] ?? []);
    $topHighRiskStudents = collect($riskResult['top_high_risk_students'] ?? [])->take(10);

    $lowRisk = (int) ($riskSummary['low_risk'] ?? 0);
    $mediumRisk = (int) ($riskSummary['medium_risk'] ?? 0);
    $highRisk = (int) ($riskSummary['high_risk'] ?? 0);
    $analyzedStudents = (int) (($riskSummary['analyzed'] ?? ($riskSummary['total_students_analyzed'] ?? 0)) ?: ($lowRisk + $mediumRisk + $highRisk));

    $revenueLabels = collect($chartData['revenueByMethod']['labels'] ?? [])->values();
    $revenueValues = collect($chartData['revenueByMethod']['values'] ?? [])->values();
    $paymentMethodTotal = $revenueValues->sum(fn ($value) => (float) $value);

    $packageLabels = collect($chartData['packagePopularity']['labels'] ?? [])->values();
    $packageValues = collect($chartData['packagePopularity']['values'] ?? [])->values();
    $packageTotal = $packageValues->sum(fn ($value) => (int) $value);

    $atRiskByInstrument = $riskStudents
        ->filter(fn ($student) => ($student['risk_level'] ?? '') !== 'Low Risk')
        ->groupBy(fn ($student) => $student['instrument_name'] ?? 'Unassigned')
        ->map(fn ($items) => $items->count())
        ->sortDesc();

    $mostAffectedInstrument = $atRiskByInstrument->keys()->first();
    $mostAffectedInstrumentCount = $atRiskByInstrument->first();

    $display = function ($value, $fallback = 'N/A') {
        return filled($value) ? $value : $fallback;
    };
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Music Lab Combined Report</title>
    <style>
        @page { margin: 22px; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #223030; background: #ffffff; }
        h1, h2, h3, p { margin: 0; }
        .header { border-bottom: 2px solid #2F4F4F; padding-bottom: 10px; margin-bottom: 14px; }
        .title { font-size: 20px; font-weight: bold; color: #223030; }
        .subtitle { margin-top: 4px; font-size: 10px; color: #44576D; line-height: 1.5; }
        .section-title { margin-top: 16px; margin-bottom: 7px; font-size: 13px; font-weight: bold; color: #2F4F4F; }
        .muted { color: #61677A; }
        .small { font-size: 10px; }
        .kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .kpi-table td { width: 25%; border: 1px solid #D8DDD8; padding: 9px; vertical-align: top; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #768A96; letter-spacing: .04em; }
        .kpi-value { margin-top: 3px; font-size: 15px; font-weight: bold; color: #223030; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; page-break-inside: auto; }
        .data-table th, .data-table td { border: 1px solid #D8DDD8; padding: 6px 7px; vertical-align: top; }
        .data-table th { background: #F4F5F2; color: #44576D; text-align: left; font-weight: bold; }
        thead { display: table-header-group; }
        tfoot { display: table-row-group; }
        tr { page-break-inside: avoid; }
        .avoid-break { page-break-inside: avoid; }
        .total-row td { font-weight: bold; background: #FCFCFA; }
        .risk-low { color: #047857; font-weight: bold; }
        .risk-medium { color: #B45309; font-weight: bold; }
        .risk-high { color: #B91C1C; font-weight: bold; }
        .note-box { border: 1px solid #D8DDD8; background: #FCFCFA; padding: 8px; margin-bottom: 10px; font-size: 10px; color: #44576D; }
    
        /* musiclab-pdf-page-break-fixes */
        table {
            page-break-inside: auto;
        }

        thead {
            display: table-header-group;
        }

        tr,
        .summary-card,
        .report-card,
        .table-section {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Music Lab Combined Report</div>
        <div class="subtitle">
            Monthly Reports Analytics + Student Retention Risk Report<br>
            Generated: {{ $generatedAt->format('F j, Y g:i A') }}<br>
            30-Day Period: {{ $reportDateRange ?? (now()->subDays(29)->format('M d, Y') . ' - ' . now()->format('M d, Y')) }}
        </div>
    </div>

    <h2 class="section-title">Monthly Reports Analytics</h2>
    <table class="kpi-table">
        <tr>
            <td><div class="kpi-label">Total Paid Revenue</div><div class="kpi-value">PHP {{ number_format($stats['total_paid_revenue'] ?? 0, 2) }}</div></td>
            <td><div class="kpi-label">Revenue Last 30 Days</div><div class="kpi-value">PHP {{ number_format($stats['revenue_30_days'] ?? 0, 2) }}</div></td>
            <td><div class="kpi-label">Total Enrollments</div><div class="kpi-value">{{ number_format($stats['total_enrollments'] ?? 0) }}</div></td>
            <td><div class="kpi-label">Active Enrollments</div><div class="kpi-value">{{ number_format($stats['active_enrollments'] ?? 0) }}</div></td>
        </tr>
    </table>

    <div class="note-box">
        Revenue Last 30 Days uses the report period shown above. If this value is PHP 0.00, it may mean there were no paid payment records inside that date range.
    </div>

    <div class="avoid-break">
        <h3 class="section-title">Revenue by Payment Method</h3>
        <table class="data-table">
            <thead><tr><th>Payment Method</th><th>Revenue</th></tr></thead>
            <tbody>
                @forelse($revenueLabels as $index => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td>PHP {{ number_format((float) ($revenueValues[$index] ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="muted">No payment method data available.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row"><td>TOTAL</td><td>PHP {{ number_format($paymentMethodTotal, 2) }}</td></tr>
            </tfoot>
        </table>
    </div>

    <div class="avoid-break">
        <h3 class="section-title">Package Enrollments</h3>
        <table class="data-table">
            <thead><tr><th>Package Sessions</th><th>Enrollments</th></tr></thead>
            <tbody>
                @forelse($packageLabels as $index => $label)
                    <tr>
                        <td>{{ $label }} sessions</td>
                        <td>{{ number_format((int) ($packageValues[$index] ?? 0)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="muted">No package data available.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row"><td>TOTAL</td><td>{{ number_format($packageTotal) }}</td></tr>
            </tfoot>
        </table>
    </div>

    <h2 class="section-title">Student Retention Risk Report</h2>
    <div class="note-box">
        Data mining technique: Decision Tree Classification. Risk factors evaluated include attendance rate, payment status, session completion, progress rating, and lesson recency.
        @if($mostAffectedInstrument)
            Most affected instrument among at-risk students: <strong>{{ $mostAffectedInstrument }}</strong> ({{ $mostAffectedInstrumentCount }} student{{ $mostAffectedInstrumentCount == 1 ? '' : 's' }}).
        @endif
    </div>

    <table class="kpi-table">
        <tr>
            <td><div class="kpi-label">Analyzed</div><div class="kpi-value">{{ number_format($analyzedStudents) }}</div></td>
            <td><div class="kpi-label">Low Risk</div><div class="kpi-value risk-low">{{ number_format($lowRisk) }}</div></td>
            <td><div class="kpi-label">Medium Risk</div><div class="kpi-value risk-medium">{{ number_format($mediumRisk) }}</div></td>
            <td><div class="kpi-label">High Risk</div><div class="kpi-value risk-high">{{ number_format($highRisk) }}</div></td>
        </tr>
    </table>

    <h3 class="section-title">Top High-Risk Students</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Score</th>
                <th>Instrument</th>
                <th>Reason</th>
                <th>Recommended Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topHighRiskStudents as $student)
                <tr>
                    <td><strong>{{ $display($student['student_name'] ?? null) }}</strong></td>
                    <td>{{ number_format((float) ($student['risk_score'] ?? 0), 2) }}</td>
                    <td>{{ $display($student['instrument_name'] ?? null, 'Unassigned') }}</td>
                    <td>{{ $display($student['primary_reason'] ?? null, 'No primary reason provided.') }}</td>
                    <td>{{ $display($student['recommended_action'] ?? null, 'Continue monitoring.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No high-risk students detected.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>