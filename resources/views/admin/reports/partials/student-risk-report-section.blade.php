{{-- resources/views/admin/reports/partials/student-risk-report-section.blade.php --}}
{{--
    Student Retention Risk Report section.

    Important:
    - Export buttons were intentionally removed here.
    - The Reports page now has one combined PDF and one combined CSV export.
--}}

<section id="studentRiskReportSection" class="mb-8 rounded-[24px] border border-[#D8DDD8] bg-white p-6 shadow-sm">
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                Student Retention Risk Report
            </h2>
            <p class="mt-1 text-sm text-[#44576D]" style="font-family: 'Inter', sans-serif;">
                Data mining technique: Decision Tree Classification for student retention monitoring.
            </p>
        </div>
        <p id="studentRiskReportStatus" class="text-sm text-[#768A96]" style="font-family: 'Inter', sans-serif;">
            Loading risk analytics...
        </p>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-[18px] border border-[#D8DDD8] bg-[#FCFCFA] p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Analyzed</p>
            <p id="riskAnalyzed" class="mt-2 text-2xl font-bold text-[#223030]" style="font-family: 'JetBrains Mono', monospace;">0</p>
        </div>
        <div class="rounded-[18px] border border-emerald-100 bg-emerald-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700" style="font-family: 'Inter', sans-serif;">Low Risk</p>
            <p id="riskLow" class="mt-2 text-2xl font-bold text-emerald-700" style="font-family: 'JetBrains Mono', monospace;">0</p>
        </div>
        <div class="rounded-[18px] border border-amber-100 bg-amber-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700" style="font-family: 'Inter', sans-serif;">Medium Risk</p>
            <p id="riskMedium" class="mt-2 text-2xl font-bold text-amber-700" style="font-family: 'JetBrains Mono', monospace;">0</p>
        </div>
        <div class="rounded-[18px] border border-red-100 bg-red-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-red-700" style="font-family: 'Inter', sans-serif;">High Risk</p>
            <p id="riskHigh" class="mt-2 text-2xl font-bold text-red-700" style="font-family: 'JetBrains Mono', monospace;">0</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div class="rounded-[20px] border border-[#D8DDD8] bg-[#FCFCFA] p-4">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-[#44576D]" style="font-family: 'Inter', sans-serif;">
                Risk Distribution
            </h3>
            <div class="space-y-4" style="font-family: 'Inter', sans-serif;">
                <div>
                    <div class="mb-1 flex justify-between text-sm font-semibold text-[#44576D]"><span>Low Risk</span><span id="riskLowLabel">0</span></div>
                    <div class="h-3 overflow-hidden rounded-full bg-white"><div id="riskLowBar" class="h-full rounded-full bg-emerald-500" style="width: 0%"></div></div>
                </div>
                <div>
                    <div class="mb-1 flex justify-between text-sm font-semibold text-[#44576D]"><span>Medium Risk</span><span id="riskMediumLabel">0</span></div>
                    <div class="h-3 overflow-hidden rounded-full bg-white"><div id="riskMediumBar" class="h-full rounded-full bg-amber-500" style="width: 0%"></div></div>
                </div>
                <div>
                    <div class="mb-1 flex justify-between text-sm font-semibold text-[#44576D]"><span>High Risk</span><span id="riskHighLabel">0</span></div>
                    <div class="h-3 overflow-hidden rounded-full bg-white"><div id="riskHighBar" class="h-full rounded-full bg-red-500" style="width: 0%"></div></div>
                </div>
            </div>
        </div>

        <div class="rounded-[20px] border border-[#D8DDD8] bg-[#FCFCFA] p-4">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-[#44576D]" style="font-family: 'Inter', sans-serif;">
                Top High-Risk Students
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" style="font-family: 'Inter', sans-serif;">
                    <thead class="text-xs uppercase tracking-wide text-[#768A96]">
                        <tr class="border-b border-[#D8DDD8]">
                            <th class="py-2 pr-3">Student</th>
                            <th class="py-2 pr-3">Score</th>
                            <th class="py-2">Reason</th>
                        </tr>
                    </thead>
                    <tbody id="topHighRiskRows" class="divide-y divide-[#D8DDD8] text-[#223030]"></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    fetch('{{ route('admin.student-risk-analytics.report-data') }}', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then(response => response.json())
        .then(renderStudentRiskReport)
        .catch(function (error) {
            console.error('Risk report load failed:', error);
            setText('studentRiskReportStatus', 'Unable to load risk analytics.');
        });

    function renderStudentRiskReport(data) {
        const summary = data.summary || {};
        const students = Array.isArray(data.students) ? data.students : [];
        const highRiskStudents = students
            .filter(student => student.risk_level === 'High Risk')
            .sort((a, b) => Number(b.risk_score || 0) - Number(a.risk_score || 0))
            .slice(0, 10);

        const analyzed = Number(summary.analyzed || 0);
        const low = Number(summary.low_risk || 0);
        const medium = Number(summary.medium_risk || 0);
        const high = Number(summary.high_risk || 0);

        setText('riskAnalyzed', analyzed);
        setText('riskLow', low);
        setText('riskMedium', medium);
        setText('riskHigh', high);
        setText('riskLowLabel', low);
        setText('riskMediumLabel', medium);
        setText('riskHighLabel', high);

        setWidth('riskLowBar', percentage(low, analyzed));
        setWidth('riskMediumBar', percentage(medium, analyzed));
        setWidth('riskHighBar', percentage(high, analyzed));

        renderHighRiskRows(highRiskStudents);
        setText('studentRiskReportStatus', data.ok === false ? (data.message || 'Analytics unavailable.') : 'Updated');
    }

    function renderHighRiskRows(students) {
        const tbody = document.getElementById('topHighRiskRows');
        if (!tbody) return;

        if (students.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="py-4 text-center text-[#768A96]">No high-risk students detected.</td></tr>';
            return;
        }

        tbody.innerHTML = students.map(student => `
            <tr>
                <td class="py-3 pr-3 font-semibold">${escapeHtml(student.student_name || 'N/A')}</td>
                <td class="py-3 pr-3" style="font-family: JetBrains Mono, monospace;">${Number(student.risk_score || 0).toFixed(2)}</td>
                <td class="py-3 text-[#44576D]">${escapeHtml(student.primary_reason || 'No reason provided.')}</td>
            </tr>
        `).join('');
    }

    function percentage(value, total) {
        if (!total) return 0;
        return Math.min(100, Math.round((Number(value) / Number(total)) * 100));
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    function setWidth(id, value) {
        const el = document.getElementById(id);
        if (el) el.style.width = value + '%';
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
});
</script>
