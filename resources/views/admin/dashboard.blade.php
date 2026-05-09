{{-- resources/views/admin/dashboard.blade.php --}}
{{-- 
    ============================================================================
    SUPER ADMIN DASHBOARD - Music Lab
    Main control center for system-wide overview and management
    Live React/JavaScript chart integration ready
    ============================================================================
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - Music Lab</title>
    {{-- ============================================================================
    DASHBOARD JAVASCRIPT CONFIGURATION
    --------------------------------------------------------------------------
    Purpose:
    - Provides the Student Risk widget with the correct Laravel endpoint.
    - Loads dashboard charts.
    - Loads Student Risk Summary widget script.
    ============================================================================ --}}

    <script>
        window.studentRiskWidgetConfig = {
            dashboardDataUrl: @json(route('admin.student-risk-analytics.dashboard-data')),
            refreshMs: 60000
        };
    </script>

    @vite([
        'resources/css/style.css',
        'resources/js/script.js',
        'resources/js/admin-pages/dashboard-charts.js',
        'resources/js/admin-pages/student-risk-dashboard-widget.js',
    ])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-light-gray">

@include('layouts.admin-header')

<main class="lg:ml-64 min-h-screen bg-light-gray">
    
    {{-- Page Header --}}
    <header class="bg-white shadow-sm p-6 lg:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-primary-dark">Welcome, Super Admin</h1>
                <p class="text-secondary-blue mt-1">{{ now()->format('l, F j, Y') }}</p>
            </div>
            <div>
                <button onclick="openAccountModal()" class="btn-secondary px-4 py-2 text-sm whitespace-nowrap">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Account Settings
                </button>
            </div>
        </div>
    </header>

    <div class="p-4 lg:p-8">
        
        {{-- ============================================================================
            ADMIN KPI SUMMARY CARDS
            --------------------------------------------------------------------------
            Purpose:
            - Cleaner and more professional stat-card layout.
            - Smaller/semi-large numbers to avoid oversized display.
            - Uses Music Lab palette and standard fonts:
                Sora = headings
                Inter = labels
                JetBrains Mono = numeric values
            - Uses data-count-up attributes so dashboard-charts.js can animate values.
            - Uses HTML entity &#8369; for peso sign to avoid random symbols/mojibake.
            ============================================================================ --}}

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">

            {{-- Total Users --}}
            <div class="rounded-[22px] border border-[#D8DDD8] bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#768A96]"
                        style="font-family: 'Inter', sans-serif;">
                            Total Users
                        </p>

                        <p class="mt-3 text-2xl font-bold text-[#223030]"
                        style="font-family: 'JetBrains Mono', monospace;"
                        data-count-up
                        data-target="{{ $totalUsers }}"
                        data-decimals="0">
                            0
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#2F4F4F]/10 text-[#2F4F4F]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H4v-2a4 4 0 015-3.87m8-4.13a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Active Students --}}
            <div class="rounded-[22px] border border-[#D8DDD8] bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#768A96]"
                        style="font-family: 'Inter', sans-serif;">
                            Active Students
                        </p>

                        <p class="mt-3 text-2xl font-bold text-[#223030]"
                        style="font-family: 'JetBrains Mono', monospace;"
                        data-count-up
                        data-target="{{ $activeStudents }}"
                        data-decimals="0">
                            0
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#B4833D]/15 text-[#B4833D]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 14l6.16-3.42a12.08 12.08 0 01.66 6.48A11.95 11.95 0 0012 20.05a11.95 11.95 0 00-6.82-2.99 12.08 12.08 0 01.66-6.48L12 14z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Active Instructors --}}
            <div class="rounded-[22px] border border-[#D8DDD8] bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#768A96]"
                        style="font-family: 'Inter', sans-serif;">
                            Active Instructors
                        </p>

                        <p class="mt-3 text-2xl font-bold text-[#223030]"
                        style="font-family: 'JetBrains Mono', monospace;"
                        data-count-up
                        data-target="{{ $activeInstructors }}"
                        data-decimals="0">
                            0
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#44576D]/12 text-[#44576D]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Pending Payments --}}
            <div class="rounded-[22px] border border-[#F2C8C8] bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#768A96]"
                        style="font-family: 'Inter', sans-serif;">
                            Pending Payments
                        </p>

                        <p class="mt-3 text-2xl font-bold text-[#523D35]"
                        style="font-family: 'JetBrains Mono', monospace;"
                        data-count-up
                        data-target="{{ $pendingPayments }}"
                        data-decimals="0">
                            0
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#DC2626]/10 text-[#DC2626]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Today's Enrollments --}}
            <div class="rounded-[22px] border border-[#D8DDD8] bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#768A96]"
                        style="font-family: 'Inter', sans-serif;">
                            Today's Enrollments
                        </p>

                        <p class="mt-3 text-2xl font-bold text-[#223030]"
                        style="font-family: 'JetBrains Mono', monospace;"
                        data-count-up
                        data-target="{{ $todaysEnrollments }}"
                        data-decimals="0">
                            0
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#2F4F4F]/10 text-[#2F4F4F]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Today's Revenue --}}
            <div class="rounded-[22px] border border-[#D8DDD8] bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#768A96]"
                        style="font-family: 'Inter', sans-serif;">
                            Today's Revenue
                        </p>

                        <p class="mt-3 text-2xl font-bold text-[#223030]"
                        style="font-family: 'JetBrains Mono', monospace;"
                        data-count-up
                        data-target="{{ $todaysRevenue }}"
                        data-prefix="&#8369;"
                        data-decimals="2">
                            &#8369;0.00
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#B4833D]/15 text-[#B4833D]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.66 0-3 .9-3 2s1.34 2 3 2 3 .9 3 2-1.34 2-3 2m0-8c1.11 0 2.08.4 2.6 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.4-2.6-1"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Low Stock Alerts --}}
            <div class="rounded-[22px] border {{ $lowStockAlerts > 0 ? 'border-[#F2C8C8]' : 'border-[#D8DDD8]' }} bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#768A96]"
                        style="font-family: 'Inter', sans-serif;">
                            Low Stock Alerts
                        </p>

                        <p class="mt-3 text-2xl font-bold {{ $lowStockAlerts > 0 ? 'text-[#DC2626]' : 'text-[#223030]' }}"
                        style="font-family: 'JetBrains Mono', monospace;"
                        data-count-up
                        data-target="{{ $lowStockAlerts }}"
                        data-decimals="0">
                            0
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl {{ $lowStockAlerts > 0 ? 'bg-[#DC2626]/10 text-[#DC2626]' : 'bg-[#44576D]/10 text-[#44576D]' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- System Status --}}
            <div class="rounded-[22px] border border-[#D8DDD8] bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#768A96]"
                        style="font-family: 'Inter', sans-serif;">
                            System Status
                        </p>

                        <p class="mt-3 text-xl font-bold text-emerald-700"
                        style="font-family: 'Sora', sans-serif;">
                            Active
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-600/10 text-emerald-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================================
            CHARTS SECTION - Live Data Visualizations (React/JS Mount Points)
            These empty divs are targets for JavaScript chart libraries (Chart.js, Recharts, etc.)
            ============================================================================ --}}
                {{-- Dashboard chart summary cards populated by dashboard-charts.js --}}
        <div id="dashboardChartSummaryCards" class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-[20px] border border-[#D8DDD8] bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">30-Day Enrollments</p>
                <p id="dashboardSummaryEnrollments" class="mt-2 text-xl font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">--</p>
            </div>
            <div class="rounded-[20px] border border-[#D8DDD8] bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">30-Day Revenue</p>
                <p id="dashboardSummaryRevenue" class="mt-2 text-xl font-bold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">--</p>
            </div>
            <div class="rounded-[20px] border border-[#D8DDD8] bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Popular Instrument</p>
                <p id="dashboardSummaryInstrument" class="mt-2 truncate text-xl font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">--</p>
            </div>
            <div class="rounded-[20px] border border-[#D8DDD8] bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Top Instructor</p>
                <p id="dashboardSummaryInstructor" class="mt-2 truncate text-xl font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">--</p>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-2 mb-4 lg:mb-6">
            
            {{-- Enrollment Trend Chart (Line Chart) --}}
            <div class="card p-4 lg:p-6">
                <h3 class="text-base lg:text-lg font-semibold text-secondary-blue mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                    </svg>
                    Enrollment Trend (Last 30 Days)
                </h3>
                <div data-dashboard-chart-wrap class="relative h-56 lg:h-80 rounded-[20px] border border-[#D8DDD8] bg-[#FCFCFA] p-4">
                    <canvas id="dashboardEnrollmentTrendChart" class="h-full w-full"></canvas>
                    <p id="dashboardEnrollmentTrendMessage"
                       class="absolute inset-x-0 top-1/2 -translate-y-1/2 text-center text-sm font-medium text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                        Loading chart...
                    </p>
                </div>
            </div>

            {{-- Revenue Chart (Bar Chart) --}}
            <div class="card p-4 lg:p-6">
                <h3 class="text-base lg:text-lg font-semibold text-secondary-blue mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Weekly Revenue (Last 30 Days)
                </h3>
                <div data-dashboard-chart-wrap class="relative h-56 lg:h-80 rounded-[20px] border border-[#D8DDD8] bg-[#FCFCFA] p-4">
                    <canvas id="dashboardRevenueChart" class="h-full w-full"></canvas>
                    <p id="dashboardRevenueMessage"
                       class="absolute inset-x-0 top-1/2 -translate-y-1/2 text-center text-sm font-medium text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                        Loading chart...
                    </p>
                </div>
            </div>

            {{-- Instrument Popularity Chart (Pie Chart) --}}
            <div class="card p-4 lg:p-6">
                <h3 class="text-base lg:text-lg font-semibold text-secondary-blue mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                    Instrument Popularity
                </h3>
                <div data-dashboard-chart-wrap class="relative h-56 lg:h-80 rounded-[20px] border border-[#D8DDD8] bg-[#FCFCFA] p-4">
                    <canvas id="dashboardInstrumentChart" class="h-full w-full"></canvas>
                    <p id="dashboardInstrumentMessage"
                       class="absolute inset-x-0 top-1/2 -translate-y-1/2 text-center text-sm font-medium text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                        Loading chart...
                    </p>
                </div>
            </div>

            {{-- Instructor Performance Chart (Bar Chart) --}}
            <div class="card p-4 lg:p-6">
                <h3 class="text-base lg:text-lg font-semibold text-secondary-blue mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Top 10 Instructors (Students Taught)
                </h3>
                <div data-dashboard-chart-wrap class="relative h-56 lg:h-80 rounded-[20px] border border-[#D8DDD8] bg-[#FCFCFA] p-4">
                    <canvas id="dashboardInstructorChart" class="h-full w-full"></canvas>
                    <p id="dashboardInstructorMessage"
                       class="absolute inset-x-0 top-1/2 -translate-y-1/2 text-center text-sm font-medium text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                        Loading chart...
                    </p>
                </div>
            </div>
        </div>

        {{-- ============================================================================
            TODAY'S ACTIVITY PANEL
            ============================================================================ --}}
        <div class="grid grid-cols-1 gap-3 lg:gap-4 mb-4 lg:mb-6">
            
            {{-- Today's Schedule --}}
            <div class="card p-4 lg:p-6">
                <h3 class="text-base lg:text-lg font-semibold text-secondary-blue mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Today's Schedule
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-accent-yellow bg-opacity-20 text-xs uppercase">
                            <tr>
                                <th class="px-3 py-2">Time</th>
                                <th class="px-3 py-2">Student</th>
                                <th class="px-3 py-2 hidden sm:table-cell">Instructor</th>
                                <th class="px-3 py-2 hidden md:table-cell">Room</th>
                                <th class="px-3 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs lg:text-sm">
                            @forelse ($todaysSchedule as $schedule)
                                <tr class="border-b hover:bg-accent-yellow hover:bg-opacity-10 transition-colors">
                                    <td class="px-3 py-3 font-medium whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - 
                                        {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                    </td>
                                    <td class="px-3 py-3">{{ $schedule->student_name }}</td>
                                    <td class="px-3 py-3 hidden sm:table-cell">{{ $schedule->instructor_name ?? 'N/A' }}</td>
                                    <td class="px-3 py-3 hidden md:table-cell">{{ $schedule->room_number }}</td>
                                    <td class="px-3 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                            @if($schedule->status === 'completed') bg-green-100 text-green-800
                                            @elseif($schedule->status === 'in_progress') bg-purple-100 text-purple-800
                                            @elseif($schedule->status === 'scheduled') bg-blue-100 text-blue-800
                                            @elseif($schedule->status === 'cancelled') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $schedule->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-8 text-center text-secondary-blue">
                                        <svg class="w-12 h-12 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="font-medium">No lessons scheduled for today</p>
                                        <p class="text-xs mt-1 opacity-70">Schedule will appear here when available</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ============================================================================
            RECENT ACTIVITY FEED
            ============================================================================ --}}
        <div class="card p-4 lg:p-6 mb-6 lg:mb-8">
            <h3 class="text-base lg:text-lg font-semibold text-secondary-blue mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Recent Activity
            </h3>
            {{-- Recent Enrollments --}}
            <div class="border-l-4 border-forest-green pl-4">
                <h4 class="font-semibold text-primary-dark mb-3 text-sm">Recent Enrollments</h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                    @forelse ($recentEnrollments as $enrollment)
                    <div class="text-xs p-2 bg-accent-yellow bg-opacity-10 rounded">
                        <p class="font-medium text-primary-dark">{{ $enrollment->student_name }}</p>
                        <p class="text-secondary-blue text-xs">ID: {{ $enrollment->enrollment_id }}</p>
                        <p class="text-secondary-blue text-xs">{{ \Carbon\Carbon::parse($enrollment->created_at)->diffForHumans() }}</p>
                    </div>
                    @empty
                        <p class="text-xs text-secondary-blue italic">No recent enrollments</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ============================================================================
            QUICK ACTION BUTTONS
            ============================================================================ --}}
        <div class="card p-4 lg:p-6">
            <h3 class="text-base lg:text-lg font-semibold text-secondary-blue mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Quick Actions
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 lg:gap-4">
                
                {{-- Add New User Button --}}
                <a href="{{ route('admin.users.create') }}" class="btn-primary py-3 text-center text-sm hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Add New User
                </a>

                {{-- User Management Button --}}
                <a href="{{ route('admin.users.index') }}" class="btn-secondary py-3 text-center text-sm hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    User Management
                </a>

                {{-- Schedule Management Button --}}
                <a href="{{ route('admin.schedules.index') }}" class="btn-secondary py-3 text-center text-sm hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Schedule
                </a>

                {{-- Lessons Button --}}
                <a href="{{ route('admin.lessons.index') }}" class="btn-secondary py-3 text-center text-sm hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Lessons
                </a>

                {{-- View Reports Button --}}
                <a href="{{ route('admin.reports.index') }}" class="btn-secondary py-3 text-center text-sm hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    View Reports
                </a>

                {{-- Inventory Dashboard Button --}}
                <a href="{{ route('admin.inventory.index') }}" class="btn-secondary py-3 text-center text-sm hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Inventory
                </a>

                {{-- Settings Button --}}
                <a href="{{ route('admin.settings.index') }}" class="btn-secondary py-3 text-center text-sm hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Settings
                </a>
            </div>
        </div>

    </div>
    {{-- Python-powered Student Risk Summary --}}
    @include('admin.partials.student-risk-dashboard-widget')

    {{-- ============================================================================
        FOOTER
        ============================================================================ --}}
    <footer class="bg-white border-t border-gray-200 py-4 text-center mt-8">
        <p class="text-xs text-gray-500">Ãƒâ€šÃ‚&copy; {{ date('Y') }} Music Lab. All rights reserved.</p>
    </footer>

</main>

{{-- Account Settings Modal --}}
<div id="account-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
    <div class="bg-white rounded-lg p-4 max-w-sm w-full mx-4">
        <h3 class="text-base font-bold text-primary-dark mb-3">Change Password</h3>
        <form id="password-form" onsubmit="event.preventDefault(); changePassword(event); return false;">
            <div class="mb-3">
                <label class="block text-xs font-medium text-gray-700 mb-1">Current Password</label>
                <input type="password" id="current-password" name="current_password" required class="input-field text-sm py-1.5">
            </div>
            <div class="mb-3">
                <label class="block text-xs font-medium text-gray-700 mb-1">New Password</label>
                <input type="password" id="new-password" name="password" required class="input-field text-sm py-1.5" minlength="8">
            </div>
            <div class="mb-3">
                <label class="block text-xs font-medium text-gray-700 mb-1">Confirm Password</label>
                <input type="password" id="confirm-password" name="password_confirmation" required class="input-field text-sm py-1.5">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary flex-1 text-sm py-1.5">Save</button>
                <button type="button" onclick="closeAccountModal()" class="btn-secondary flex-1 text-sm py-1.5">Cancel</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
