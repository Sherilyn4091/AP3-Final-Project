{{-- 
  resources/views/emerging-tech/pitch-monitor/index.blade.php
  
  Pitch Monitor - Real-time pitch extraction interface
  Uses Essentia.js PitchMelodia algorithm running in AudioWorklet
  
  Layout:
  - Top: Header with controls
  - Middle: Large graph area (stacks on mobile, full-width on desktop)
  - Bottom: Metrics panel (right panel moves below on medium screens)
--}}

@extends('layouts.student')

@section('pageTitle')
    Pitch Monitor
@endsection

@section('content')
<div class="min-h-screen bg-[#f8f7f4] px-4 py-6 sm:px-6 sm:py-8">
    <div class="mx-auto max-w-7xl">

        {{-- ============================================================= --}}
        {{-- PAGE HEADER --}}
        {{-- ============================================================= --}}
        <header class="mb-8 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-[#223030] sm:text-4xl" style="font-family: 'Sora', sans-serif;">
                    Pitch Monitor
                </h1>
                <p class="mt-2 text-sm text-[#44576D] sm:text-base" style="font-family: 'Inter', sans-serif;">
                    Real-time pitch extraction using Essentia.js for monophonic audio analysis.
                </p>
                <p class="mt-1 text-xs text-[#768A96] sm:text-sm" style="font-family: 'Inter', sans-serif;">
                    Single-note detection recommended. Multiple simultaneous notes may reduce accuracy.
                </p>
            </div>

            {{-- Header actions --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Tool switcher dropdown --}}
                <div class="relative">
                    <details class="group">
                        <summary class="list-none cursor-pointer rounded-2xl border border-[#D8DDD8] bg-white px-4 py-2 text-sm font-semibold text-[#223030] transition hover:border-[#768A96] hover:bg-[#F4F5F2]" style="font-family: 'Inter', sans-serif;">
                            Switch Tool
                        </summary>

                        <div class="absolute right-0 z-20 mt-2 w-52 overflow-hidden rounded-2xl border border-[#D8DDD8] bg-white shadow-lg">
                            <a href="{{ route('student.guitar.index') }}"
                               class="block px-4 py-3 text-sm text-[#223030] hover:bg-[#F4F5F2]"
                               style="font-family: 'Inter', sans-serif;">
                                Sound Check
                            </a>
                            <a href="{{ route('student.pitch-monitor.index') }}"
                               class="block px-4 py-3 text-sm text-[#223030] hover:bg-[#F4F5F2]"
                               style="font-family: 'Inter', sans-serif;">
                                Pitch Monitor
                            </a>
                        </div>
                    </details>
                </div>

                {{-- History button --}}
                <a href="{{ route('student.pitch-monitor.history') }}"
                   class="rounded-2xl border border-[#D8DDD8] bg-white px-4 py-2 text-sm font-semibold text-[#223030] transition hover:border-[#768A96] hover:bg-[#F4F5F2]"
                   style="font-family: 'Inter', sans-serif;">
                    History
                </a>
            </div>
        </header>

        {{-- ============================================================= --}}
        {{-- MAIN CONTENT AREA --}}
        {{-- ============================================================= --}}
        <section class="space-y-5">

            {{-- NOTE DETECTION DISPLAY --}}
            <div class="rounded-[24px] border border-[#D8DDD8] bg-white p-6 shadow-sm">
                <div class="text-center">
                    {{-- Large detected note --}}
                    <div id="noteDisplay"
                         class="text-5xl font-extrabold text-[#223030] sm:text-6xl md:text-7xl"
                         style="font-family: 'Sora', sans-serif; letter-spacing: -0.02em;">
                        --
                    </div>

                    {{-- Frequency readout --}}
                    <div id="frequencyDisplay"
                         class="mt-3 text-lg text-[#44576D]"
                         style="font-family: 'JetBrains Mono', monospace;">
                        -- Hz
                    </div>

                    {{-- Cents deviation --}}
                    <div id="centsDisplay"
                         class="mt-2 text-2xl font-bold text-[#223030]"
                         style="font-family: 'JetBrains Mono', monospace;">
                        0.0 ¢
                    </div>
                </div>
            </div>

            {{-- PITCH CONTOUR GRAPH (Essentia-style) --}}
            <div class="rounded-[24px] border border-[#D8DDD8] bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-[#44576D]" 
                        style="font-family: 'Inter', sans-serif;">
                        Live Pitch Contour
                    </h2>
                    <span id="statusBadge"
                          class="rounded-full bg-[#EEF2F4] px-3 py-1 text-xs font-bold text-[#44576D]"
                          style="font-family: 'Inter', sans-serif;">
                        Idle
                    </span>
                </div>

                {{-- Canvas: Responsive pitch graph --}}
                {{-- 
                    Graph wrapper

                    Purpose:
                    - Keeps the graph large and accurate.
                    - On smaller screens, the graph scrolls horizontally instead of being compressed.
                --}}
                <div id="pitchGraphScroller" class="w-full overflow-x-auto pb-3">
                    <div id="pitchGraphInner" class="min-w-[1100px]">
                        <canvas id="pitchCanvas" 
                                class="block w-full rounded-2xl bg-white"
                                style="height: 520px; min-height: 420px;">
                        </canvas>
                    </div>
                </div>
            </div>

            {{-- SECONDARY PANEL: 5 compact cards below the graph --}}
            <div class="rounded-[24px] border border-[#D8DDD8] bg-white p-5 shadow-sm">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

                    {{-- CARD 1: CONFIDENCE METER --}}
                    <div class="rounded-[20px] border border-[#D8DDD8] bg-[#FCFCFA] p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <label class="text-xs font-semibold uppercase tracking-wide text-[#44576D]" 
                                style="font-family: 'Inter', sans-serif;">
                                Confidence
                            </label>
                            <span id="confidenceText"
                                class="text-sm text-[#223030]"
                                style="font-family: 'JetBrains Mono', monospace;">
                                0%
                            </span>
                        </div>
                        <div class="h-3 overflow-hidden rounded-full bg-[#EEF1EC]">
                            <div id="confidenceBar" 
                                class="h-full w-0 rounded-full bg-[#768A96] transition-all duration-150">
                            </div>
                        </div>
                    </div>

                    {{-- CARD 2: SIGNAL LEVEL METER --}}
                    <div class="rounded-[20px] border border-[#D8DDD8] bg-[#FCFCFA] p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <label class="text-xs font-semibold uppercase tracking-wide text-[#44576D]" 
                                style="font-family: 'Inter', sans-serif;">
                                Signal Level
                            </label>
                            <span id="rmsText"
                                class="text-sm text-[#223030]"
                                style="font-family: 'JetBrains Mono', monospace;">
                                0.0000
                            </span>
                        </div>
                        <div class="h-3 overflow-hidden rounded-full bg-[#EEF1EC]">
                            <div id="rmsBar" 
                                class="h-full w-0 rounded-full bg-[#959D90] transition-all duration-150">
                            </div>
                        </div>
                    </div>

                    {{-- CARD 3: NEAREST NOTE DISPLAY --}}
                    <div class="rounded-[20px] border border-[#D8DDD8] bg-[#FCFCFA] p-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-[#768A96]" 
                            style="font-family: 'Inter', sans-serif;">
                            Nearest Note
                        </h3>
                        <p id="nearestNoteText"
                        class="mt-3 text-2xl font-bold text-[#223030]"
                        style="font-family: 'Sora', sans-serif;">
                            --
                        </p>
                    </div>

                    {{-- CARD 4: LIVE STATUS MESSAGE --}}
                    <div class="rounded-[20px] border border-[#D8DDD8] bg-[#FCFCFA] p-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-[#768A96]" 
                            style="font-family: 'Inter', sans-serif;">
                            Status
                        </h3>
                        <p id="liveStatusText"
                        class="mt-3 text-sm text-[#44576D]"
                        style="font-family: 'Inter', sans-serif;">
                            Press Start to begin microphone-based pitch monitoring.
                        </p>
                    </div>

                    {{-- CARD 5: CONTROL BUTTONS --}}
                    <div class="rounded-[20px] border border-[#D8DDD8] bg-[#FCFCFA] p-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-[#768A96]" 
                            style="font-family: 'Inter', sans-serif;">
                            Controls
                        </h3>

                        {{--
                            Pitch Monitor Controls

                            Purpose:
                            - Start begins the microphone recording/session.
                            - Pause freezes the current recording display without resetting the graph.
                            - Resume continues the microphone recording/session after pause.
                            - Reset stops the current session and restores the original display state.
                        --}}
                        <div class="mt-3 grid grid-cols-1 gap-2">
                            <button id="btnStart"
                                    type="button"
                                    class="rounded-2xl bg-[#223030] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#29353C] disabled:cursor-not-allowed disabled:opacity-50"
                                    style="font-family: 'Inter', sans-serif;">
                                Start
                            </button>

                            <div class="grid grid-cols-2 gap-2">
                                <button id="btnPause"
                                        type="button"
                                        disabled
                                        class="flex flex-col items-center justify-center gap-1 rounded-2xl border border-[#D8DDD8] bg-white px-3 py-2.5 text-xs font-semibold text-[#223030] transition hover:border-[#768A96] hover:bg-[#F4F5F2] disabled:cursor-not-allowed disabled:opacity-50"
                                        style="font-family: 'Inter', sans-serif;">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M8 5v14M16 5v14" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                                    </svg>
                                    Pause
                                </button>

                                <button id="btnResume"
                                        type="button"
                                        disabled
                                        class="flex flex-col items-center justify-center gap-1 rounded-2xl border border-[#D8DDD8] bg-white px-3 py-2.5 text-xs font-semibold text-[#223030] transition hover:border-[#768A96] hover:bg-[#F4F5F2] disabled:cursor-not-allowed disabled:opacity-50"
                                        style="font-family: 'Inter', sans-serif;">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M8 5.8v12.4c0 .8.9 1.3 1.6.8l9-6.2c.6-.4.6-1.3 0-1.7l-9-6.2C8.9 4.5 8 5 8 5.8Z" fill="currentColor"/>
                                    </svg>
                                    Resume
                                </button>
                            </div>

                            <button id="btnReset"
                                    type="button"
                                    class="rounded-2xl border border-[#D8DDD8] bg-white px-4 py-2.5 text-sm font-semibold text-[#223030] transition hover:border-[#768A96] hover:bg-[#F4F5F2] disabled:cursor-not-allowed disabled:opacity-50"
                                    style="font-family: 'Inter', sans-serif;">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- WAVEFORM DISPLAY --}}
            <div class="rounded-[24px] border border-[#D8DDD8] bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-[#44576D]" 
                    style="font-family: 'Inter', sans-serif;">
                    Live Waveform
                </h2>
                {{-- Canvas: Responsive waveform visualization --}}
                <canvas id="waveCanvas" 
                        class="block w-full rounded-2xl bg-white"
                        style="height: 150px; min-height: 120px;">
                </canvas>
            </div>
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Import feature fonts */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&family=Sora:wght@400;600;700;800&display=swap');

