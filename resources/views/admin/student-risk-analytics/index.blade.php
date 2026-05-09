{{-- resources/views/admin/student-risk-analytics/index.blade.php --}}
@extends('layouts.admin-default')

@section('title', 'Student Risk Analytics')

@section('headername')
    <div class="risk-header-title">
        <h1 class="text-[25px] font-semibold tracking-tight text-[#272829] sm:text-[26px]">Student Risk Analytics</h1>
        <p class="mt-1 text-[13px] leading-5 text-[#61677A]">
            Decision Tree Classification for student retention monitoring.
        </p>
    </div>
@endsection

@section('header_actions')
    <div class="flex flex-col items-start gap-1 sm:items-end">
        <div class="flex flex-wrap gap-2">
            <button type="button"
                    id="riskRefreshButton"
                    class="inline-flex items-center rounded-2xl bg-[#2F4F4F] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#3C4B33] focus:outline-none focus:ring-2 focus:ring-[#959D90]">
                Refresh Analysis
            </button>

            <button type="button"
                    id="riskExportButton"
                    class="inline-flex items-center rounded-2xl border border-[#D8D9DA] bg-white px-4 py-2 text-sm font-semibold text-[#394a56] shadow-sm transition hover:border-[#959D90] hover:bg-[#FFF6E0] focus:outline-none focus:ring-2 focus:ring-[#959D90]">
                Export CSV
            </button>
        </div>

        <p id="riskCountdownText" class="risk-mono text-[11px] leading-4 text-[#959D90]">
            Next refresh in 60s
        </p>
    </div>
@endsection

