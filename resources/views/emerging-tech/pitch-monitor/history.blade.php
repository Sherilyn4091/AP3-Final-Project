{{-- resources/views/emerging-tech/pitch-monitor/history.blade.php --}}
@extends('layouts.student')

@section('pageTitle', 'Pitch Monitor History')

@section('content')
<div class="min-h-screen bg-[#f8f7f4] px-4 py-6 sm:px-6 sm:py-8">
    <div class="max-w-5xl mx-auto">

        {{-- ============================================================= --}}
        {{-- HEADER --}}
        {{-- ============================================================= --}}
        <header class="relative z-50 mb-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="min-w-0">
                    <h1 class="text-3xl sm:text-4xl font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                        Pitch Monitor History
                    </h1>
                    <p class="mt-2 text-sm text-[#44576D]" style="font-family: 'Inter', sans-serif;">
                        Review previous pitch extraction sessions and captured note events.
                    </p>
                </div>

                {{--
                |--------------------------------------------------------------------------
                | History Header Actions
                |--------------------------------------------------------------------------
                |
                | Purpose:
                | - Keeps Practice History as the only history link in the sidebar.
                | - Allows users to switch back to String Pitch Detection History here.
                | - Uses a floating dropdown so session cards do not move when opened.
                | - Keeps buttons horizontally aligned across screen sizes.
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
        {{-- SESSION LIST --}}
        {{-- ============================================================= --}}
        <section class="space-y-4">
            @forelse($sessions as $session)
                @php
                    $duration = $session->duration_seconds ?? 0;
                    $minutes = intdiv($duration, 60);
                    $seconds = $duration % 60;
                @endphp

                <div class="overflow-hidden rounded-[26px] border border-[#D8DDD8] bg-white shadow-sm">
                    <div class="border-b border-[#D8DDD8] bg-[#FCFCFA] px-5 py-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                                    {{ $session->started_at->format('F d, Y') }}
                                </h2>
                                <p class="text-sm text-[#768A96]" style="font-family: 'JetBrains Mono', monospace;">
                                    {{ $session->started_at->format('H:i:s') }}
                                    @if($session->ended_at)
                                        — {{ $session->ended_at->format('H:i:s') }}
                                    @endif
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-[#EEF2F4] px-3 py-1 text-xs font-bold text-[#44576D]" style="font-family: 'Inter', sans-serif;">
                                    {{ $minutes }}m {{ $seconds }}s
                                </span>

                                <button type="button"
                                        class="delete-pitch-session rounded-2xl bg-[#F6EFEC] px-3 py-2 text-xs font-bold text-[#523D35] hover:bg-[#EFE3DE]"
                                        data-session-id="{{ $session->session_id }}"
                                        style="font-family: 'Inter', sans-serif;">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto px-5 py-4">
                        @if($session->events->count())
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
                                    @foreach($session->events as $event)
                                        <tr>
                                            <td class="px-2 py-3 font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                                                {{ $event->note_name }}
                                            </td>
                                            <td class="px-2 py-3 text-[#44576D]" style="font-family: 'JetBrains Mono', monospace;">
                                                {{ number_format($event->frequency, 2) }} Hz
                                            </td>
                                            <td class="px-2 py-3 text-[#223030]" style="font-family: 'JetBrains Mono', monospace;">
                                                {{ $event->cents_deviation >= 0 ? '+' : '' }}{{ number_format($event->cents_deviation, 2) }} ¢
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
                                                    {{ ucfirst(str_replace('_', ' ', $event->tuning_status)) }}
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
                            <p class="text-sm text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                                No pitch events were saved in this session.
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-[26px] border border-[#D8DDD8] bg-white px-6 py-12 text-center shadow-sm">
                    <h2 class="text-2xl font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                        No pitch monitor sessions yet
                    </h2>
                    <p class="mt-2 text-sm text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                        Start using Pitch Monitor to build your session history.
                    </p>
                    <a href="{{ route('student.pitch-monitor.index') }}"
                       class="mt-5 inline-block rounded-2xl bg-[#223030] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#29353C]"
                       style="font-family: 'Inter', sans-serif;">
                        Open Pitch Monitor
                    </a>
                </div>
            @endforelse
        </section>

        @if($sessions->hasPages())
            <div class="mt-6">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&family=Sora:wght@400;600;700;800&display=swap');
</style>
@endpush

@push('scripts')
<script>
/*
|--------------------------------------------------------------------------
| Delete Pitch Monitor Session
|--------------------------------------------------------------------------
*/
document.querySelectorAll('.delete-pitch-session').forEach(function (button) {
    button.addEventListener('click', async function () {
        const sessionId = this.dataset.sessionId;

        if (!confirm('Delete this pitch monitor session? This cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch('/student/pitch-monitor/session/' + sessionId + '/delete', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Delete failed.');
            }

            window.location.reload();
        } catch (error) {
            console.error(error);
            alert('Failed to delete pitch monitor session.');
        }
    });
});
</script>
@endpush