{{-- resources/views/admin/student-risk-analytics/index.blade.php --}}
@extends('layouts.admin-default')

@section('title', 'Student Risk Analytics')

@section('headername')
    <h1 class="text-2xl font-bold text-gray-800">Student Risk Analytics</h1>
    <p class="mt-1 text-sm text-gray-600">
        Decision Tree Classification for student retention monitoring.
    </p>
@endsection

@section('header_actions')
    <div class="flex flex-wrap gap-2">
        <button type="button"
                id="riskRefreshButton"
                class="inline-flex items-center rounded-lg bg-[#2d2d2d] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#525252]">
            Refresh Analysis
        </button>

        <a href="{{ route('admin.student-risk-analytics.export-csv') }}"
           class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
            Export CSV
        </a>
    </div>
@endsection

@section('maincontent')
<div class="px-4 py-6 sm:px-6 lg:px-8" data-risk-page>

    {{-- Status / error notice --}}
    <div id="riskStatusNotice"
         class="mb-4 hidden rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm">
    </div>

    {{-- Summary cards: compact and responsive --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Analyzed Students</p>
            <p id="riskTotalStudents" class="mt-2 text-2xl font-bold text-gray-900">--</p>
            <p class="mt-1 text-xs text-gray-500">Included in current risk scan</p>
        </div>

        <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Low Risk</p>
            <p id="riskLowCount" class="mt-2 text-2xl font-bold text-emerald-700">--</p>
            <p class="mt-1 text-xs text-gray-500">Stable attendance and progress</p>
        </div>

        <div class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Medium Risk</p>
            <p id="riskMediumCount" class="mt-2 text-2xl font-bold text-amber-700">--</p>
            <p class="mt-1 text-xs text-gray-500">Needs monitoring</p>
        </div>

        <div class="rounded-2xl border border-red-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-red-700">High Risk</p>
            <p id="riskHighCount" class="mt-2 text-2xl font-bold text-red-700">--</p>
            <p class="mt-1 text-xs text-gray-500">Needs early intervention</p>
        </div>
    </section>

    {{-- Charts / visual analytics using HTML bars, not PNG --}}
    <section class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-base font-semibold text-gray-800">Risk Distribution</h2>
                <span id="riskGeneratedAt" class="text-xs text-gray-500">Waiting for analysis...</span>
            </div>

            <div id="riskDistributionBars" class="space-y-3">
                {{-- JavaScript renders bars here --}}
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="mb-4 text-base font-semibold text-gray-800">Common Risk Factors</h2>
            <div id="riskFactorBars" class="space-y-3">
                {{-- JavaScript renders bars here --}}
            </div>
        </div>
    </section>

    {{-- Controls --}}
    <section class="mt-6 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Search Student</label>
                <input type="text"
                       id="riskSearchInput"
                       placeholder="Search name, instrument, instructor..."
                       class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-200">
            </div>

            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Risk Level</label>
                <select id="riskLevelFilter"
                        class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-200">
                    <option value="all">All Risk Levels</option>
                    <option value="High Risk">High Risk</option>
                    <option value="Medium Risk">Medium Risk</option>
                    <option value="Low Risk">Low Risk</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="button"
                        id="riskClearFiltersButton"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Clear Filters
                </button>
            </div>
        </div>
    </section>

    {{-- High-risk students --}}
    <section class="mt-6 rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-4 py-3">
            <h2 class="text-base font-semibold text-gray-800">Top High-Risk Students</h2>
            <p class="mt-1 text-xs text-gray-500">Sorted by risk score. Use this table for immediate admin follow-up.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Risk</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Attendance</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Progress</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Payment</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Main Reason</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody id="topRiskStudentsTable" class="divide-y divide-gray-100 bg-white">
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">Loading student risk analytics...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    {{-- All students --}}
    <section class="mt-6 rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-4 py-3">
            <h2 class="text-base font-semibold text-gray-800">All Students Risk Table</h2>
            <p class="mt-1 text-xs text-gray-500">Compact list for reviewing every student classified by the Python engine.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Instrument</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Instructor</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Risk Level</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Score</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Reason</th>
                    </tr>
                </thead>
                <tbody id="allRiskStudentsTable" class="divide-y divide-gray-100 bg-white">
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    window.studentRiskAnalyticsConfig = {
        dataUrl: @json(route('admin.student-risk-analytics.data')),
        refreshMs: 60000
    };
</script>
@vite('resources/js/admin-pages/student-risk-analytics.js')
@endpush