/* Responsive canvas sizing */
@media (max-width: 768px) {
    #pitchCanvas {
        height: 360px !important;
        min-height: 320px !important;
    }

    #waveCanvas {
        height: 120px !important;
        min-height: 100px !important;
    }
}

/*
|--------------------------------------------------------------------------
| Pitch Monitor Responsive Graph
|--------------------------------------------------------------------------
|
| The pitch graph should not shrink too much on small screens because
| compressed graph width reduces readability and makes pitch movement unclear.
| Instead, the graph keeps a minimum width and scrolls horizontally.
|
*/
#pitchGraphScroller {
    overscroll-behavior-x: contain;
    scrollbar-width: thin;
}

#pitchGraphInner {
    min-width: 1100px;
}

@media (max-width: 768px) {
    #pitchGraphInner {
        min-width: 980px;
    }
}

@media (min-width: 769px) and (max-width: 1180px) {
    #pitchGraphInner {
        min-width: 1050px;
    }
}

@media (min-width: 769px) and (max-width: 1023px) {
    #pitchCanvas {
        height: 430px !important;
        min-height: 380px !important;
    }

    #waveCanvas {
        height: 135px !important;
        min-height: 110px !important;
    }
}

@media (min-width: 1024px) {
    #pitchCanvas {
        height: 520px !important;
        min-height: 420px !important;
    }

    #waveCanvas {
        height: 150px !important;
        min-height: 120px !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
    /*
      Pitch Monitor Config

      Purpose:
      - Keeps Laravel Blade values inside the Blade file
      - Keeps pitch-monitor-controller.js as pure JavaScript
      - Prevents JavaScript syntax errors caused by Blade helper text inside normal JavaScript files

      Important:
      - Do not use asset('js/...') here because the files are in resources/js, not public/js.
      - The controller is loaded through Vite.
      - The processor URL is generated through Vite and passed to the controller.
    */
    window.pitchMonitorConfig = {
        processorUrl: {!! \Illuminate\Support\Js::from(Vite::asset('resources/js/emerging-tech/pitch-monitor-processor.js')) !!},

        /*
          Essentia CDN files

          Purpose:
          - These files are combined with the AudioWorklet processor at runtime.
          - The controller treats these as optional, so the graph can still work
            with the JavaScript fallback if the CDN cannot load.
        */
        essentiaWasmUrl: 'https://cdn.jsdelivr.net/npm/essentia.js@0.1.3/dist/essentia-wasm.umd.js',
        essentiaCoreUrl: 'https://cdn.jsdelivr.net/npm/essentia.js@0.1.3/dist/essentia.js-core.umd.js',

        startSessionUrl: {!! \Illuminate\Support\Js::from(route('student.pitch-monitor.session.start')) !!},
        endSessionUrlTemplate: {!! \Illuminate\Support\Js::from(url('/student/pitch-monitor/session/__SESSION_ID__/end')) !!},
        storeEventUrlTemplate: {!! \Illuminate\Support\Js::from(url('/student/pitch-monitor/session/__SESSION_ID__/event')) !!},
        csrfToken: {!! \Illuminate\Support\Js::from(csrf_token()) !!}
    };
</script>

@vite('resources/js/emerging-tech/pitch-monitor-controller.js')
@endpush