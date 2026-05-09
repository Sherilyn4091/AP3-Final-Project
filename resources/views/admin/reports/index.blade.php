{{-- resources/views/admin/reports/index.blade.php --}}
@extends('layouts.admin-default')

@section('title', 'Monthly Report')

@section('headername')
    @php
        $totalPaidRevenue = (float) ($stats['total_paid_revenue'] ?? 0);
        $revenue30Days = (float) ($stats['revenue_30_days'] ?? 0);
        $activeEnrollments = (int) ($stats['active_enrollments'] ?? 0);
    @endphp

    <h1 class="text-2xl font-bold tracking-tight text-[#223030]" style="font-family: 'Sora', sans-serif;">
        Monthly Reports (Analytics)
    </h1>

    <p class="mt-1 text-sm text-[#44576D]" style="font-family: 'Inter', sans-serif;">
        Paid Revenue:
        <span class="font-semibold text-[#223030]" style="font-family: 'JetBrains Mono', monospace;" data-count-up data-target="{{ $totalPaidRevenue }}" data-prefix="&#8369;" data-decimals="2">&#8369;0.00</span>
        <span class="mx-1 text-[#768A96]">&bull;</span>
        30 Days:
        <span class="font-semibold text-emerald-700" style="font-family: 'JetBrains Mono', monospace;" data-count-up data-target="{{ $revenue30Days }}" data-prefix="&#8369;" data-decimals="2">&#8369;0.00</span>
        <span class="mx-1 text-[#768A96]">&bull;</span>
        Active Enrollments:
        <span class="font-semibold text-indigo-700" style="font-family: 'JetBrains Mono', monospace;" data-count-up data-target="{{ $activeEnrollments }}" data-decimals="0">0</span>
    </p>
@endsection

@section('header_actions')
    {{-- One-page exports: Monthly Reports + Student Retention Risk Report --}}
    <div class="flex flex-wrap items-center gap-3" style="font-family: 'Inter', sans-serif;">
        <a href="{{ route('admin.reports.export-pdf') }}"
           class="inline-flex items-center rounded-xl bg-[#223030] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#2F4F4F]">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
            </svg>
            Export Full PDF
        </a>

        <a href="{{ route('admin.reports.export-csv') }}"
           class="inline-flex items-center rounded-xl border border-[#D8DDD8] bg-white px-5 py-2.5 text-sm font-semibold text-[#223030] shadow-sm transition hover:bg-[#FCFCFA]">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export Full CSV
        </a>
    </div>
@endsection

@section('maincontent')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

    {{-- Font import for this analytics page. --}}
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&family=Sora:wght@400;600;700;800&display=swap');
    </style>

    {{-- KPI Cards --}}
    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-8">
        <div class="rounded-[20px] border border-[#D8DDD8] bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#768A96]"
            style="font-family: 'Inter', sans-serif;">
                Total Paid Revenue
            </p>

            <p class="mt-3 text-2xl font-bold text-[#223030]"
            style="font-family: 'JetBrains Mono', monospace;"
            data-count-up
            data-target="{{ $stats['total_paid_revenue'] ?? 0 }}"
            data-prefix="&#8369;"
            data-decimals="2">
                &#8369;0.00
            </p>
        </div>

        <div class="rounded-[20px] border border-[#D8DDD8] bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#768A96]"
            style="font-family: 'Inter', sans-serif;">
                Revenue Last 30 Days
            </p>

            <p class="mt-3 text-2xl font-bold text-emerald-700"
            style="font-family: 'JetBrains Mono', monospace;"
            data-count-up
            data-target="{{ $stats['revenue_30_days'] ?? 0 }}"
            data-prefix="&#8369;"
            data-decimals="2">
                &#8369;0.00
            </p>
        </div>

        <div class="rounded-[20px] border border-[#D8DDD8] bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#768A96]"
            style="font-family: 'Inter', sans-serif;">
                Enrollments All / Active
            </p>

            <p class="mt-3 text-2xl font-bold text-[#223030]"
            style="font-family: 'JetBrains Mono', monospace;">
                <span data-count-up
                    data-target="{{ $stats['total_enrollments'] ?? 0 }}"
                    data-decimals="0">0</span>
                <span class="mx-2 text-[#768A96]">/</span>
                <span data-count-up
                    data-target="{{ $stats['active_enrollments'] ?? 0 }}"
                    data-decimals="0">0</span>
            </p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-[20px] border border-[#D8DDD8] bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-[#44576D]" style="font-family: 'Sora', sans-serif;">Revenue Trend (Last 30 Days)</h3>
            <div class="chart-wrap relative h-[clamp(220px,28vh,340px)] rounded-[20px] bg-[#FCFCFA]">
                <canvas id="revenueTrendChart"></canvas>
                <div id="revenueTrendEmpty" class="absolute inset-0 hidden items-center justify-center rounded-[20px] bg-[#FCFCFA]">
                    <p class="text-sm text-[#768A96]" style="font-family: 'Inter', sans-serif;">No report data available for this period.</p>
                </div>
            </div>
        </div>

        <div class="rounded-[20px] border border-[#D8DDD8] bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-[#44576D]" style="font-family: 'Sora', sans-serif;">Enrollment Trend (Last 30 Days)</h3>
            <div class="chart-wrap relative h-[clamp(220px,28vh,340px)] rounded-[20px] bg-[#FCFCFA]">
                <canvas id="enrollmentTrendChart"></canvas>
                <div id="enrollmentTrendEmpty" class="absolute inset-0 hidden items-center justify-center rounded-[20px] bg-[#FCFCFA]">
                    <p class="text-sm text-[#768A96]" style="font-family: 'Inter', sans-serif;">No report data available for this period.</p>
                </div>
            </div>
        </div>

        <div class="rounded-[20px] border border-[#D8DDD8] bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-[#44576D]" style="font-family: 'Sora', sans-serif;">Revenue by Payment Method</h3>
            <div class="chart-wrap relative h-[clamp(220px,28vh,340px)] rounded-[20px] bg-[#FCFCFA]">
                <canvas id="revenueByMethodChart"></canvas>
                <div id="revenueByMethodEmpty" class="absolute inset-0 hidden items-center justify-center rounded-[20px] bg-[#FCFCFA]">
                    <p class="text-sm text-[#768A96]" style="font-family: 'Inter', sans-serif;">No report data available for this period.</p>
                </div>
            </div>
        </div>

        <div class="rounded-[20px] border border-[#D8DDD8] bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-[#44576D]" style="font-family: 'Sora', sans-serif;">Enrollment Package Popularity (5/10/20)</h3>
            <div class="chart-wrap relative h-[clamp(220px,28vh,340px)] rounded-[20px] bg-[#FCFCFA]">
                <canvas id="packagePopularityChart"></canvas>
                <div id="packagePopularityEmpty" class="absolute inset-0 hidden items-center justify-center rounded-[20px] bg-[#FCFCFA]">
                    <p class="text-sm text-[#768A96]" style="font-family: 'Inter', sans-serif;">No report data available for this period.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Python-powered Student Retention Risk Report --}}
    @include('admin.reports.partials.student-risk-report-section')
</div>

<script>
   window.__monthlyReportChartData = @json($chartData ?? []);
</script>

@vite('resources/js/admin-pages/reports-chart.js')
@endsection
