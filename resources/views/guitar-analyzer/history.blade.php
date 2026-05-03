{{-- resources/views/guitar-analyzer/history.blade.php --}}
@extends('layouts.student')

@section('title', 'String Pitch Detection History')
@section('pageTitle', 'String Pitch Detection History')

@section('content')
<div class="min-h-screen bg-[#f8f7f4] py-8 px-4">
    <div class="max-w-4xl mx-auto">

        {{-- PAGE HEADER --}}
        <header class="relative z-50 mb-8 animate-fadeIn">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <h1 class="text-3xl sm:text-4xl font-bold text-[#223030] mb-2" style="font-family: 'Sora', sans-serif;">
                        String Pitch Detection History
                    </h1>
                    <p class="text-sm text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                        Review your Sound Check sessions by default, then switch to Pitch Monitor History when needed.
                    </p>
                </div>

                {{--
                |--------------------------------------------------------------------------
                | History Header Actions
                |--------------------------------------------------------------------------
                |
                | Purpose:
                | - Keeps the Switch History and Back button horizontally aligned.
                | - Uses a floating dropdown so history cards do not move when opened.
                | - Keeps the controls responsive by using smaller spacing on small screens.
                | - Avoids duplicated dropdown logic by keeping all history switching in one
                |   small, focused header section.
                |
                --}}
                <div class="relative z-[80] flex flex-row flex-nowrap items-start justify-start gap-2 sm:justify-end">
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
                                - absolute keeps the dropdown floating above cards.
                                - group-hover also shows the menu on hover.
                                - group-open keeps it visible after clicking the summary.
                            --}}
                            <div class="invisible pointer-events-none absolute right-0 top-full z-[90] mt-2 w-64 overflow-hidden rounded-2xl border border-[#D8DDD8] bg-white opacity-0 shadow-xl transition-all duration-150
                                        group-hover:visible group-hover:pointer-events-auto group-hover:opacity-100
                                        group-open:visible group-open:pointer-events-auto group-open:opacity-100">
                                <span class="block bg-[#F4F5F2] px-4 py-3 text-sm font-semibold text-[#223030]"
                                      style="font-family: 'Inter', sans-serif;">
                                    String Pitch Detection History
                                </span>

                                <a href="{{ route('student.pitch-monitor.history') }}"
                                   class="block px-4 py-3 text-sm text-[#223030] transition hover:bg-[#F4F5F2]"
                                   style="font-family: 'Inter', sans-serif;">
                                    Pitch Monitor History
                                </a>
                            </div>
                        </details>
                    </div>

                    <a href="{{ route('student.guitar.index') }}"
                       class="shrink-0 whitespace-nowrap rounded-2xl border border-[#D8DDD8] bg-white px-3 py-2 text-xs font-semibold text-[#223030] transition hover:border-[#768A96] hover:bg-[#F4F5F2] sm:px-4 sm:text-sm"
                       style="font-family: 'Inter', sans-serif;">
                        Back to Sound Check
                    </a>
                </div>
            </div>
        </header>

        {{-- ANALYTICS SUMMARY --}}
        @php
            $visibleSessions = $sessions->count();
            $totalPracticeTime = 0;
            $accuracyTotal = 0;
            $bestAccuracy = 0;
            $accuracySessions = 0;

            foreach ($sessions as $session) {
                if ($session->duration_seconds) {
                    $totalPracticeTime += $session->duration_seconds;
                }

                $eventTotal = $session->noteEvents->count();
                if ($eventTotal > 0) {
                    $inTune = $session->noteEvents->where('tuning_status', 'in_tune')->count();
                    $accuracy = round(($inTune / $eventTotal) * 100);
                    $accuracyTotal += $accuracy;
                    $bestAccuracy = max($bestAccuracy, $accuracy);
                    $accuracySessions++;
                }
            }

            $avgAccuracy = $accuracySessions > 0 ? round($accuracyTotal / $accuracySessions) : 0;
            $hours = intdiv($totalPracticeTime, 3600);
            $mins = intdiv($totalPracticeTime % 3600, 60);
        @endphp

        @if($visibleSessions > 0)
        <section class="mb-8 animate-slideUp" style="animation-delay: 0.1s;">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">

                <div class="bg-white border border-[#D8DDD8] rounded-[20px] p-4 sm:p-5 text-center hover:border-[#768A96] hover:shadow-md transition-all duration-300">
                    <p class="text-xs font-semibold text-[#768A96] uppercase tracking-wide mb-2" style="font-family: 'Inter', sans-serif;">Sessions</p>
                    <p class="text-2xl sm:text-3xl font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">{{ $visibleSessions }}</p>
                </div>

                <div class="bg-white border border-[#D8DDD8] rounded-[20px] p-4 sm:p-5 text-center hover:border-[#959D90] hover:shadow-md transition-all duration-300">
                    <p class="text-xs font-semibold text-[#768A96] uppercase tracking-wide mb-2" style="font-family: 'Inter', sans-serif;">Practice Time</p>
                    <p class="text-2xl sm:text-3xl font-bold text-[#44576D]" style="font-family: 'Sora', sans-serif;">
                        {{ $hours > 0 ? $hours . 'h ' : '' }}{{ $mins }}m
                    </p>
                </div>

                <div class="bg-white border border-[#D8DDD8] rounded-[20px] p-4 sm:p-5 text-center hover:border-[#768A96] hover:shadow-md transition-all duration-300">
                    <p class="text-xs font-semibold text-[#768A96] uppercase tracking-wide mb-2" style="font-family: 'Inter', sans-serif;">Avg Accuracy</p>
                    <p class="text-2xl sm:text-3xl font-bold text-[#44576D]" style="font-family: 'Sora', sans-serif;">{{ $avgAccuracy }}%</p>
                </div>

                <div class="bg-white border border-[#D8DDD8] rounded-[20px] p-4 sm:p-5 text-center hover:border-[#523D35] hover:shadow-md transition-all duration-300">
                    <p class="text-xs font-semibold text-[#768A96] uppercase tracking-wide mb-2" style="font-family: 'Inter', sans-serif;">Best Accuracy</p>
                    <p class="text-2xl sm:text-3xl font-bold text-[#523D35]" style="font-family: 'Sora', sans-serif;">{{ $bestAccuracy }}%</p>
                </div>

            </div>
        </section>
        @endif

        {{-- FILTER + COMPARE --}}
        <section class="mb-6 animate-slideUp" style="animation-delay: 0.2s;">
            <div class="flex flex-col lg:flex-row gap-3 items-stretch lg:items-center justify-between">
                <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                    <label for="dateFilter" class="text-xs font-semibold text-[#768A96] uppercase tracking-wide" style="font-family: 'Inter', sans-serif;">
                        Filter by Date:
                    </label>

                    <div class="flex gap-2">
                        <input type="date" id="dateFilter"
                            class="px-3 py-2 rounded-2xl bg-white border border-[#D8DDD8] text-[#223030] text-sm focus:border-[#768A96] focus:outline-none transition-colors"
                            style="font-family: 'JetBrains Mono', monospace;">

                        <button id="clearDateFilter"
                            type="button"
                            class="px-4 py-2 rounded-2xl text-sm font-semibold border border-[#D8DDD8] bg-white text-[#223030] hover:border-[#768A96] hover:bg-[#F4F5F2] transition-all duration-300"
                            style="font-family: 'Inter', sans-serif;">
                            Reset
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button id="compareBtn"
                        class="px-4 py-2 rounded-2xl text-sm font-semibold transition-all duration-300 bg-white border border-[#D8DDD8] text-[#223030] hover:border-[#768A96] hover:bg-[#F4F5F2] hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-[#768A96] disabled:opacity-50"
                        type="button"
                        disabled
                        style="font-family: 'Inter', sans-serif;">
                        Compare (0 selected)
                    </button>

                    <button id="bulkDeleteBtn"
                        class="px-4 py-2 rounded-2xl text-sm font-semibold transition-all duration-300 bg-white border border-[#D8DDD8] text-[#523D35] hover:border-[#523D35] hover:bg-[#F6EFEC] hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-[#523D35] disabled:opacity-50"
                        type="button"
                        disabled
                        style="font-family: 'Inter', sans-serif;">
                        Delete Selected
                    </button>

                    <button id="clearSelectionBtn"
                        class="px-4 py-2 rounded-2xl text-sm font-semibold transition-all duration-300 bg-white border border-[#D8DDD8] text-[#44576D] hover:border-[#768A96] hover:bg-[#F4F5F2] hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-[#768A96] disabled:opacity-50"
                        type="button"
                        disabled
                        style="font-family: 'Inter', sans-serif;">
                        Clear Selection
                    </button>

                </div>
            </div>
        </section>

        {{-- COMPARISON MODAL --}}
        <div id="comparisonModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4 animate-fadeIn">
            <div class="bg-white border border-[#D8DDD8] rounded-[24px] max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-[#FCFCFA] border-b border-[#D8DDD8] px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                        Session Comparison
                    </h2>
                    <button id="closeComparison" class="text-[#768A96] hover:text-[#223030] text-2xl leading-none">✕</button>
                </div>

                <div id="comparisonContent" class="p-6"></div>
            </div>
        </div>

        {{-- SESSION CARDS --}}
        <section id="sessionsList" class="space-y-4 animate-slideUp" style="animation-delay: 0.3s;">
            @forelse($sessions as $session)
                @php
                    $total = $session->noteEvents->count();
                    $inTune = $session->noteEvents->where('tuning_status', 'in_tune')->count();
                    $pct = $total > 0 ? round(($inTune / $total) * 100) : 0;
                    $duration = $session->duration_seconds ?? 0;
                    $minsPerSession = intdiv($duration, 60);
                    $secsPerSession = $duration % 60;
                @endphp

                <div
                    class="hist-card bg-white border border-[#D8DDD8] rounded-[20px] sm:rounded-[24px] overflow-hidden hover:border-[#768A96] transition-all duration-300 hover:shadow-md"
                    data-session-id="{{ $session->session_id }}"
                    data-date="{{ $session->started_at->format('Y-m-d') }}"
                    data-target-string="{{ $session->target_string ?? '' }}"
                    data-duration="{{ $duration }}"
                    data-accuracy="{{ $pct }}"
                >
                    <div class="bg-[#FCFCFA] px-4 sm:px-6 py-4 border-b border-[#D8DDD8]">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                            <div class="flex-1">
                                <p class="text-sm font-bold text-[#223030] mb-1" style="font-family: 'Sora', sans-serif;">
                                    {{ $session->started_at->format('l, F d, Y') }}
                                </p>
                                <p class="text-xs text-[#768A96]" style="font-family: 'JetBrains Mono', monospace;">
                                    {{ $session->started_at->format('H:i:s') }}
                                    @if($session->ended_at)
                                        — {{ $session->ended_at->format('H:i:s') }}
                                    @endif
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2 items-center">
                                @if($session->target_string)
                                    <span class="px-3 py-1 bg-[#F1F3EF] text-[#223030] rounded-full text-xs font-bold" style="font-family: 'Inter', sans-serif;">
                                        {{ $session->target_string }}
                                    </span>
                                @endif

                                @if($session->duration_seconds !== null)
                                    <span class="px-3 py-1 bg-[#F1F3EF] text-[#44576D] rounded-full text-xs font-bold" style="font-family: 'Inter', sans-serif;">
                                        {{ $minsPerSession }}m {{ $secsPerSession }}s
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-[#F6EFEC] text-[#523D35] rounded-full text-xs font-bold" style="font-family: 'Inter', sans-serif;">
                                        Incomplete
                                    </span>
                                @endif

                                @if($total > 0)
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $pct >= 80 ? 'bg-[#F1F3EF] text-[#223030]' : ($pct >= 50 ? 'bg-[#EEF2F4] text-[#44576D]' : 'bg-[#F6EFEC] text-[#523D35]') }}" style="font-family: 'Inter', sans-serif;">
                                        {{ $pct }}% in tune
                                    </span>
                                @endif
                            </div>

                            <div class="flex gap-2 items-center">
                                <input
                                    type="checkbox"
                                    class="session-checkbox w-4 h-4 cursor-pointer rounded border-[#D8DDD8]"
                                    data-session-id="{{ $session->session_id }}"
                                    title="Select for comparison">

                                <button
                                    type="button"
                                    class="delete-session px-3 py-1 rounded-2xl text-xs font-bold bg-[#F6EFEC] text-[#523D35] hover:bg-[#EFE3DE] transition-colors"
                                    data-session-id="{{ $session->session_id }}"
                                    title="Delete this session"
                                    style="font-family: 'Inter', sans-serif;">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    @if($session->noteEvents->count())
                        <div class="px-4 sm:px-6 py-4 overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-[#D8DDD8]">
                                        <th class="text-left text-xs font-bold text-[#768A96] uppercase tracking-wide py-2 px-2" style="font-family: 'Inter', sans-serif;">Note</th>
                                        <th class="text-left text-xs font-bold text-[#768A96] uppercase tracking-wide py-2 px-2" style="font-family: 'Inter', sans-serif;">Freq</th>
                                        <th class="text-left text-xs font-bold text-[#768A96] uppercase tracking-wide py-2 px-2" style="font-family: 'Inter', sans-serif;">Cents</th>
                                        <th class="text-left text-xs font-bold text-[#768A96] uppercase tracking-wide py-2 px-2" style="font-family: 'Inter', sans-serif;">Status</th>
                                        <th class="text-left text-xs font-bold text-[#768A96] uppercase tracking-wide py-2 px-2" style="font-family: 'Inter', sans-serif;">Time</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#EEF1EC]">
                                    @foreach($session->noteEvents as $ev)
                                    <tr class="hover:bg-[#FAFAF8] transition-colors">
                                        <td class="py-2 px-2 font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                                            {{ $ev->note_name }}
                                        </td>
                                        <td class="py-2 px-2 text-[#44576D]" style="font-family: 'JetBrains Mono', monospace;">
                                            {{ number_format($ev->frequency, 1) }}
                                        </td>
                                        <td class="py-2 px-2 text-sm {{ $ev->cents_deviation > 0 ? 'text-[#523D35]' : ($ev->cents_deviation < 0 ? 'text-[#44576D]' : 'text-[#223030]') }}"
                                            style="font-family: 'JetBrains Mono', monospace;">
                                            {{ $ev->cents_deviation >= 0 ? '+' : '' }}{{ number_format($ev->cents_deviation, 1) }}
                                        </td>
                                        <td class="py-2 px-2">
                                            <span class="px-2 py-1 rounded-full text-xs font-bold {{ $ev->tuning_status === 'in_tune' ? 'bg-[#F1F3EF] text-[#223030]' : ($ev->tuning_status === 'flat' ? 'bg-[#EEF2F4] text-[#44576D]' : 'bg-[#F6EFEC] text-[#523D35]') }}" style="font-family: 'Inter', sans-serif;">
                                                {{ str_replace('_', ' ', ucfirst($ev->tuning_status)) }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-2 text-xs text-[#768A96]" style="font-family: 'JetBrains Mono', monospace;">
                                            {{ $ev->detected_at->format('H:i:s') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="px-4 sm:px-6 py-4 text-center">
                            <p class="text-xs text-[#768A96]" style="font-family: 'Inter', sans-serif;">No note events recorded in this session.</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-16">
                    <p class="text-lg font-semibold text-[#223030] mb-2" style="font-family: 'Sora', sans-serif;">
                        No sessions yet
                    </p>
                    <p class="text-sm text-[#768A96] mb-4" style="font-family: 'Inter', sans-serif;">
                        Start a Sound Check session to begin tracking your progress.
                    </p>
                    <a href="{{ route('student.guitar.index') }}"
                        class="px-5 sm:px-6 py-3 rounded-2xl text-sm font-semibold transition-all duration-300 bg-[#223030] text-white hover:bg-[#29353C] hover:shadow-md hover:-translate-y-1 text-decoration-none inline-block"
                        style="font-family: 'Inter', sans-serif;">
                        Open Sound Check
                    </a>
                </div>
            @endforelse
        </section>

        @if($sessions->hasPages())
        <div class="mt-8 flex justify-center">
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
    from { opacity: 0; }
    to { opacity: 1; }
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

.animate-fadeIn {
    animation: fadeIn 0.6s ease-out forwards;
}

.animate-slideUp {
    animation: slideUp 0.5s ease-out forwards;
    opacity: 0;
}

.hist-card {
    animation: slideUp 0.45s ease-out forwards;
}

.session-checkbox:checked {
    accent-color: #44576D;
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    const dateFilter = document.getElementById('dateFilter');
    const clearDateFilter = document.getElementById('clearDateFilter');
    const compareBtn = document.getElementById('compareBtn');
    const clearSelectionBtn = document.getElementById('clearSelectionBtn');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectionHelpText = document.getElementById('selectionHelpText');
    const comparisonModal = document.getElementById('comparisonModal');
    const closeComparison = document.getElementById('closeComparison');
    const comparisonContent = document.getElementById('comparisonContent');

    function updateCompareButton() {
        const selected = document.querySelectorAll('.session-checkbox:checked').length;

        compareBtn.textContent = 'Compare (' + selected + ' selected)';
        compareBtn.disabled = selected < 2 || selected > 3;

        clearSelectionBtn.disabled = selected === 0;
        bulkDeleteBtn.disabled = selected < 1;

        if (selected === 0) {
            selectionHelpText.textContent = 'Select up to 3 sessions to compare. Select more than 3 to enable bulk delete.';
        } else if (selected === 1) {
            selectionHelpText.textContent = 'Select at least 2 sessions to compare.';
        } else if (selected >= 2 && selected <= 3) {
            selectionHelpText.textContent = 'Ready to compare selected sessions.';
        } else {
            selectionHelpText.textContent = 'More than 3 selected. Comparison is limited to 3, but bulk delete is available.';
        }
    }

    function applyDateFilter(selectedDate) {
        document.querySelectorAll('.hist-card').forEach(function (card) {
            const cardDate = card.dataset.date;
            card.style.display = (!selectedDate || cardDate === selectedDate) ? '' : 'none';
        });

        document.querySelectorAll('.session-checkbox').forEach(function (cb) {
            if (cb.closest('.hist-card').style.display === 'none') {
                cb.checked = false;
            }
        });

        updateCompareButton();
    }

    if (dateFilter) {
        dateFilter.addEventListener('change', function () {
            applyDateFilter(this.value);
        });
    }

    if (clearDateFilter) {
        clearDateFilter.addEventListener('click', function () {
            dateFilter.value = '';
            applyDateFilter('');
        });
    }

    if (clearSelectionBtn) {
        clearSelectionBtn.addEventListener('click', function () {
            document.querySelectorAll('.session-checkbox:checked').forEach(function (cb) {
                cb.checked = false;
            });

            updateCompareButton();
        });
    }

    document.querySelectorAll('.session-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', updateCompareButton);
    });

    if (compareBtn) {
        compareBtn.addEventListener('click', function () {
            const selectedCards = Array.from(document.querySelectorAll('.session-checkbox:checked'))
                .map(function (cb) { return cb.closest('.hist-card'); });

            if (selectedCards.length < 2 || selectedCards.length > 3) {
                alert('Select 2 to 3 sessions to compare.');
                return;
            }

            const sessions = selectedCards.map(function (card) {
                return {
                    id: card.dataset.sessionId,
                    date: card.dataset.date,
                    targetString: card.dataset.targetString || 'Auto-detect',
                    duration: parseInt(card.dataset.duration || '0', 10),
                    accuracy: parseInt(card.dataset.accuracy || '0', 10),
                };
            });

            renderComparison(sessions);
            comparisonModal.classList.remove('hidden');
        });
    }

    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', async function () {
            const selectedCheckboxes = Array.from(document.querySelectorAll('.session-checkbox:checked'));

            if (selectedCheckboxes.length === 0) {
                alert('Select at least one session to delete.');
                return;
            }

            const confirmed = confirm('Delete the selected sessions? This cannot be undone.');
            if (!confirmed) return;

            for (const cb of selectedCheckboxes) {
                const sessionId = cb.dataset.sessionId;

                try {
                    const res = await fetch(`/student/sound-check/session/${sessionId}/delete`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        }
                    });

                    if (res.ok) {
                        const card = document.querySelector('.hist-card[data-session-id="' + sessionId + '"]');
                        if (card) card.remove();
                    }
                } catch (err) {
                    console.error('Bulk delete failed for session:', sessionId, err);
                }
            }

            updateCompareButton();

            if (!document.querySelector('.hist-card')) {
                selectionHelpText.textContent = 'No sessions available.';
            }
        });
    }

    function renderComparison(sessions) {
        let html = '<div class="space-y-6">';

        html += '<div>';
        html += '<h3 class="text-sm font-bold text-[#768A96] uppercase tracking-wide mb-3" style="font-family: Inter, sans-serif;">Accuracy Comparison</h3>';
        html += '<div class="grid gap-4" style="grid-template-columns: repeat(' + sessions.length + ', minmax(0, 1fr));">';

        sessions.forEach(function (s) {
            const dash = (s.accuracy / 100) * 282.7;
            html += `
                <div class="bg-[#FCFCFA] rounded-[20px] p-4 text-center border border-[#D8DDD8]">
                    <p class="text-xs text-[#768A96] mb-2" style="font-family: Inter, sans-serif;">${s.date}</p>
                    <div class="relative w-24 h-24 mx-auto mb-3">
                        <svg class="w-full h-full" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="45" fill="none" stroke="#D8DDD8" stroke-width="8"></circle>
                            <circle cx="50" cy="50" r="45" fill="none" stroke="#44576D" stroke-width="8"
                                stroke-dasharray="${dash} 282.7"
                                stroke-linecap="round"
                                transform="rotate(-90 50 50)"></circle>
                            <text x="50" y="54" text-anchor="middle" fill="#223030" font-size="20" font-weight="bold">${s.accuracy}%</text>
                        </svg>
                    </div>
                    <p class="text-xs text-[#768A96]" style="font-family: Inter, sans-serif;">${s.targetString}</p>
                </div>
            `;
        });

        html += '</div></div>';

        html += '<div>';
        html += '<h3 class="text-sm font-bold text-[#768A96] uppercase tracking-wide mb-3" style="font-family: Inter, sans-serif;">Practice Duration</h3>';
        html += '<div class="grid gap-4" style="grid-template-columns: repeat(' + sessions.length + ', minmax(0, 1fr));">';

        sessions.forEach(function (s) {
            const mins = Math.floor(s.duration / 60);
            const secs = s.duration % 60;
            html += `
                <div class="bg-[#FCFCFA] rounded-[20px] p-4 text-center border border-[#D8DDD8]">
                    <p class="text-2xl font-bold text-[#44576D]" style="font-family: Sora, sans-serif;">${mins}m ${secs}s</p>
                    <p class="text-xs text-[#768A96] mt-2" style="font-family: Inter, sans-serif;">${s.date}</p>
                </div>
            `;
        });

        html += '</div></div>';

        html += `
            <div>
                <h3 class="text-sm font-bold text-[#768A96] uppercase tracking-wide mb-3" style="font-family: Inter, sans-serif;">Comparison Details</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-[#D8DDD8]">
                                <th class="text-left py-2 px-3 text-xs font-bold text-[#768A96]" style="font-family: Inter, sans-serif;">Metric</th>
                                ${sessions.map(() => '<th class="text-left py-2 px-3 text-xs font-bold text-[#223030]" style="font-family: Inter, sans-serif;">Session</th>').join('')}
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-[#D8DDD8]">
                                <td class="py-2 px-3 text-[#768A96]" style="font-family: Inter, sans-serif;">Accuracy</td>
                                ${sessions.map(s => `<td class="py-2 px-3 font-bold text-[#223030]" style="font-family: JetBrains Mono, monospace;">${s.accuracy}%</td>`).join('')}
                            </tr>
                            <tr class="border-b border-[#D8DDD8]">
                                <td class="py-2 px-3 text-[#768A96]" style="font-family: Inter, sans-serif;">Duration</td>
                                ${sessions.map(s => {
                                    const mins = Math.floor(s.duration / 60);
                                    const secs = s.duration % 60;
                                    return `<td class="py-2 px-3 font-bold text-[#44576D]" style="font-family: JetBrains Mono, monospace;">${mins}m ${secs}s</td>`;
                                }).join('')}
                            </tr>
                            <tr>
                                <td class="py-2 px-3 text-[#768A96]" style="font-family: Inter, sans-serif;">Target String</td>
                                ${sessions.map(s => `<td class="py-2 px-3 font-bold text-[#523D35]" style="font-family: Inter, sans-serif;">${s.targetString || '—'}</td>`).join('')}
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        html += '</div>';
        comparisonContent.innerHTML = html;
    }

    if (closeComparison) {
        closeComparison.addEventListener('click', function () {
            comparisonModal.classList.add('hidden');
        });
    }

    if (comparisonModal) {
        comparisonModal.addEventListener('click', function (e) {
            if (e.target === comparisonModal) {
                comparisonModal.classList.add('hidden');
            }
        });
    }

    document.querySelectorAll('.delete-session').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const sessionId = this.dataset.sessionId;
            if (!confirm('Delete this session? This cannot be undone.')) return;

            try {
                const res = await fetch(`/student/sound-check/session/${sessionId}/delete`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });

                if (!res.ok) {
                    throw new Error('Delete failed');
                }

                const card = document.querySelector('.hist-card[data-session-id="' + sessionId + '"]');
                if (card) {
                    card.remove();
                }

                updateCompareButton();

            } catch (err) {
                console.error(err);
                alert('Failed to delete session.');
            }
        });
    });

})();
</script>
@endpush