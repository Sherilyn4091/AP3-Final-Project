{{-- resources/views/admin/reports/index.blade.php --}}
@extends('layouts.admin-default')

@section('title', 'Monthly Report')

@section('headername')
    <h1 class="text-2xl font-bold text-gray-800">Monthly Reports (Analytics)</h1>
    <p class="text-gray-600 mt-1 text-sm">
        Paid Revenue: <span class="font-semibold">₱{{ number_format($stats['total_paid_revenue'] ?? 0, 2) }}</span> •
        30 Days: <span class="text-emerald-700 font-semibold">₱{{ number_format($stats['revenue_30_days'] ?? 0, 2) }}</span> •
        Active Enrollments: <span class="text-indigo-700 font-semibold">{{ $stats['active_enrollments'] ?? 0 }}</span>
    </p>
@endsection

@section('header_actions')
    <a href="{{ route('admin.reports.export-pdf') }}"
       class="inline-flex items-center px-5 py-2.5 bg-[#2d2d2d] text-white rounded-lg shadow-sm hover:bg-[#525252] transition-all duration-200 font-medium">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
        </svg>
        Export Report (PDF)
    </a>
@endsection

@section('maincontent')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- KPI Cards --}}
    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm font-medium text-gray-500">Total Paid Revenue</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">
                ₱{{ number_format($stats['total_paid_revenue'] ?? 0, 2) }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm font-medium text-gray-500">Revenue (Last 30 Days)</p>
            <p class="text-2xl font-bold text-emerald-700 mt-1">
                ₱{{ number_format($stats['revenue_30_days'] ?? 0, 2) }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm font-medium text-gray-500">Enrollments (All / Active)</p>
            <p class="text-2xl font-bold text-indigo-700 mt-1">
                {{ $stats['total_enrollments'] ?? 0 }} / {{ $stats['active_enrollments'] ?? 0 }}
            </p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-700 mb-4">Revenue Trend (Last 30 Days)</h3>
            <div class="chart-wrap">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-700 mb-4">Enrollment Trend (Last 30 Days)</h3>
            <div class="chart-wrap">
                <canvas id="enrollmentTrendChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-700 mb-4">Revenue by Payment Method</h3>
            <div class="chart-wrap">
                <canvas id="revenueByMethodChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-700 mb-4">Enrollment Package Popularity (5/10/20)</h3>
            <div class="chart-wrap">
                <canvas id="packagePopularityChart"></canvas>
            </div>
        </div>

    </div>
</div>

{{-- Page-only styles --}}
<style>
    .chart-wrap {
        position: relative;
        width: 100%;
        height: clamp(220px, 28vh, 340px);
    }
</style>

{{-- 1) Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>

{{-- 2) Pass PHP data to JS safely (no red squiggles in external file) --}}
<script>
   window.__monthlyReportChartData = @json($chartData ?? []);
</script>

{{-- 3) Load your external JS (Vite) --}}
@vite('resources/js/charts/ReportsChart.js')

@endsection