@section('maincontent')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Sora:wght@500;600;700&display=swap');

    .risk-analytics-scope {
        --risk-primary: #2F4F4F;
        --risk-secondary: #3C4B33;
        --risk-brown: #42300B;
        --risk-gold: #B4833D;
        --risk-sage: #959D90;
        --risk-light: #D8D9DA;
        --risk-cream: #FFF6E0;
        --risk-dark: #272829;
        --risk-border: #61677A;
        --risk-teal: #013d5a;
        --risk-warm: #fcf3e3;
        --risk-slate: #394a56;
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    .risk-analytics-scope h1,
    .risk-analytics-scope h2,
    .risk-analytics-scope h3,
    .risk-header-title h1 {
        font-family: 'Sora', 'Inter', system-ui, sans-serif;
    }

    .risk-mono {
        font-family: 'JetBrains Mono', Consolas, 'Courier New', monospace;
    }

    .risk-card {
        border-radius: 22px;
        border: 1px solid rgba(97, 103, 122, 0.18);
        background: #ffffff;
        box-shadow: 0 8px 18px rgba(39, 40, 41, 0.05);
    }

    .risk-card-top {
        position: relative;
        overflow: hidden;
    }

    .risk-card-top::before {
        content: '';
        position: absolute;
        inset: 0 0 auto 0;
        height: 4px;
        background: var(--accent-color, var(--risk-primary));
    }

    .risk-badge-dot {
        width: 7px;
        height: 7px;
        border-radius: 9999px;
        background: currentColor;
        opacity: 0.85;
    }

    .risk-bar-fill {
        width: 0%;
        transition: width 0.9s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    .risk-row-chevron {
        transition: transform 0.2s ease;
    }

    .risk-row-expanded .risk-row-chevron {
        transform: rotate(90deg);
    }
</style>

<div class="risk-analytics-scope px-4 py-6 sm:px-6 lg:px-8" data-risk-page>

    {{-- Status / error notice --}}
    <div id="riskStatusNotice"
         class="mb-4 hidden rounded-[20px] border border-[#D8D9DA] bg-white px-4 py-3 text-sm text-[#394a56] shadow-sm">
    </div>

    {{-- Summary cards: compact and responsive --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="risk-card risk-card-top p-4" style="--accent-color: #394a56;">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#61677A]">Analyzed Students</p>
            <p id="riskTotalStudents" class="risk-mono mt-2 text-2xl font-bold text-[#272829]">--</p>
            <p class="mt-1 text-xs text-[#61677A]">Included in current risk scan</p>
        </div>

        <div class="risk-card risk-card-top p-4" style="--accent-color: #2F4F4F;">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#2F4F4F]">Low Risk</p>
            <p id="riskLowCount" class="risk-mono mt-2 text-2xl font-bold text-[#2F4F4F]">--</p>
            <p class="mt-1 text-xs text-[#61677A]">Stable attendance and progress</p>
        </div>

        <div class="risk-card risk-card-top p-4" style="--accent-color: #B4833D;">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#B4833D]">Medium Risk</p>
            <p id="riskMediumCount" class="risk-mono mt-2 text-2xl font-bold text-[#B4833D]">--</p>
            <p class="mt-1 text-xs text-[#61677A]">Needs monitoring</p>
        </div>

        <div class="risk-card risk-card-top p-4" style="--accent-color: #9f1d20;">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#9f1d20]">High Risk</p>
            <p id="riskHighCount" class="risk-mono mt-2 text-2xl font-bold text-[#9f1d20]">--</p>
            <p class="mt-1 text-xs text-[#61677A]">Needs early intervention</p>
        </div>
    </section>

    {{-- Charts / visual analytics using HTML bars, not PNG --}}
    <section class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="risk-card p-4">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-[17px] font-semibold text-[#272829]">Risk Distribution</h2>
                <span id="riskGeneratedAt" class="risk-mono text-[11px] text-[#959D90]">Waiting for analysis...</span>
            </div>

            <div id="riskDistributionBars" class="space-y-3">
                {{-- JavaScript renders bars here --}}
            </div>
        </div>

        <div class="risk-card p-4">
            <h2 class="mb-4 text-[17px] font-semibold text-[#272829]">Common Risk Factors</h2>
            <div id="riskFactorBars" class="space-y-3">
                {{-- JavaScript renders bars here --}}
            </div>
        </div>
    </section>

    {{-- Controls --}}
    <section class="risk-card mt-6 p-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-[#61677A]">Search Student</label>
                <input type="text"
                       id="riskSearchInput"
                       placeholder="Search name, instrument, instructor..."
                       class="mt-1 w-full rounded-2xl border border-[#D8D9DA] px-3 py-2 text-sm text-[#272829] focus:border-[#959D90] focus:outline-none focus:ring-2 focus:ring-[#959D90]/30">
            </div>

            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-[#61677A]">Risk Level</label>
                <select id="riskLevelFilter"
                        class="mt-1 w-full rounded-2xl border border-[#D8D9DA] px-3 py-2 text-sm text-[#272829] focus:border-[#959D90] focus:outline-none focus:ring-2 focus:ring-[#959D90]/30">
                    <option value="all">All Risk Levels</option>
                    <option value="High Risk">High Risk</option>
                    <option value="Medium Risk">Medium Risk</option>
                    <option value="Low Risk">Low Risk</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="button"
                        id="riskClearFiltersButton"
                        class="w-full rounded-2xl border border-[#D8D9DA] bg-white px-3 py-2 text-sm font-semibold text-[#394a56] transition hover:border-[#959D90] hover:bg-[#FFF6E0]">
                    Clear Filters
                </button>
            </div>
        </div>
    </section>

    {{-- High-risk students --}}
    <section class="risk-card mt-6 overflow-hidden">
        <div class="border-b border-[#D8D9DA] px-4 py-3">
            <h2 class="text-[17px] font-semibold text-[#272829]">Top High-Risk Students</h2>
            <p class="mt-1 text-xs text-[#61677A]">Sorted by risk score. Use this table for immediate admin follow-up.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#D8D9DA] text-sm">
                <thead class="bg-[#fcf3e3]">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">Risk</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">Attendance</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">Progress</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">Payment</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">Main Reason</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">Action</th>
                    </tr>
                </thead>
                <tbody id="topRiskStudentsTable" class="divide-y divide-[#D8D9DA]/60 bg-white">
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-[#61677A]">Loading student risk analytics...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    {{-- All students --}}
    <section class="risk-card mt-6 overflow-hidden">
        <div class="border-b border-[#D8D9DA] px-4 py-3">
            <h2 class="text-[17px] font-semibold text-[#272829]">All Students Risk Table</h2>
            <p class="mt-1 text-xs text-[#61677A]">Click a row to view all risk reasons inline.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#D8D9DA] text-sm">
                <thead class="bg-[#fcf3e3]">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">Instrument</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">Instructor</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">Risk Level</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">Score</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">Reason</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[#394a56]">Details</th>
                    </tr>
                </thead>
                <tbody id="allRiskStudentsTable" class="divide-y divide-[#D8D9DA]/60 bg-white">
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-[#61677A]">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    {{-- Instructor drill-down --}}
    <section class="risk-card mt-6 overflow-hidden">
        <div class="border-b border-[#D8D9DA] px-4 py-3">
            <h2 class="text-[17px] font-semibold text-[#272829]">Instructor Risk Breakdown</h2>
            <p class="mt-1 text-xs text-[#61677A]">Sorted by high-risk count to help admins see where follow-up may be needed.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#D8D9DA] text-sm">
                <thead class="bg-[#fcf3e3]">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">Instructor Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">Total Students</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">Low Risk</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">Medium Risk</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#394a56]">High Risk</th>
                    </tr>
                </thead>
                <tbody id="instructorRiskTable" class="divide-y divide-[#D8D9DA]/60 bg-white">
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-[#61677A]">Loading instructor risk breakdown...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    window.studentRiskAnalyticsConfig = {!! \Illuminate\Support\Js::from([
        'dataUrl' => route('admin.student-risk-analytics.data'),
        'exportCsvUrl' => route('admin.student-risk-analytics.export-csv'),
        'refreshMs' => 60000,
    ]) !!};
</script>
@vite('resources/js/admin-pages/student-risk-analytics.js')
@endpush
