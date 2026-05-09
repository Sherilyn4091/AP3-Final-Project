<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Risk Analytics Report</title>

    <style>
        @page {
            margin: 24px;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #272829;
            background: #ffffff;
        }

        h1,
        h2,
        h3,
        p {
            margin: 0;
        }

        .report-header {
            width: 100%;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 2px solid #2F4F4F;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-logo-cell {
            width: 120px;
            vertical-align: middle;
            text-align: left;
        }

        .header-logo {
            width: 96px;
            height: auto;
            display: block;
        }

        .logo-fallback {
            width: 96px;
            padding: 8px 6px;
            border: 1px solid #D8D9DA;
            font-size: 10px;
            font-weight: bold;
            color: #2F4F4F;
            text-align: center;
        }

        .header-title-cell {
            vertical-align: middle;
        }

        .report-title {
            font-size: 19px;
            font-weight: bold;
            color: #2F4F4F;
            line-height: 1.25;
        }

        .report-subtitle {
            margin-top: 4px;
            font-size: 10px;
            color: #61677A;
            line-height: 1.45;
        }

        .generated-box {
            margin-top: 10px;
            padding: 8px 10px;
            border: 1px solid #D8D9DA;
            background: #fcf3e3;
            color: #394a56;
            font-size: 10px;
        }

        .section-title {
            margin-top: 18px;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: bold;
            color: #2F4F4F;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .summary-table td {
            width: 25%;
            padding: 10px;
            border: 1px solid #D8D9DA;
            vertical-align: top;
        }

        .summary-label {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #61677A;
        }

        .summary-value {
            margin-top: 5px;
            font-size: 17px;
            font-weight: bold;
            color: #272829;
        }

        .low {
            color: #2F4F4F;
            font-weight: bold;
        }

        .medium {
            color: #B4833D;
            font-weight: bold;
        }

        .high {
            color: #9f1d20;
            font-weight: bold;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .data-table th {
            padding: 7px;
            border: 1px solid #D8D9DA;
            background: #fcf3e3;
            color: #394a56;
            font-size: 8.5px;
            text-transform: uppercase;
            text-align: left;
            vertical-align: top;
        }

        .data-table td {
            padding: 7px;
            border: 1px solid #D8D9DA;
            vertical-align: top;
            line-height: 1.35;
        }

        .mono {
            font-family: DejaVu Sans Mono, monospace;
        }

        .muted {
            color: #61677A;
        }

        .small {
            font-size: 9px;
        }

        .page-break {
            page-break-before: always;
        }

        .footer-note {
            margin-top: 18px;
            padding-top: 8px;
            border-top: 1px solid #D8D9DA;
            font-size: 9px;
            color: #61677A;
            text-align: center;
        }
    </style>
</head>

<body>
    @php
        /*
        |--------------------------------------------------------------------------
        | Local Logo
        |--------------------------------------------------------------------------
        |
        | Place the logo here:
        | public/images/logo.png
        |
        | The image is converted to base64 so DomPDF does not need Cloudinary
        | or remote image access.
        |
        */

        $logoPath = public_path('images/logo.png');

        $logoSrc = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $summary = $result['summary'] ?? [];

        $students = collect($result['students'] ?? [])
            ->sortByDesc(function ($student) {
                return (float) ($student['risk_score'] ?? 0);
            })
            ->values();

        $topStudents = collect($result['top_high_risk_students'] ?? [])
            ->sortByDesc(function ($student) {
                return (float) ($student['risk_score'] ?? 0);
            })
            ->values();

        $factors = collect($result['factor_summary'] ?? [])
            ->sortByDesc(function ($factor) {
                return (int) ($factor['count'] ?? 0);
            })
            ->values();

        $instructors = collect($result['instructor_summary'] ?? [])
            ->sortByDesc(function ($instructor) {
                return (int) ($instructor['high_risk'] ?? 0);
            })
            ->values();

        $riskClass = function ($level) {
            return match ($level) {
                'High Risk' => 'high',
                'Medium Risk' => 'medium',
                default => 'low',
            };
        };

        $displayValue = function ($value, $fallback = 'N/A') {
            return ($value === null || $value === '') ? $fallback : $value;
        };

        $displayProgress = function ($value) {
            if ($value === null || $value === '') {
                return 'N/A';
            }

            return $value . '/10';
        };

        $displayPercent = function ($value) {
            if ($value === null || $value === '') {
                return 'N/A';
            }

            return $value . '%';
        };
    @endphp

    <div class="report-header">
        <table class="header-table">
            <tr>
                <td class="header-logo-cell">
                    @if ($logoSrc)
                        <img src="{{ $logoSrc }}" alt="Music Lab Logo" class="header-logo">
                    @else
                        <div class="logo-fallback">Music Lab</div>
                    @endif
                </td>

                <td class="header-title-cell">
                    <h1 class="report-title">Student Risk Analytics Report</h1>
                    <p class="report-subtitle">
                        Music Lab Data Mining Report<br>
                        Decision Tree Classification for student retention monitoring
                    </p>
                </td>
            </tr>
        </table>

        <div class="generated-box">
            <strong>Generated:</strong> {{ $generatedAt ?? now()->format('F d, Y h:i A') }}<br>
            <strong>Purpose:</strong> Identify students who may need follow-up, intervention, or manual review.
        </div>
    </div>

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">Analyzed Students</div>
                <div class="summary-value mono">{{ $summary['total_students_analyzed'] ?? 0 }}</div>
            </td>

            <td>
                <div class="summary-label">Low Risk</div>
                <div class="summary-value mono low">{{ $summary['low_risk'] ?? 0 }}</div>
            </td>

            <td>
                <div class="summary-label">Medium Risk</div>
                <div class="summary-value mono medium">{{ $summary['medium_risk'] ?? 0 }}</div>
            </td>

            <td>
                <div class="summary-label">High Risk</div>
                <div class="summary-value mono high">{{ $summary['high_risk'] ?? 0 }}</div>
            </td>
        </tr>
    </table>

    <h2 class="section-title">Top High-Risk Students</h2>

    <table class="data-table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Risk</th>
                <th>Score</th>
                <th>Attendance</th>
                <th>Progress</th>
                <th>Main Reason</th>
                <th>Recommended Action</th>
            </tr>
        </thead>

        <tbody>
            @forelse($topStudents->take(10) as $student)
                <tr>
                    <td>
                        <strong>{{ $displayValue($student['student_name'] ?? null) }}</strong><br>
                        <span class="small muted">
                            {{ $displayValue($student['instrument_name'] ?? null, 'No instrument') }}
                        </span>
                    </td>

                    <td class="{{ $riskClass($student['risk_level'] ?? 'Low Risk') }}">
                        {{ $displayValue($student['risk_level'] ?? null) }}
                    </td>

                    <td class="mono">{{ $displayValue($student['risk_score'] ?? null) }}</td>
                    <td class="mono">{{ $displayPercent($student['attendance_rate'] ?? null) }}</td>
                    <td class="mono">{{ $displayProgress($student['average_progress_rating'] ?? null) }}</td>
                    <td>{{ $displayValue($student['primary_reason'] ?? null) }}</td>
                    <td>{{ $displayValue($student['recommended_action'] ?? null, 'Continue monitoring.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="muted">No high-risk students detected.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2 class="section-title">Common Risk Factors</h2>

    <table class="data-table">
        <thead>
            <tr>
                <th>Risk Factor</th>
                <th>Count</th>
            </tr>
        </thead>

        <tbody>
            @forelse($factors->take(12) as $factor)
                <tr>
                    <td>{{ $displayValue($factor['factor'] ?? null) }}</td>
                    <td class="mono">{{ $factor['count'] ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="muted">No recurring risk factor detected.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2 class="section-title">Instructor Risk Breakdown</h2>

    <table class="data-table">
        <thead>
            <tr>
                <th>Instructor</th>
                <th>Total Students</th>
                <th>Low Risk</th>
                <th>Medium Risk</th>
                <th>High Risk</th>
            </tr>
        </thead>

        <tbody>
            @forelse($instructors->take(12) as $instructor)
                <tr>
                    <td>{{ $displayValue($instructor['instructor_name'] ?? null, 'Unassigned') }}</td>
                    <td class="mono">{{ $instructor['total_students'] ?? 0 }}</td>
                    <td class="mono low">{{ $instructor['low_risk'] ?? 0 }}</td>
                    <td class="mono medium">{{ $instructor['medium_risk'] ?? 0 }}</td>
                    <td class="mono high">{{ $instructor['high_risk'] ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="muted">No instructor risk data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <h2 class="section-title">All Students Sorted by Risk Score</h2>

    <table class="data-table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Risk</th>
                <th>Score</th>
                <th>Attendance</th>
                <th>Progress</th>
                <th>Enrollment</th>
                <th>Days Since Last Lesson</th>
                <th>Main Reason</th>
            </tr>
        </thead>

        <tbody>
            @forelse($students->take(40) as $student)
                <tr>
                    <td>
                        <strong>{{ $displayValue($student['student_name'] ?? null) }}</strong><br>
                        <span class="small muted">
                            {{ $displayValue($student['instrument_name'] ?? null, 'No instrument') }}
                        </span>
                    </td>

                    <td class="{{ $riskClass($student['risk_level'] ?? 'Low Risk') }}">
                        {{ $displayValue($student['risk_level'] ?? null) }}
                    </td>

                    <td class="mono">{{ $displayValue($student['risk_score'] ?? null) }}</td>
                    <td class="mono">{{ $displayPercent($student['attendance_rate'] ?? null) }}</td>
                    <td class="mono">{{ $displayProgress($student['average_progress_rating'] ?? null) }}</td>
                    <td>{{ $displayValue($student['enrollment_status'] ?? null) }}</td>
                    <td class="mono">{{ max(0, (int) ($student['days_since_last_lesson'] ?? 0)) }}</td>
                    <td>{{ $displayValue($student['primary_reason'] ?? null) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="muted">No student risk records available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-note">
        &copy; {{ date('Y') }} Music Lab. All rights reserved.
    </div>
</body>
</html>