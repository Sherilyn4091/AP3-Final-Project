{{-- resources/views/emerging-tech/pitch-monitor/history.blade.php --}}
@extends('layouts.student')

@section('pageTitle', 'Pitch Monitor History')

@section('content')
@php
    /*
    |--------------------------------------------------------------------------
    | Safe Defaults
    |--------------------------------------------------------------------------
    |
    | These defaults prevent undefined variable errors if the view is loaded
    | before the updated controller is pasted.
    |
    */
    $selectedDate = $selectedDate ?? request('date');

    $stats = $stats ?? [
        'total_sessions' => $sessions->total(),
        'total_events' => 0,
        'total_seconds' => 0,
        'total_duration_label' => '0m',
        'avg_accuracy' => 0,
        'best_accuracy' => 0,
        'avg_confidence' => 0,
    ];
@endphp

<div class="min-h-screen bg-[#f8f7f4] px-4 py-6 sm:px-6 sm:py-8">
    <div class="mx-auto max-w-6xl">

        {{-- ============================================================= --}}
        {{-- PAGE HEADER --}}
        {{-- ============================================================= --}}
        <header class="relative z-50 mb-8 animate-fadeIn">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="min-w-0">
                    <h1 class="text-3xl font-bold text-[#223030] sm:text-4xl" style="font-family: 'Sora', sans-serif;">
                        Pitch Monitor History
                    </h1>

                    <p class="mt-2 text-sm text-[#44576D] sm:text-base" style="font-family: 'Inter', sans-serif;">
                        Review previous pitch extraction sessions and captured note events.
                    </p>
                </div>

                {{--
                |--------------------------------------------------------------------------
                | History Header Actions
                |--------------------------------------------------------------------------
                |
                | Purpose:
                | - Keeps Switch History and Back button horizontally aligned.
                | - Uses a floating dropdown so history cards do not move when opened.
                | - Keeps the controls responsive across smaller screens.
                |
                --}}
                <div class="relative z-[80] flex flex-row flex-nowrap items-start justify-start gap-2 md:justify-end">
                    {{-- Switch between histories --}}
                    <div class="relative shrink-0">
                        <details class="group">
                            <summary class="list-none cursor-pointer rounded-2xl border border-[#D8DDD8] bg-white px-3 py-2 text-xs font-semibold text-[#223030] transition hover:border-[#768A96] hover:bg-[#F4F5F2] sm:px-4 sm:text-sm [&::-webkit-details-marker]:hidden"
                                     style="font-family: 'Inter', sans-serif;">
                                Switch History
                            </summary>

                            {{--
                                Floating dropdown

                                Important:
                                - absolute prevents layout shift.
                                - z-index keeps the dropdown above cards.
                                - group-hover supports hover preview.
                                - group-open supports click/tap behavior.
                            --}}
                            <div class="invisible pointer-events-none absolute right-0 top-full z-[90] mt-2 w-64 overflow-hidden rounded-2xl border border-[#D8DDD8] bg-white opacity-0 shadow-xl transition-all duration-150
                                        group-hover:visible group-hover:pointer-events-auto group-hover:opacity-100
                                        group-open:visible group-open:pointer-events-auto group-open:opacity-100">
                                <a href="{{ route('student.guitar.history') }}"
                                   class="block px-4 py-3 text-sm text-[#223030] transition hover:bg-[#F4F5F2]"
                                   style="font-family: 'Inter', sans-serif;">
                                    String Pitch Detection History
                                </a>

                                <span class="block bg-[#F4F5F2] px-4 py-3 text-sm font-semibold text-[#223030]"
                                      style="font-family: 'Inter', sans-serif;">
                                    Pitch Monitor History
                                </span>
                            </div>
                        </details>
                    </div>

                    <a href="{{ route('student.pitch-monitor.index') }}"
                       class="shrink-0 whitespace-nowrap rounded-2xl border border-[#D8DDD8] bg-white px-3 py-2 text-xs font-semibold text-[#223030] transition hover:border-[#768A96] hover:bg-[#F4F5F2] sm:px-4 sm:text-sm"
                       style="font-family: 'Inter', sans-serif;">
                        Back to Pitch Monitor
                    </a>
                </div>
            </div>
        </header>

        {{-- ============================================================= --}}
        {{-- ANALYTICS SUMMARY --}}
        {{-- ============================================================= --}}
        <section class="mb-8 animate-slideUp" style="animation-delay: 0.1s;">
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                <div class="rounded-[20px] border border-[#D8DDD8] bg-white p-4 text-center transition-all duration-300 hover:-translate-y-1 hover:border-[#768A96] hover:shadow-md sm:p-5">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                        Sessions
                    </p>
                    <p class="text-2xl font-bold text-[#223030] sm:text-3xl" style="font-family: 'Sora', sans-serif;">
                        {{ $stats['total_sessions'] }}
                    </p>
                </div>

                <div class="rounded-[20px] border border-[#D8DDD8] bg-white p-4 text-center transition-all duration-300 hover:-translate-y-1 hover:border-[#959D90] hover:shadow-md sm:p-5">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                        Monitor Time
                    </p>
                    <p class="text-2xl font-bold text-[#44576D] sm:text-3xl" style="font-family: 'Sora', sans-serif;">
                        {{ $stats['total_duration_label'] }}
                    </p>
                </div>

                <div class="rounded-[20px] border border-[#D8DDD8] bg-white p-4 text-center transition-all duration-300 hover:-translate-y-1 hover:border-[#768A96] hover:shadow-md sm:p-5">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                        Avg Accuracy
                    </p>
                    <p class="text-2xl font-bold text-[#44576D] sm:text-3xl" style="font-family: 'Sora', sans-serif;">
                        {{ $stats['avg_accuracy'] }}%
                    </p>
                </div>

                <div class="rounded-[20px] border border-[#D8DDD8] bg-white p-4 text-center transition-all duration-300 hover:-translate-y-1 hover:border-[#523D35] hover:shadow-md sm:p-5">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                        Best Accuracy
                    </p>
                    <p class="text-2xl font-bold text-[#523D35] sm:text-3xl" style="font-family: 'Sora', sans-serif;">
                        {{ $stats['best_accuracy'] }}%
                    </p>
                </div>
            </div>
        </section>

        {{-- ============================================================= --}}
        {{-- FILTER + BULK ACTIONS --}}
        {{-- ============================================================= --}}
        <section class="mb-6 animate-slideUp" style="animation-delay: 0.2s;">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                {{-- Date filter --}}
                <form id="dateFilterForm"
                      action="{{ route('student.pitch-monitor.history') }}"
                      method="GET"
                      class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <label for="dateFilter"
                           class="text-xs font-semibold uppercase tracking-wide text-[#768A96]"
                           style="font-family: 'Inter', sans-serif;">
                        Filter by Date:
                    </label>

                    <div class="flex flex-wrap gap-2">
                        <input type="date"
                               id="dateFilter"
                               name="date"
                               value="{{ $selectedDate }}"
                               class="rounded-2xl border border-[#D8DDD8] bg-white px-3 py-2 text-sm text-[#223030] transition-colors focus:border-[#768A96] focus:outline-none"
                               style="font-family: 'JetBrains Mono', monospace;">

                        <a href="{{ route('student.pitch-monitor.history') }}"
                           id="clearDateFilter"
                           class="rounded-2xl border border-[#D8DDD8] bg-white px-4 py-2 text-sm font-semibold text-[#223030] transition-all duration-300 hover:border-[#768A96] hover:bg-[#F4F5F2]"
                           style="font-family: 'Inter', sans-serif;">
                            Reset
                        </a>
                    </div>
                </form>

                {{-- Selection controls --}}
                <div class="flex flex-wrap gap-2">
                    <button id="compareBtn"
                            type="button"
                            disabled
                            class="rounded-2xl border border-[#D8DDD8] bg-white px-4 py-2 text-sm font-semibold text-[#223030] transition-all duration-300 hover:-translate-y-1 hover:border-[#768A96] hover:bg-[#F4F5F2] focus:outline-none focus:ring-2 focus:ring-[#768A96] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0"
                            style="font-family: 'Inter', sans-serif;">
                        Compare (0 selected)
                    </button>

                    <button id="bulkDeleteBtn"
                            type="button"
                            disabled
                            class="rounded-2xl border border-[#D8DDD8] bg-white px-4 py-2 text-sm font-semibold text-[#523D35] transition-all duration-300 hover:-translate-y-1 hover:border-[#523D35] hover:bg-[#F6EFEC] focus:outline-none focus:ring-2 focus:ring-[#523D35] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0"
                            style="font-family: 'Inter', sans-serif;">
                        Delete Selected
                    </button>

                    <button id="clearSelectionBtn"
                            type="button"
                            disabled
                            class="rounded-2xl border border-[#D8DDD8] bg-white px-4 py-2 text-sm font-semibold text-[#44576D] transition-all duration-300 hover:-translate-y-1 hover:border-[#768A96] hover:bg-[#F4F5F2] focus:outline-none focus:ring-2 focus:ring-[#768A96] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0"
                            style="font-family: 'Inter', sans-serif;">
                        Clear Selection
                    </button>
                </div>
            </div>

            <p id="selectionHelpText"
               class="mt-3 text-xs text-[#768A96]"
               style="font-family: 'Inter', sans-serif;">
                Select 2 to 3 sessions to compare. Select at least 1 session to delete.
            </p>
        </section>

        {{-- ============================================================= --}}
        {{-- COMPARISON MODAL --}}
        {{-- ============================================================= --}}
        <div id="comparisonModal"
             class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/40 p-4">
            <div class="modal-panel max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-[24px] border border-[#D8DDD8] bg-white shadow-2xl">
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-[#D8DDD8] bg-[#FCFCFA] px-5 py-4">
                    <div>
                        <h2 class="text-xl font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                            Pitch Monitor Session Comparison
                        </h2>
                        <p class="mt-1 text-xs text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                            Compare 2 to 3 selected monitoring sessions.
                        </p>
                    </div>

                    <button id="closeComparison"
                            type="button"
                            class="rounded-2xl border border-[#D8DDD8] bg-white px-4 py-2 text-sm font-semibold text-[#223030] transition hover:border-[#768A96] hover:bg-[#F4F5F2]"
                            style="font-family: 'Inter', sans-serif;">
                        Close
                    </button>
                </div>

                <div id="comparisonContent" class="p-5"></div>
            </div>
        </div>

        {{-- ============================================================= --}}
        {{-- SESSION LIST --}}
        {{-- ============================================================= --}}
        <section id="sessionsList" class="space-y-4 animate-slideUp" style="animation-delay: 0.3s;">
            @forelse($sessions as $session)
                @php
                    $events = $session->events;
                    $totalEvents = $events->count();
                    $inTuneEvents = $events->where('tuning_status', 'in_tune')->count();
                    $accuracy = $totalEvents > 0 ? round(($inTuneEvents / $totalEvents) * 100) : 0;

                    $duration = $session->duration_seconds ?? 0;
                    $durationMinutes = intdiv($duration, 60);
                    $durationSeconds = $duration % 60;

                    $avgConfidence = $totalEvents > 0 ? round(($events->avg('confidence') ?? 0) * 100, 1) : 0;
                    $avgRms = $totalEvents > 0 ? round(($events->avg('rms') ?? 0), 4) : 0;

                    $mostDetectedNote = $totalEvents > 0
                        ? ($events->groupBy('note_name')->sortByDesc(fn ($group) => $group->count())->keys()->first() ?? '--')
                        : '--';
                @endphp

                <div class="hist-card overflow-hidden rounded-[22px] border border-[#D8DDD8] bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-[#768A96] hover:shadow-md"
                     data-session-id="{{ $session->session_id }}"
                     data-date="{{ $session->started_at->format('Y-m-d') }}"
                     data-display-date="{{ $session->started_at->format('M d, Y') }}"
                     data-duration="{{ $duration }}"
                     data-accuracy="{{ $accuracy }}"
                     data-confidence="{{ $avgConfidence }}"
                     data-rms="{{ $avgRms }}"
                     data-event-count="{{ $totalEvents }}"
                     data-most-note="{{ $mostDetectedNote }}">
                    <div class="border-b border-[#D8DDD8] bg-[#FCFCFA] px-4 py-4 sm:px-5">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0 flex-1">
                                <h2 class="text-lg font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                                    {{ $session->started_at->format('l, F d, Y') }}
                                </h2>

                                <p class="mt-1 text-sm text-[#768A96]" style="font-family: 'JetBrains Mono', monospace;">
                                    {{ $session->started_at->format('H:i:s') }}
                                    @if($session->ended_at)
                                        â€” {{ $session->ended_at->format('H:i:s') }}
                                    @endif
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @if($session->duration_seconds !== null)
                                    <span class="rounded-full bg-[#EEF2F4] px-3 py-1 text-xs font-bold text-[#44576D]" style="font-family: 'Inter', sans-serif;">
                                        {{ $durationMinutes }}m {{ $durationSeconds }}s
                                    </span>
                                @else
                                    <span class="rounded-full bg-[#F6EFEC] px-3 py-1 text-xs font-bold text-[#523D35]" style="font-family: 'Inter', sans-serif;">
                                        Incomplete
                                    </span>
                                @endif

                                <span class="rounded-full px-3 py-1 text-xs font-bold
                                    {{ $accuracy >= 80 ? 'bg-[#F1F3EF] text-[#223030]' : ($accuracy >= 50 ? 'bg-[#EEF2F4] text-[#44576D]' : 'bg-[#F6EFEC] text-[#523D35]') }}"
                                    style="font-family: 'Inter', sans-serif;">
                                    {{ $accuracy }}% in tune
                                </span>

                                <span class="rounded-full bg-[#F1F3EF] px-3 py-1 text-xs font-bold text-[#223030]" style="font-family: 'Inter', sans-serif;">
                                    {{ $totalEvents }} events
                                </span>

                                <label class="inline-flex cursor-pointer items-center gap-2 rounded-2xl border border-[#D8DDD8] bg-white px-3 py-2 text-xs font-semibold text-[#223030] transition hover:border-[#768A96] hover:bg-[#F4F5F2]"
                                       style="font-family: 'Inter', sans-serif;">
                                    <input type="checkbox"
                                           class="session-checkbox h-4 w-4 rounded border-[#D8DDD8]"
                                           data-session-id="{{ $session->session_id }}">
                                    Select
                                </label>

                                <button type="button"
                                        class="delete-pitch-session rounded-2xl bg-[#F6EFEC] px-3 py-2 text-xs font-bold text-[#523D35] transition hover:bg-[#EFE3DE]"
                                        data-session-id="{{ $session->session_id }}"
                                        style="font-family: 'Inter', sans-serif;">
                                    Delete
                                </button>
                            </div>
                        </div>

                        {{-- Compact session metrics --}}
                        <div class="mt-4 grid grid-cols-2 gap-2 md:grid-cols-4">
                            <div class="rounded-2xl border border-[#EEF1EC] bg-white px-3 py-2">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                                    Avg Confidence
                                </p>
                                <p class="mt-1 text-sm font-bold text-[#223030]" style="font-family: 'JetBrains Mono', monospace;">
                                    {{ number_format($avgConfidence, 1) }}%
                                </p>
                            </div>

                            <div class="rounded-2xl border border-[#EEF1EC] bg-white px-3 py-2">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                                    Avg RMS
                                </p>
                                <p class="mt-1 text-sm font-bold text-[#223030]" style="font-family: 'JetBrains Mono', monospace;">
                                    {{ number_format($avgRms, 4) }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-[#EEF1EC] bg-white px-3 py-2">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                                    Most Note
                                </p>
                                <p class="mt-1 text-sm font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                                    {{ $mostDetectedNote }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-[#EEF1EC] bg-white px-3 py-2">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                                    Source
                                </p>
                                <p class="mt-1 text-sm font-bold text-[#223030]" style="font-family: 'Inter', sans-serif;">
                                    {{ ucfirst(str_replace('_', ' ', $session->source_type ?? 'microphone')) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto px-4 py-4 sm:px-5">
                        @if($events->count())
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-[#D8DDD8]">
                                        <th class="px-2 py-2 text-left text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Note</th>
                                        <th class="px-2 py-2 text-left text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Frequency</th>
                                        <th class="px-2 py-2 text-left text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Cents</th>
                                        <th class="px-2 py-2 text-left text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Confidence</th>
                                        <th class="px-2 py-2 text-left text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">RMS</th>
                                        <th class="px-2 py-2 text-left text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Status</th>
                                        <th class="px-2 py-2 text-left text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: 'Inter', sans-serif;">Time</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-[#EEF1EC]">
                                    @foreach($events as $event)
                                        <tr class="transition hover:bg-[#FCFCFA]">
                                            <td class="px-2 py-3 font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                                                {{ $event->note_name }}
                                            </td>

                                            <td class="px-2 py-3 text-[#44576D]" style="font-family: 'JetBrains Mono', monospace;">
                                                {{ number_format($event->frequency, 2) }} Hz
                                            </td>

                                            <td class="px-2 py-3 text-[#223030]" style="font-family: 'JetBrains Mono', monospace;">
                                                {{ $event->cents_deviation >= 0 ? '+' : '' }}{{ number_format($event->cents_deviation, 2) }} Â¢
                                            </td>

                                            <td class="px-2 py-3 text-[#223030]" style="font-family: 'JetBrains Mono', monospace;">
                                                {{ number_format(($event->confidence ?? 0) * 100, 1) }}%
                                            </td>

                                            <td class="px-2 py-3 text-[#223030]" style="font-family: 'JetBrains Mono', monospace;">
                                                {{ number_format($event->rms ?? 0, 4) }}
                                            </td>

                                            <td class="px-2 py-3">
                                                <span class="rounded-full px-2 py-1 text-xs font-bold
                                                    {{ $event->tuning_status === 'in_tune' ? 'bg-[#F1F3EF] text-[#223030]' : ($event->tuning_status === 'flat' ? 'bg-[#EEF2F4] text-[#44576D]' : 'bg-[#F6EFEC] text-[#523D35]') }}"
                                                    style="font-family: 'Inter', sans-serif;">
                                                    {{ ucfirst(str_replace('_', ' ', $event->tuning_status ?? 'detected')) }}
                                                </span>
                                            </td>

                                            <td class="px-2 py-3 text-[#768A96]" style="font-family: 'JetBrains Mono', monospace;">
                                                {{ $event->detected_at->format('H:i:s') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="rounded-2xl bg-[#FCFCFA] px-4 py-5 text-center">
                                <p class="text-sm text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                                    No pitch events were saved in this session.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-[26px] border border-[#D8DDD8] bg-white px-6 py-12 text-center shadow-sm">
                    <h2 class="text-2xl font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                        {{ $selectedDate ? 'No sessions found for this date' : 'No pitch monitor sessions yet' }}
                    </h2>

                    <p class="mt-2 text-sm text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                        {{ $selectedDate ? 'Reset the date filter to view all Pitch Monitor sessions.' : 'Start using Pitch Monitor to build your session history.' }}
                    </p>

                    <a href="{{ $selectedDate ? route('student.pitch-monitor.history') : route('student.pitch-monitor.index') }}"
                       class="mt-5 inline-block rounded-2xl bg-[#223030] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#29353C]"
                       style="font-family: 'Inter', sans-serif;">
                        {{ $selectedDate ? 'Reset Filter' : 'Open Pitch Monitor' }}
                    </a>
                </div>
            @endforelse
        </section>

        {{-- ============================================================= --}}
        {{-- PAGINATION --}}
        {{-- ============================================================= --}}
        @if($sessions->hasPages())
            <div class="mt-6 animate-fadeIn">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&family=Sora:wght@400;600;700;800&display=swap');

@keyframes fadeIn {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes modalScale {
    from {
        opacity: 0;
        transform: scale(0.96) translateY(8px);
    }

    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.45s ease-out both;
}

.animate-slideUp {
    animation: slideUp 0.5s ease-out both;
}

.hist-card {
    animation: slideUp 0.45s ease-out both;
}

.hist-card--removing {
    opacity: 0;
    transform: translateX(16px) scale(0.98);
    pointer-events: none;
}

.modal-panel {
    animation: modalScale 0.22s ease-out both;
}

.session-checkbox:checked {
    accent-color: #44576D;
}

@media (prefers-reduced-motion: reduce) {
    .animate-fadeIn,
    .animate-slideUp,
    .hist-card,
    .modal-panel {
        animation: none !important;
    }

    .hist-card,
    .hist-card--removing {
        transform: none !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    /*
    |--------------------------------------------------------------------------
    | Pitch Monitor History Config
    |--------------------------------------------------------------------------
    |
    | Purpose:
    | - Keeps Laravel route values inside Blade.
    | - Keeps all JavaScript logic reusable and readable.
    |
    */
    const historyConfig = {
        csrfToken: @json(csrf_token()),
        deleteUrlTemplate: @json(url('/student/pitch-monitor/session/__SESSION_ID__/delete')),
    };

    const filterForm = document.getElementById('dateFilterForm');
    const dateFilter = document.getElementById('dateFilter');
    const compareBtn = document.getElementById('compareBtn');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const clearSelectionBtn = document.getElementById('clearSelectionBtn');
    const selectionHelpText = document.getElementById('selectionHelpText');
    const comparisonModal = document.getElementById('comparisonModal');
    const closeComparison = document.getElementById('closeComparison');
    const comparisonContent = document.getElementById('comparisonContent');

    /*
    |--------------------------------------------------------------------------
    | Small Helpers
    |--------------------------------------------------------------------------
    |
    | Purpose:
    | - Avoid duplicated logic.
    | - Keep event handlers short and readable.
    |
    */
    function getSelectedCheckboxes() {
        return Array.from(document.querySelectorAll('.session-checkbox:checked'));
    }

    function getSelectedCards() {
        return getSelectedCheckboxes()
            .map(function (checkbox) {
                return checkbox.closest('.hist-card');
            })
            .filter(Boolean);
    }

    function buildDeleteUrl(sessionId) {
        return historyConfig.deleteUrlTemplate.replace('__SESSION_ID__', encodeURIComponent(sessionId));
    }

    function formatDuration(seconds) {
        const safeSeconds = Math.max(0, parseInt(seconds || '0', 10));
        const hours = Math.floor(safeSeconds / 3600);
        const minutes = Math.floor((safeSeconds % 3600) / 60);
        const remainingSeconds = safeSeconds % 60;

        if (hours > 0) {
            return hours + 'h ' + minutes + 'm';
        }

        if (minutes > 0) {
            return minutes + 'm ' + remainingSeconds + 's';
        }

        return remainingSeconds + 's';
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function readSessionFromCard(card) {
        return {
            id: card.dataset.sessionId || '',
            date: card.dataset.displayDate || card.dataset.date || '',
            duration: parseInt(card.dataset.duration || '0', 10),
            accuracy: parseFloat(card.dataset.accuracy || '0'),
            confidence: parseFloat(card.dataset.confidence || '0'),
            rms: parseFloat(card.dataset.rms || '0'),
            eventCount: parseInt(card.dataset.eventCount || '0', 10),
            mostNote: card.dataset.mostNote || '--',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Date Filter
    |--------------------------------------------------------------------------
    |
    | This submits the form automatically when the date changes.
    | The controller handles the actual filtering so pagination stays accurate.
    |
    */
    if (dateFilter && filterForm) {
        dateFilter.addEventListener('change', function () {
            filterForm.classList.add('opacity-70');
            filterForm.submit();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Selection State
    |--------------------------------------------------------------------------
    */
    function updateSelectionControls() {
        const selectedCount = getSelectedCheckboxes().length;

        if (compareBtn) {
            compareBtn.textContent = 'Compare (' + selectedCount + ' selected)';
            compareBtn.disabled = selectedCount < 2 || selectedCount > 3;
        }

        if (bulkDeleteBtn) {
            bulkDeleteBtn.disabled = selectedCount < 1;
        }

        if (clearSelectionBtn) {
            clearSelectionBtn.disabled = selectedCount < 1;
        }

        if (!selectionHelpText) {
            return;
        }

        if (selectedCount === 0) {
            selectionHelpText.textContent = 'Select 2 to 3 sessions to compare. Select at least 1 session to delete.';
        } else if (selectedCount === 1) {
            selectionHelpText.textContent = 'Select at least 2 sessions to compare. Delete Selected is already available.';
        } else if (selectedCount >= 2 && selectedCount <= 3) {
            selectionHelpText.textContent = 'Ready to compare selected sessions. You can also delete the selected sessions.';
        } else {
            selectionHelpText.textContent = 'More than 3 selected. Comparison is limited to 3 sessions, but bulk delete is available.';
        }
    }

    document.querySelectorAll('.session-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', updateSelectionControls);
    });

    if (clearSelectionBtn) {
        clearSelectionBtn.addEventListener('click', function () {
            getSelectedCheckboxes().forEach(function (checkbox) {
                checkbox.checked = false;
            });

            updateSelectionControls();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Compare Selected Sessions
    |--------------------------------------------------------------------------
    */
    function renderComparison(sessions) {
        let html = '<div class="space-y-6">';

        html += '<div>';
        html += '<h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-[#768A96]" style="font-family: Inter, sans-serif;">Accuracy Overview</h3>';
        html += '<div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">';

        sessions.forEach(function (session) {
            const accuracy = Math.max(0, Math.min(100, Number(session.accuracy || 0)));
            const dash = (accuracy / 100) * 282.7;

            html += `
                <div class="rounded-[20px] border border-[#D8DDD8] bg-[#FCFCFA] p-4 text-center">
                    <p class="mb-2 text-xs text-[#768A96]" style="font-family: Inter, sans-serif;">${escapeHtml(session.date)}</p>

                    <div class="relative mx-auto mb-3 h-24 w-24">
                        <svg class="h-full w-full -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="45" fill="none" stroke="#D8DDD8" stroke-width="8"></circle>
                            <circle cx="50" cy="50" r="45" fill="none" stroke="#44576D" stroke-width="8"
                                    stroke-dasharray="${dash} 282.7"
                                    stroke-linecap="round"></circle>
                        </svg>

                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-lg font-bold text-[#223030]" style="font-family: Sora, sans-serif;">${accuracy}%</span>
                        </div>
                    </div>

                    <p class="text-xs text-[#768A96]" style="font-family: Inter, sans-serif;">In-tune rate</p>
                </div>
            `;
        });

        html += '</div>';
        html += '</div>';

        html += '<div>';
        html += '<h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-[#768A96]" style="font-family: Inter, sans-serif;">Session Details</h3>';
        html += '<div class="overflow-x-auto rounded-[20px] border border-[#D8DDD8]">';
        html += '<table class="w-full text-sm">';
        html += '<thead class="bg-[#FCFCFA]">';
        html += '<tr class="border-b border-[#D8DDD8]">';
        html += '<th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: Inter, sans-serif;">Date</th>';
        html += '<th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: Inter, sans-serif;">Duration</th>';
        html += '<th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: Inter, sans-serif;">Events</th>';
        html += '<th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: Inter, sans-serif;">Most Note</th>';
        html += '<th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: Inter, sans-serif;">Avg Confidence</th>';
        html += '<th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: Inter, sans-serif;">Avg RMS</th>';
        html += '<th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-[#768A96]" style="font-family: Inter, sans-serif;">Accuracy</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody class="divide-y divide-[#EEF1EC] bg-white">';

        sessions.forEach(function (session) {
            html += `
                <tr>
                    <td class="px-4 py-3 font-semibold text-[#223030]" style="font-family: Inter, sans-serif;">${escapeHtml(session.date)}</td>
                    <td class="px-4 py-3 text-[#44576D]" style="font-family: JetBrains Mono, monospace;">${escapeHtml(formatDuration(session.duration))}</td>
                    <td class="px-4 py-3 text-[#223030]" style="font-family: JetBrains Mono, monospace;">${escapeHtml(session.eventCount)}</td>
                    <td class="px-4 py-3 font-bold text-[#223030]" style="font-family: Sora, sans-serif;">${escapeHtml(session.mostNote)}</td>
                    <td class="px-4 py-3 text-[#223030]" style="font-family: JetBrains Mono, monospace;">${escapeHtml(session.confidence.toFixed(1))}%</td>
                    <td class="px-4 py-3 text-[#223030]" style="font-family: JetBrains Mono, monospace;">${escapeHtml(session.rms.toFixed(4))}</td>
                    <td class="px-4 py-3 font-bold text-[#523D35]" style="font-family: JetBrains Mono, monospace;">${escapeHtml(session.accuracy)}%</td>
                </tr>
            `;
        });

        html += '</tbody>';
        html += '</table>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        comparisonContent.innerHTML = html;
    }

    if (compareBtn) {
        compareBtn.addEventListener('click', function () {
            const selectedCards = getSelectedCards();

            if (selectedCards.length < 2 || selectedCards.length > 3) {
                alert('Select 2 to 3 sessions to compare.');
                return;
            }

            const sessions = selectedCards.map(readSessionFromCard);

            renderComparison(sessions);

            comparisonModal.classList.remove('hidden');
            comparisonModal.classList.add('flex');
        });
    }

    function closeComparisonModal() {
        comparisonModal.classList.add('hidden');
        comparisonModal.classList.remove('flex');

        if (comparisonContent) {
            comparisonContent.innerHTML = '';
        }
    }

    if (closeComparison) {
        closeComparison.addEventListener('click', closeComparisonModal);
    }

    if (comparisonModal) {
        comparisonModal.addEventListener('click', function (event) {
            if (event.target === comparisonModal) {
                closeComparisonModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && comparisonModal && !comparisonModal.classList.contains('hidden')) {
            closeComparisonModal();
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Delete Sessions
    |--------------------------------------------------------------------------
    |
    | Supports:
    | - single session delete
    | - bulk delete
    |
    */
    async function deleteSessionById(sessionId) {
        const response = await fetch(buildDeleteUrl(sessionId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': historyConfig.csrfToken,
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Delete failed for session ' + sessionId);
        }
    }

    async function deleteSessions(sessionIds, confirmationMessage) {
        if (!sessionIds.length) {
            alert('Select at least one session to delete.');
            return;
        }

        if (!confirm(confirmationMessage)) {
            return;
        }

        const originalBulkText = bulkDeleteBtn ? bulkDeleteBtn.textContent : '';

        if (bulkDeleteBtn) {
            bulkDeleteBtn.disabled = true;
            bulkDeleteBtn.textContent = 'Deleting...';
        }

        let failedCount = 0;

        for (const sessionId of sessionIds) {
            try {
                await deleteSessionById(sessionId);

                const card = document.querySelector('.hist-card[data-session-id="' + sessionId + '"]');

                if (card) {
                    card.classList.add('hist-card--removing');
                }
            } catch (error) {
                failedCount++;
                console.error(error);
            }
        }

        setTimeout(function () {
            if (failedCount > 0) {
                alert('Some sessions could not be deleted. The page will refresh to show the latest data.');
            }

            window.location.reload();
        }, 350);

        if (bulkDeleteBtn) {
            bulkDeleteBtn.textContent = originalBulkText || 'Delete Selected';
        }
    }

    document.querySelectorAll('.delete-pitch-session').forEach(function (button) {
        button.addEventListener('click', function () {
            const sessionId = this.dataset.sessionId;

            deleteSessions(
                [sessionId],
                'Delete this pitch monitor session? This cannot be undone.'
            );
        });
    });

    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function () {
            const sessionIds = getSelectedCheckboxes()
                .map(function (checkbox) {
                    return checkbox.dataset.sessionId;
                })
                .filter(Boolean);

            deleteSessions(
                sessionIds,
                'Delete the selected pitch monitor sessions? This cannot be undone.'
            );
        });
    }

    updateSelectionControls();
})();
</script>
@endpush