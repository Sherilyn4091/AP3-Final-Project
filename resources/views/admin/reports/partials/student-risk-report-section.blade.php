{{-- resources/views/admin/reports/partials/student-risk-report-section.blade.php --}}
<section class="mb-8 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm" data-risk-widget="reports">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-800">Student Retention Risk Report</h2>
            <p class="text-xs text-gray-500">Data mining technique: Decision Tree Classification for student retention monitoring.</p>
        </div>
        <a href="{{ route('admin.student-risk-analytics.export-csv') }}"
           class="inline-flex rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
            Export Risk CSV
        </a>
    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Analyzed</p>
            <p class="mt-1 text-xl font-bold text-gray-900" data-risk-card="total_students_analyzed">--</p>
        </div>
        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Low Risk</p>
            <p class="mt-1 text-xl font-bold text-emerald-700" data-risk-card="low_risk">--</p>
        </div>
        <div class="rounded-xl border border-amber-100 bg-amber-50 p-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Medium Risk</p>
            <p class="mt-1 text-xl font-bold text-amber-700" data-risk-card="medium_risk">--</p>
        </div>
        <div class="rounded-xl border border-red-100 bg-red-50 p-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-red-700">High Risk</p>
            <p class="mt-1 text-xl font-bold text-red-700" data-risk-card="high_risk">--</p>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
            <h3 class="text-sm font-semibold text-gray-700">Risk Distribution</h3>
            <div class="mt-3" data-risk-widget-bars></div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
            <h3 class="text-sm font-semibold text-gray-700">Top High-Risk Students</h3>
            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                            <th class="py-2 pr-3">Student</th>
                            <th class="py-2 pr-3">Score</th>
                            <th class="py-2 pr-3">Reason</th>
                        </tr>
                    </thead>
                    <tbody data-risk-widget-students>
                        <tr><td colspan="3" class="py-4 text-center text-gray-500">Loading risk report...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    window.studentRiskWidgetConfig = window.studentRiskWidgetConfig || {
        dashboardDataUrl: @json(route('admin.student-risk-analytics.report-data')),
        refreshMs: 60000
    };
</script>
@vite('resources/js/admin-pages/student-risk-dashboard-widget.js')
@endpush
