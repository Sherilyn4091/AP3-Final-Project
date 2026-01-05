{{-- resources/views/admin/dashboard.blade.php --}}
{{-- 
    ============================================================================
    SUPER ADMIN DASHBOARD - Music Lab
    Main control center for system-wide overview and management
    Updated: January 2026 - Live React/JavaScript chart integration ready
    ============================================================================
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Music Lab</title>
    @vite(['resources/css/style.css', 'resources/js/script.js', 'resources/js/admin.js'])
</head>
<body class="bg-light-gray">

@include('layouts.admin-header')

<main class="lg:ml-64 min-h-screen bg-light-gray">
    
    {{-- Page Header --}}
    <header class="bg-white shadow-sm p-6 lg:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-primary-dark">Welcome, Super Admin</h1>
                <p class="text-secondary-blue mt-1">{{ now()->format('l, F j, Y') }}</p>
            </div>
        </div>
    </header>

    <div class="p-4 lg:p-8">
        
        {{-- ============================================================================
            TOP ROW: USER STATISTICS CARDS
            ============================================================================ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6 lg:mb-8">
            
            {{-- Total Users Card --}}
            <div class="card p-4 lg:p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm lg:text-base font-semibold text-secondary-blue">Total Users</p>
                        <p class="text-2xl lg:text-3xl font-bold text-primary-dark mt-1">{{ number_format($totalUsers) }}</p>
                    </div>
                    <div class="p-3 bg-forest-green bg-opacity-10 rounded-lg">
                        <svg class="w-6 h-6 lg:w-8 lg:h-8 text-forest-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Active Students Card --}}
            <div class="card p-4 lg:p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm lg:text-base font-semibold text-secondary-blue">Active Students</p>
                        <p class="text-2xl lg:text-3xl font-bold text-primary-dark mt-1">{{ number_format($activeStudents) }}</p>
                    </div>
                    <div class="p-3 bg-golden-yellow bg-opacity-10 rounded-lg">
                        <svg class="w-6 h-6 lg:w-8 lg:h-8 text-golden-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Active Instructors Card --}}
            <div class="card p-4 lg:p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm lg:text-base font-semibold text-secondary-blue">Active Instructors</p>
                        <p class="text-2xl lg:text-3xl font-bold text-primary-dark mt-1">{{ number_format($activeInstructors) }}</p>
                    </div>
                    <div class="p-3 bg-warm-coral bg-opacity-10 rounded-lg">
                        <svg class="w-6 h-6 lg:w-8 lg:h-8 text-warm-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Staff Card --}}
            <div class="card p-4 lg:p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm lg:text-base font-semibold text-secondary-blue">Total Staff</p>
                        <p class="text-2xl lg:text-3xl font-bold text-primary-dark mt-1">{{ number_format($totalStaff) }}</p>
                    </div>
                    <div class="p-3 bg-secondary-blue bg-opacity-10 rounded-lg">
                        <svg class="w-6 h-6 lg:w-8 lg:h-8 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================================
            SECOND ROW: TODAY'S ACTIVITY & ALERTS
            ============================================================================ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6 lg:mb-8">
            
            {{-- Today's Enrollments --}}
            <div class="card p-4 lg:p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm lg:text-base font-semibold text-secondary-blue">Today's Enrollments</p>
                        <p class="text-2xl lg:text-3xl font-bold text-primary-dark mt-1">{{ number_format($todaysEnrollments) }}</p>
                    </div>
                    <div class="p-3 bg-forest-green bg-opacity-10 rounded-lg">
                        <svg class="w-6 h-6 lg:w-8 lg:h-8 text-forest-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Today's Revenue --}}
            <div class="card p-4 lg:p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm lg:text-base font-semibold text-secondary-blue">Today's Revenue</p>
                        <p class="text-2xl lg:text-3xl font-bold text-primary-dark mt-1">₱{{ number_format($todaysRevenue, 2) }}</p>
                    </div>
                    <div class="p-3 bg-golden-yellow bg-opacity-10 rounded-lg">
                        <svg class="w-6 h-6 lg:w-8 lg:h-8 text-golden-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Pending Payments --}}
            <div class="card p-4 lg:p-6 hover:shadow-xl transition-shadow duration-300 border-l-4 border-warm-coral">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm lg:text-base font-semibold text-secondary-blue">Pending Payments</p>
                        <p class="text-2xl lg:text-3xl font-bold text-primary-dark mt-1">{{ number_format($pendingPayments) }}</p>
                    </div>
                    <div class="p-3 bg-warm-coral bg-opacity-10 rounded-lg">
                        <svg class="w-6 h-6 lg:w-8 lg:h-8 text-warm-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Low Stock Alerts --}}
            <div class="card p-4 lg:p-6 hover:shadow-xl transition-shadow duration-300 border-l-4 {{ $lowStockAlerts > 0 ? 'border-red-500' : 'border-secondary-blue' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm lg:text-base font-semibold text-secondary-blue">Low Stock Alerts</p>
                        <p class="text-2xl lg:text-3xl font-bold {{ $lowStockAlerts > 0 ? 'text-red-600' : 'text-primary-dark' }} mt-1">{{ number_format($lowStockAlerts) }}</p>
                    </div>
                    <div class="p-3 {{ $lowStockAlerts > 0 ? 'bg-red-100' : 'bg-secondary-blue bg-opacity-10' }} rounded-lg">
                        <svg class="w-6 h-6 lg:w-8 lg:h-8 {{ $lowStockAlerts > 0 ? 'text-red-600' : 'text-secondary-blue' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================================
            CHARTS SECTION - Live Data Visualizations (React/JS Mount Points)
            These empty divs are targets for JavaScript chart libraries (Chart.js, Recharts, etc.)
            ============================================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6 mb-6 lg:mb-8">
            
            {{-- Enrollment Trend Chart (Line Chart) --}}
            <div class="card p-4 lg:p-6">
                <h3 class="text-base lg:text-lg font-semibold text-secondary-blue mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                    </svg>
                    Enrollment Trend (Last 30 Days)
                </h3>
                <div id="enrollment-trend-chart" class="h-64 lg:h-80 bg-gray-50 rounded-lg"></div>
            </div>

            {{-- Revenue Chart (Bar Chart) --}}
            <div class="card p-4 lg:p-6">
                <h3 class="text-base lg:text-lg font-semibold text-secondary-blue mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Weekly Revenue (Last 30 Days)
                </h3>
                <div id="revenue-chart" class="h-64 lg:h-80 bg-gray-50 rounded-lg"></div>
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
                <div id="instrument-popularity-chart" class="h-64 lg:h-80 bg-gray-50 rounded-lg"></div>
            </div>

            {{-- Instructor Performance Chart (Bar Chart) --}}
            <div class="card p-4 lg:p-6">
                <h3 class="text-base lg:text-lg font-semibold text-secondary-blue mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Top 10 Instructors (Students Taught)
                </h3>
                <div id="instructor-performance-chart" class="h-64 lg:h-80 bg-gray-50 rounded-lg"></div>
            </div>
        </div>

        {{-- ============================================================================
            TODAY'S ACTIVITY PANEL
            ============================================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6 mb-6 lg:mb-8">
            
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

            {{-- Today's Bookings --}}
            <div class="card p-4 lg:p-6">
                <h3 class="text-base lg:text-lg font-semibold text-secondary-blue mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Today's Bookings
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-accent-yellow bg-opacity-20 text-xs uppercase">
                            <tr>
                                <th class="px-3 py-2">Time</th>
                                <th class="px-3 py-2 hidden sm:table-cell">Room</th>
                                <th class="px-3 py-2">Customer</th>
                                <th class="px-3 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs lg:text-sm">
                            @forelse ($todaysBookings as $booking)
                                <tr class="border-b hover:bg-accent-yellow hover:bg-opacity-10 transition-colors">
                                    <td class="px-3 py-3 font-medium whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }} - 
                                        {{ \Carbon\Carbon::parse($booking->end_time)->format('g:i A') }}
                                    </td>
                                    <td class="px-3 py-3 hidden sm:table-cell">{{ $booking->room_number }}</td>
                                    <td class="px-3 py-3">{{ $booking->customer }}</td>
                                    <td class="px-3 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                            @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                            @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($booking->status === 'cancelled') bg-red-100 text-red-800
                                            @elseif($booking->status === 'completed') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-8 text-center text-secondary-blue">
                                        <svg class="w-12 h-12 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <p class="font-medium">No bookings for today</p>
                                        <p class="text-xs mt-1 opacity-70">Room bookings will appear here when available</p>
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
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6">
                
                {{-- Recent Enrollments --}}
                <div class="border-l-4 border-forest-green pl-4">
                    <h4 class="font-semibold text-primary-dark mb-3 text-sm">Recent Enrollments</h4>
                    <div class="space-y-2">
                        @forelse ($recentEnrollments as $enrollment)
                            <div class="text-xs lg:text-sm p-2 bg-accent-yellow bg-opacity-10 rounded">
                                <p class="font-medium text-primary-dark">{{ $enrollment->student_name }}</p>
                                <p class="text-secondary-blue text-xs">ID: {{ $enrollment->enrollment_id }}</p>
                                <p class="text-secondary-blue text-xs">{{ \Carbon\Carbon::parse($enrollment->created_at)->diffForHumans() }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-secondary-blue italic">No recent enrollments</p>
                        @endforelse
                    </div>
                </div>

                {{-- Recent Payments --}}
                <div class="border-l-4 border-golden-yellow pl-4">
                    <h4 class="font-semibold text-primary-dark mb-3 text-sm">Recent Payments</h4>
                    <div class="space-y-2">
                        @forelse ($recentPayments as $payment)
                            <div class="text-xs lg:text-sm p-2 bg-accent-yellow bg-opacity-10 rounded">
                                <p class="font-medium text-primary-dark">{{ $payment->student_name }}</p>
                                <p class="text-secondary-blue text-xs">₱{{ number_format($payment->amount, 2) }} - {{ $payment->method_name }}</p>
                                <p class="text-secondary-blue text-xs">{{ \Carbon\Carbon::parse($payment->created_at)->diffForHumans() }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-secondary-blue italic">No recent payments</p>
                        @endforelse
                    </div>
                </div>

                {{-- Recent Reports --}}
                <div class="border-l-4 border-warm-coral pl-4">
                    <h4 class="font-semibold text-primary-dark mb-3 text-sm">Recent Reports</h4>
                    <div class="space-y-2">
                        @forelse ($recentReports as $report)
                            <div class="text-xs lg:text-sm p-2 bg-accent-yellow bg-opacity-10 rounded">
                                <p class="font-medium text-primary-dark">{{ $report->report_title }}</p>
                                <p class="text-secondary-blue text-xs">Type: {{ ucfirst($report->report_type) }}</p>
                                <p class="text-secondary-blue text-xs">{{ \Carbon\Carbon::parse($report->generated_at)->diffForHumans() }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-secondary-blue italic">No recent reports</p>
                        @endforelse
                    </div>
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

                {{-- Financial Reports Button --}}
                <a href="{{ route('admin.reports.financial') }}" class="btn-secondary py-3 text-center text-sm hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Financial Reports
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

    {{-- ============================================================================
        FOOTER
        ============================================================================ --}}
    <footer class="bg-white border-t border-gray-200 py-4 text-center mt-8">
        <p class="text-xs text-gray-500">© {{ date('Y') }} Music Lab. All rights reserved.</p>
    </footer>

</main>

</body>
</html>