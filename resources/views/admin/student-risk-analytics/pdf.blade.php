<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Risk Analytics Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #272829;
            font-size: 11px;
            margin: 24px;
        }

        h1, h2 {
            margin: 0;
            color: #2F4F4F;
        }

        h1 {
            font-size: 20px;
        }

        h2 {
            font-size: 14px;
            margin-top: 20px;
            margin-bottom: 8px;
        }

        .muted {
            color: #61677A;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .summary td {
            border: 1px solid #D8D9DA;
            padding: 10px;
            width: 25%;
        }

        .label {
            font-size: 9px;
            text-transform: uppercase;
            color: #61677A;
            letter-spacing: 0.04em;
        }

        .value {
            margin-top: 4px;
            font-size: 18px;
            font-weight: bold;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table.data th {
            background: #fcf3e3;
            color: #394a56;
            font-size: 9px;
            text-transform: uppercase;
            text-align: left;
            padding: 7px;
            border: 1px solid #D8D9DA;
        }

        table.data td {
            padding: 7px;
            border: 1px solid #D8D9DA;
            vertical-align: top;
        }

        .low { color: #2F4F4F; font-weight: bold; }
        .medium { color: #B4833D; font-weight: bold; }
        .high { color: #9f1d20; font-weight: bold; }
    </style>
</head>
<body>
    @php
        $summary = $result['summary'] ?? [];
        $students = $result['students'] ?? [];
        $topStudents = $result['top_high_risk_students'] ?? [];
        $factors = $result['factor_summary'] ?? [];
    @endphp

    <h1>Student Risk Analytics Report</h1>
    <p class="muted">
        Data mining technique: Decision Tree Classification for student retention monitoring.<br>
        Generated: {{ $generatedAt }}
    </p>

    <table class="summary">
        <tr>
            <td>
                <div class="label">Analyzed Students</div>
                <div class="value">{{ $summary['total_students_analyzed'] ?? 0 }}</div>
            </td>
            <td>
                <div class="label">Low Risk</div>
                <div class="value low">{{ $summary['low_risk'] ?? 0 }}</div>
            </td>
            <td>
                <div class="label">Medium Risk</div>
                <div class="value medium">{{ $summary['medium_risk'] ?? 0 }}</div>
            </td>
            <td>
                <div class="label">High Risk</div>
                <div class="value high">{{ $summary['high_risk'] ?? 0 }}</div>
            </td>
        </tr>
    </table>

    <h2>Top High-Risk Students</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Student</th>
                <th>Risk Score</th>
                <th>Attendance</th>
                <th>Progress</th>
                <th>Main Reason</th>
                <th>Recommended Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse(array_slice($topStudents, 0, 10) as $student)
                <tr>
                    <td>{{ $student['student_name'] ?? 'N/A' }}</td>
                    <td>{{ $student['risk_score'] ?? 'N/A' }}</td>
                    <td>{{ $student['attendance_rate'] ?? 'N/A' }}%</td>
                    <td>{{ $student['average_progress_rating'] ?? 'N/A' }}</td>
                    <td>{{ $student['primary_reason'] ?? 'N/A' }}</td>
                    <td>{{ $student['recommended_action'] ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No high-risk students detected.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Common Risk Factors</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Risk Factor</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            @forelse($factors as $factor)
                <tr>
                    <td>{{ $factor['factor'] ?? 'N/A' }}</td>
                    <td>{{ $factor['count'] ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">No recurring risk factor detected.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>All Students Sorted by Risk Score</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Student</th>
                <th>Risk</th>
                <th>Score</th>
                <th>Attendance</th>
                <th>Enrollment</th>
                <th>Days Since Last Lesson</th>
                <th>Main Reason</th>
            </tr>
        </thead>
        <tbody>
            @foreach(array_slice($students, 0, 40) as $student)
                <tr>
                    <td>{{ $student['student_name'] ?? 'N/A' }}</td>
                    <td>{{ $student['risk_level'] ?? 'N/A' }}</td>
                    <td>{{ $student['risk_score'] ?? 'N/A' }}</td>
                    <td>{{ $student['attendance_rate'] ?? 'N/A' }}%</td>
                    <td>{{ $student['enrollment_status'] ?? 'N/A' }}</td>
                    <td>{{ $student['days_since_last_lesson'] ?? 0 }}</td>
                    <td>{{ $student['primary_reason'] ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
