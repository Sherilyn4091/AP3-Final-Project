{{-- resources/views/admin/partials/student-risk-dashboard-widget.blade.php --}}
<section class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm" data-risk-widget="dashboard">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-800">Student Risk Summary</h2>
            <p class="text-xs text-gray-500">Python-powered Decision Tree Classification. Refreshes automatically.</p>
        </div>
        <a href="{{ route('admin.student-risk-analytics.index') }}"
           class="inline-flex rounded-lg bg-[#2d2d2d] px-3 py-2 text-xs font-semibold text-white transition hover:bg-[#525252]">
            View Risk Analytics
        </a>
    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Analyzed</p>
            <p class="mt-1 text-xl font-bold text-gray-900" data-risk-card="total_students_analyzed">--</p>
        </div>
        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Low</p>
            <p class="mt-1 text-xl font-bold text-emerald-700" data-risk-card="low_risk">--</p>
        </div>
        <div class="rounded-xl border border-amber-100 bg-amber-50 p-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Medium</p>
            <p class="mt-1 text-xl font-bold text-amber-700" data-risk-card="medium_risk">--</p>
        </div>
        <div class="rounded-xl border border-red-100 bg-red-50 p-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-red-700">High</p>
            <p class="mt-1 text-xl font-bold text-red-700" data-risk-card="high_risk">--</p>
        </div>
    </div>

    <div class="mt-4" data-risk-widget-bars></div>
</section>

@push('scripts')
<script>
    window.studentRiskWidgetConfig = window.studentRiskWidgetConfig || {
        dashboardDataUrl: @json(route('admin.student-risk-analytics.dashboard-data')),
        refreshMs: 60000
    };
</script>
@vite('resources/js/admin-pages/student-risk-dashboard-widget.js')
@endpush
