{{-- resources/views/guitar-analyzer/index.blade.php --}}
@extends('layouts.student')

@section('title', 'Sound Check')

@section('content')
<div class="min-h-screen bg-[#f8f7f4] py-6 sm:py-8 px-3 sm:px-4">
    <div class="max-w-5xl mx-auto">

        {{-- PAGE HEADER --}}
        <header class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between animate-fadeIn">
            <div>
                <h1 class="text-3xl sm:text-4xl font-bold text-[#223030] mb-2" style="font-family: 'Sora', sans-serif;">
                    Sound Check
                </h1>

                <p class="text-sm sm:text-base text-[#44576D] mb-1" style="font-family: 'Inter', sans-serif;">
                    Real-time single-string pitch detection and tuning assistant
                </p>
                <p class="text-xs sm:text-sm text-[#768A96]" style="font-family: 'Inter', sans-serif;">
                    Pluck one string at a time. Chords are not supported.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
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

                <a href="{{ route('student.pitch-monitor.index') }}"
                class="rounded-2xl border border-[#D8DDD8] bg-white px-4 py-2 text-sm font-semibold text-[#223030] transition hover:border-[#768A96] hover:bg-[#F4F5F2]"
                style="font-family: 'Inter', sans-serif;">
                    Open Pitch Monitor
                </a>
            </div>
        </header>

        {{-- STRING SELECTION --}}
        <section class="mb-6 animate-slideUp" style="animation-delay: 0.08s;">
            <div class="flex items-center justify-between gap-3 mb-3 px-1">
                <label class="text-xs sm:text-sm font-semibold uppercase tracking-wide text-[#44576D]" style="font-family: 'Inter', sans-serif;">
                    Select Target String
                </label>

                <p id="selectionHint" class="text-[11px] sm:text-xs text-[#768A96] italic text-right" style="font-family: 'Inter', sans-serif;"></p>
            </div>

            {{-- Responsive: 3 columns on small screens, 6 on wider screens --}}
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 sm:gap-3">
                @foreach(['E2'=>82.41,'A2'=>110.00,'D3'=>146.83,'G3'=>196.00,'B3'=>246.94,'E4'=>329.63] as $name => $hz)
                <button
                    type="button"
                    class="str-btn group relative rounded-2xl border border-[#D7DDD9] bg-white px-2 py-3 sm:px-3 sm:py-4 text-[#223030] transition-all duration-300 hover:border-[#768A96] hover:bg-[#F4F5F2] hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#768A96] focus:ring-offset-2 focus:ring-offset-[#f8f7f4] active:scale-[0.98]"
                    data-note="{{ $name }}"
                    data-freq="{{ $hz }}"
                    title="Select {{ $name }}"
                >
                    {{-- Sora for note names --}}
                    <div class="text-sm sm:text-base font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                        {{ $name }}
                    </div>

                    {{-- JetBrains Mono for precise Hz values --}}
                    <div class="mt-1 text-[10px] sm:text-xs text-[#768A96]" style="font-family: 'JetBrains Mono', monospace;">
                        {{ number_format($hz, 0) }} Hz
                    </div>

                    {{-- Monochrome selected marker --}}
                    <div class="str-check absolute top-1.5 right-1.5 h-4 w-4 rounded-full bg-[#223030] text-white text-[10px] flex items-center justify-center opacity-0 scale-75 transition-all duration-300">
                        ✓
                    </div>
                </button>
                @endforeach
            </div>
        </section>

        {{-- MAIN ANALYZER LAYOUT --}}
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5 animate-slideUp" style="animation-delay: 0.14s;">

            {{-- LEFT: MAIN TUNER CARD --}}
            <div class="lg:col-span-8 bg-white border border-[#D7DDD9] rounded-[28px] shadow-sm p-4 sm:p-6">
                {{-- Detected note --}}
                <div class="text-center mb-6">
                    {{-- Sora for big note --}}
                    <div id="noteDisplay"
                        class="text-5xl sm:text-6xl md:text-7xl font-extrabold text-[#223030] leading-none transition-all duration-200"
                        style="font-family: 'Sora', sans-serif;">
                        --
                    </div>

                    {{-- JetBrains Mono for exact frequency --}}
                    <div id="freqDisplay"
                        class="mt-2 text-base sm:text-lg text-[#44576D]"
                        style="font-family: 'JetBrains Mono', monospace;">
                        -- Hz
                    </div>
                </div>

                {{-- CURVED TUNER VISUAL --}}
                <div class="mb-6">
                    <div class="relative w-full h-44 sm:h-52 bg-[#FCFCFA] border border-[#E1E6E2] rounded-[28px] overflow-hidden shadow-inner">

                        {{-- Soft curved guide arc using chosen harmony palette --}}
                        <svg class="absolute inset-0 w-full h-full" viewBox="0 0 500 240" preserveAspectRatio="none" aria-hidden="true">
                            <defs>
                                <linearGradient id="tunerArcGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" style="stop-color:#f2c94c;stop-opacity:0.55;" />
                                    <stop offset="50%" style="stop-color:#6fcf97;stop-opacity:0.55;" />
                                    <stop offset="100%" style="stop-color:#eb5757;stop-opacity:0.45;" />
                                </linearGradient>
                            </defs>

                            {{-- Main outer arc --}}
                            <path d="M 35 170 Q 250 28, 465 170"
                                  fill="none"
                                  stroke="url(#tunerArcGradient)"
                                  stroke-width="40"
                                  stroke-linecap="round" />

                            {{-- Subtle inner arc for more real tuner feel --}}
                            <path d="M 80 174 Q 250 64, 420 174"
                                  fill="none"
                                  stroke="#E4E9E5"
                                  stroke-width="2.5"
                                  stroke-linecap="round" />
                        </svg>

                        {{-- Tick marks for a more real tuner look --}}
                        <div class="absolute inset-0 pointer-events-none">
                            <div class="tick tick-1"></div>
                            <div class="tick tick-2"></div>
                            <div class="tick tick-3"></div>
                            <div class="tick tick-4"></div>
                            <div class="tick tick-5"></div>
                            <div class="tick tick-6"></div>
                            <div class="tick tick-7"></div>
                            <div class="tick tick-8"></div>
                            <div class="tick tick-9"></div>
                        </div>

                        {{-- Center reference line --}}
                        <div class="absolute inset-0 flex items-end justify-center pb-8 pointer-events-none">
                            <div class="w-[2px] h-16 sm:h-20 bg-[#959D90] opacity-60 rounded-full"></div>
                        </div>

                        {{-- Needle --}}
                        <div id="needle" class="needle absolute bottom-8 left-1/2 z-10">
                            <div class="w-1.5 h-24 sm:h-28 bg-[#223030] rounded-full shadow-sm"></div>
                            <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-4 h-4 rounded-full bg-[#223030]"></div>
                        </div>

                        {{-- Labels --}}
                        <div class="absolute bottom-3 left-0 right-0 flex justify-between px-4 sm:px-6 text-[11px] sm:text-xs font-semibold" style="font-family: 'Inter', sans-serif;">
                            <span class="text-[#44576D]">Flat</span>
                            <span class="text-[#223030]">In Tune</span>
                            <span class="text-[#523D35]">Sharp</span>
                        </div>
                    </div>
                </div>

                {{-- STATUS + CENTS --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4">
                    <span id="tuningStatus"
                        class="tuning-badge px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider bg-[#F1F3EF] text-[#768A96] transition-all duration-300"
                        style="font-family: 'Inter', sans-serif;">
                        --
                    </span>

                    {{-- JetBrains Mono for cents --}}
                    <span id="centsDisplay"
                        class="text-xl sm:text-2xl font-bold text-[#223030]"
                        style="font-family: 'JetBrains Mono', monospace;">
                        0 ¢
                    </span>
                </div>
            </div>

            {{-- RIGHT: SIDE INFO CARD --}}
            <div class="lg:col-span-4 bg-white border border-[#D7DDD9] rounded-[28px] shadow-sm p-4 sm:p-5 flex flex-col gap-5">

                {{-- SIGNAL QUALITY --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs sm:text-sm font-semibold uppercase tracking-wide text-[#44576D]" style="font-family: 'Inter', sans-serif;">
                            Signal Quality
                        </label>
                        <span id="confPct" class="text-xs sm:text-sm text-[#223030]" style="font-family: 'JetBrains Mono', monospace;">
                            0%
                        </span>
                    </div>

                    <div class="h-3.5 bg-[#F3F5F1] border border-[#D7DDD9] rounded-full overflow-hidden shadow-inner">
                        <div id="confBar"
                            class="h-full w-0 rounded-full transition-all duration-150"
                            style="background: linear-gradient(90deg, #f2c94c, #6fcf97, #27ae60); box-shadow: 0 0 10px rgba(111, 207, 151, 0.35);">
                        </div>
                    </div>

                    <p id="signalHint" class="mt-2 text-[11px] sm:text-xs text-[#768A96] italic" style="font-family: 'Inter', sans-serif;">
                        Pluck a string clearly...
                    </p>
                </div>

                {{-- WAVEFORM --}}
                <div>
                    <label class="block mb-2 text-xs sm:text-sm font-semibold uppercase tracking-wide text-[#44576D]" style="font-family: 'Inter', sans-serif;">
                        Live Waveform
                    </label>
                    <canvas id="waveCanvas" class="w-full h-20 sm:h-24 bg-[linear-gradient(180deg,#fcfcfa_0%,#f4f8f4_100%)] border border-[#D7DDD9] rounded-2xl block shadow-inner"></canvas>
                </div>

                {{-- STATUS MESSAGE --}}
                <div>
                    <label class="block mb-2 text-xs sm:text-sm font-semibold uppercase tracking-wide text-[#44576D]" style="font-family: 'Inter', sans-serif;">
                        Live Status
                    </label>
                    <div class="min-h-[52px] rounded-2xl border border-[#D7DDD9] bg-[#FCFCFA] px-4 py-3 text-xs sm:text-sm text-[#44576D]" style="font-family: 'Inter', sans-serif;">
                        <p id="statusMsg">Select a string (optional), then press Start.</p>
                    </div>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="grid grid-cols-2 gap-2 sm:gap-3 mt-auto">
                    <button id="btnStart"
                        class="rounded-2xl bg-[#223030] px-4 py-3 text-white text-sm font-semibold transition-all duration-300 hover:bg-[#29353C] hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#768A96] focus:ring-offset-2 focus:ring-offset-white disabled:opacity-50 disabled:cursor-not-allowed"
                        type="button"
                        style="font-family: 'Inter', sans-serif;">
                        Start
                    </button>

                    <button id="btnStop"
                        class="rounded-2xl border border-[#D7DDD9] bg-white px-4 py-3 text-[#223030] text-sm font-semibold transition-all duration-300 hover:border-[#768A96] hover:bg-[#F4F5F2] hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#768A96] focus:ring-offset-2 focus:ring-offset-white disabled:opacity-50 disabled:cursor-not-allowed"
                        type="button"
                        disabled
                        style="font-family: 'Inter', sans-serif;">
                        Stop
                    </button>

                    <button id="btnReset"
                        class="rounded-2xl border border-[#D7DDD9] bg-white px-4 py-3 text-[#223030] text-sm font-semibold transition-all duration-300 hover:border-[#768A96] hover:bg-[#F4F5F2] hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#768A96] focus:ring-offset-2 focus:ring-offset-white"
                        type="button"
                        style="font-family: 'Inter', sans-serif;">
                        Reset
                    </button>

                    <a href="{{ route('student.guitar.history') }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-[#D7DDD9] bg-white px-4 py-3 text-[#223030] text-sm font-semibold transition-all duration-300 hover:border-[#768A96] hover:bg-[#F4F5F2] hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#768A96] focus:ring-offset-2 focus:ring-offset-white"
                        style="font-family: 'Inter', sans-serif;">
                        History
                    </a>
                </div>
            </div>
        </section>

    </div>
</div>
@endsection

@push('styles')
<style>
/* FONT SYSTEM
   Sora = big / identity / section emphasis
   Inter = body text / labels / buttons
   JetBrains Mono = frequency / cents / technical values */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&family=Sora:wght@400;600;700;800&display=swap');

/* Entry animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
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
    animation: fadeIn 0.55s ease-out forwards;
}

.animate-slideUp {
    animation: slideUp 0.45s ease-out forwards;
    opacity: 0;
}

/* Selected string state */
.str-btn.active {
    border-color: #768A96 !important;
    background: #F4F5F2 !important;
    box-shadow: 0 0 0 1px rgba(118, 138, 150, 0.18), 0 6px 16px rgba(34, 48, 48, 0.05);
}

.str-btn.active .str-check {
    opacity: 1;
    transform: scale(1);
}

/* Auto-detected target highlight */
.str-btn.detected {
    border-color: #959D90 !important;
    box-shadow: 0 0 0 1px rgba(149, 157, 144, 0.18);
}

/* In-tune highlight */
.str-btn.detected-in-tune {
    border-color: #223030 !important;
    background: #F4F5F2 !important;
    color: #223030 !important;
    box-shadow: 0 0 0 1px rgba(34, 48, 48, 0.10), 0 4px 12px rgba(34, 48, 48, 0.06);
}

/* Badge states */
.tuning-badge.in_tune {
    background: rgba(149, 157, 144, 0.25);
    color: #223030;
}

.tuning-badge.flat {
    background: rgba(118, 138, 150, 0.18);
    color: #44576D;
}

.tuning-badge.sharp {
    background: rgba(82, 61, 53, 0.12);
    color: #523D35;
}

/* Big note color changes */
#noteDisplay {
    text-shadow: 0 0 18px rgba(149, 157, 144, 0.12);
    transition: color 0.2s ease, text-shadow 0.2s ease;
}

#noteDisplay.in-tune {
    color: #223030;
    text-shadow: 0 0 18px rgba(149, 157, 144, 0.22);
}

#noteDisplay.flat {
    color: #44576D;
    text-shadow: 0 0 16px rgba(118, 138, 150, 0.16);
}

#noteDisplay.sharp {
    color: #523D35;
    text-shadow: 0 0 16px rgba(82, 61, 53, 0.12);
}

/* Needle must stay centered while rotating */
.needle {
    transform: translateX(-50%) rotate(0deg);
    transform-origin: bottom center;
    transition: transform 0.1s ease-out;
}

/* Decorative tuner ticks */
.tick {
    position: absolute;
    bottom: 72px;
    left: 50%;
    width: 2px;
    height: 18px;
    background: rgba(68, 87, 109, 0.45);
    border-radius: 999px;
    transform-origin: bottom center;
}

.tick-1 { transform: translateX(-50%) rotate(-42deg) translateY(-58px); }
.tick-2 { transform: translateX(-50%) rotate(-31deg) translateY(-53px); }
.tick-3 { transform: translateX(-50%) rotate(-20deg) translateY(-48px); }
.tick-4 { transform: translateX(-50%) rotate(-10deg) translateY(-44px); }
.tick-5 { transform: translateX(-50%) rotate(0deg) translateY(-42px); }
.tick-6 { transform: translateX(-50%) rotate(10deg) translateY(-44px); }
.tick-7 { transform: translateX(-50%) rotate(20deg) translateY(-48px); }
.tick-8 { transform: translateX(-50%) rotate(31deg) translateY(-53px); }
.tick-9 { transform: translateX(-50%) rotate(42deg) translateY(-58px); }

/* Mobile tuning visual adjustments */
@media (max-width: 640px) {
    .tick {
        bottom: 66px;
        height: 14px;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    const NOTE_NAMES = ['C','C#','D','D#','E','F','F#','G','G#','A','A#','B'];

    /* Standard guitar open strings */
    const STANDARD_STRINGS = [
        { note: 'E2', freq: 82.41 },
        { note: 'A2', freq: 110.00 },
        { note: 'D3', freq: 146.83 },
        { note: 'G3', freq: 196.00 },
        { note: 'B3', freq: 246.94 },
        { note: 'E4', freq: 329.63 }
    ];

    /* Analyzer tuning settings */
    const BUF_SIZE = 2048;
    const SMOOTH_ALPHA = 0.15;
    const STABLE_MS = 110;
    const CENTS_IN_TUNE = 10;
    const SAVE_DELAY_MS = 800;
    const MIN_CONFIDENCE_TO_SAVE = 0.55;
    const MIN_VALID_FREQ = 70;
    const MAX_VALID_FREQ = 360;

    let audioCtx, analyser, srcNode, micStream, rafId;
    let sessionId = null;
    let targetNote = null;
    let targetFreq = null;
    let isListening = false;
    let smoothedFreq = 0;
    let stableNote = null;
    let stableStart = 0;
    let lastSave = 0;

    const timeBuf = new Float32Array(BUF_SIZE);

    /* DOM refs */
    const elNote = document.getElementById('noteDisplay');
    const elFreq = document.getElementById('freqDisplay');
    const elBadge = document.getElementById('tuningStatus');
    const elCents = document.getElementById('centsDisplay');
    const elNeedle = document.getElementById('needle');
    const elMsg = document.getElementById('statusMsg');
    const elConf = document.getElementById('confBar');
    const elConfPc = document.getElementById('confPct');
    const elSigHint = document.getElementById('signalHint');
    const elSelHint = document.getElementById('selectionHint');
    const btnStart = document.getElementById('btnStart');
    const btnStop = document.getElementById('btnStop');
    const btnReset = document.getElementById('btnReset');
    const canvas = document.getElementById('waveCanvas');
    const ctx2d = canvas.getContext('2d');

    /* String selection:
       - click once = select target
       - click again = clear target */
    document.querySelectorAll('.str-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const isSelected = btn.classList.contains('active');

            document.querySelectorAll('.str-btn').forEach(function (b) {
                b.classList.remove('active');
            });

            if (!isSelected) {
                btn.classList.add('active');
                targetNote = btn.dataset.note;
                targetFreq = parseFloat(btn.dataset.freq);
                elSelHint.textContent = 'Target: ' + targetNote + ' — manual guide enabled';
            } else {
                targetNote = null;
                targetFreq = null;
                elSelHint.textContent = 'Target cleared — auto-detect will be used';
            }
        });
    });

    /* Start analyzer */
    btnStart.addEventListener('click', async function () {
        if (isListening) return;
        setMsg('Requesting microphone…');

        try {
            micStream = await navigator.mediaDevices.getUserMedia({
                audio: {
                    echoCancellation: false,
                    noiseSuppression: false,
                    autoGainControl: false,
                    channelCount: 1,
                },
                video: false,
            });
        } catch (err) {
            setMsg('Microphone access denied. Please allow microphone permissions.');
            console.error(err);
            return;
        }

        try {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            analyser = audioCtx.createAnalyser();
            analyser.fftSize = BUF_SIZE;
            analyser.smoothingTimeConstant = 0;

            srcNode = audioCtx.createMediaStreamSource(micStream);
            srcNode.connect(analyser);

            if (audioCtx.state === 'suspended') {
                await audioCtx.resume();
            }

            const res = await fetch('{{ route("student.guitar.session.start") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ target_string: targetNote }),
            });

            if (!res.ok) {
                const text = await res.text();
                console.error('SESSION START FAILED:', text);
                setMsg('Backend error. Please try again.');
                return;
            }

            const data = await res.json();
            sessionId = data.session_id;

            isListening = true;
            smoothedFreq = 0;
            stableNote = null;
            stableStart = 0;
            lastSave = 0;

            btnStart.disabled = true;
            btnStop.disabled = false;

            setMsg('Listening… pluck one string clearly.');
            elSigHint.textContent = 'Waiting for signal…';
            loop();

        } catch (err) {
            console.error(err);
            setMsg('Failed to start analyzer.');
        }
    });

    btnStop.addEventListener('click', stopListening);

    async function stopListening() {
        if (!isListening) return;

        isListening = false;
        cancelAnimationFrame(rafId);

        if (srcNode) {
            srcNode.disconnect();
            srcNode = null;
        }

        if (audioCtx) {
            await audioCtx.close();
            audioCtx = null;
        }

        if (micStream) {
            micStream.getTracks().forEach(function (t) { t.stop(); });
            micStream = null;
        }

        if (sessionId) {
            await fetch('/student/sound-check/session/' + sessionId + '/end', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            }).catch(console.error);
        }

        btnStart.disabled = false;
        btnStop.disabled = true;
        setMsg('Session ended.');
    }

    /* Reset UI + state */
    btnReset.addEventListener('click', async function () {
        await stopListening();

        sessionId = null;
        smoothedFreq = 0;
        stableNote = null;
        stableStart = 0;
        targetNote = null;
        targetFreq = null;

        document.querySelectorAll('.str-btn').forEach(function (btn) {
            btn.classList.remove('active', 'detected', 'detected-in-tune');
        });

        elNote.textContent = '--';
        elNote.classList.remove('in-tune', 'flat', 'sharp');
        elFreq.textContent = '-- Hz';
        elCents.textContent = '0 ¢';
        setBadge('neutral', '--');
        setNeedle(0);
        setConf(0);
        clearWave();
        setMsg('Select a string (optional), then press Start.');
        elSelHint.textContent = '';
        elSigHint.textContent = 'Pluck a string clearly...';
    });

    /* Main loop */
    function loop() {
        if (!isListening) return;
        rafId = requestAnimationFrame(loop);

        analyser.getFloatTimeDomainData(timeBuf);
        drawWave(timeBuf);

        const result = acf2Plus(timeBuf, audioCtx.sampleRate);

        if (result.freq === -1) {
            setConf(0);
            elSigHint.textContent = 'No signal — pluck a string clearly.';
            return;
        }

        setConf(result.confidence);

        if (result.confidence < 0.4) {
            elSigHint.textContent = 'Signal too weak — pluck harder.';
        } else if (result.confidence < 0.7) {
            elSigHint.textContent = 'Signal okay — can improve clarity.';
        } else {
            elSigHint.textContent = 'Great signal quality.';
        }

        /* Smooth frequency so display is less jumpy */
        smoothedFreq = smoothedFreq === 0
            ? result.freq
            : smoothedFreq + SMOOTH_ALPHA * (result.freq - smoothedFreq);

        /* Ignore unrealistic frequencies outside open-string guitar range */
        if (smoothedFreq < MIN_VALID_FREQ || smoothedFreq > MAX_VALID_FREQ) {
            setMsg('Detected pitch is outside standard guitar tuning range.');
            return;
        }

        const noteResult = freqToNote(smoothedFreq);
        const fullNote = noteResult.note + noteResult.octave;
        const now = performance.now();

        /* Stability gate to avoid flicker */
        if (fullNote !== stableNote) {
            stableNote = fullNote;
            stableStart = now;
            return;
        }

        if (now - stableStart < STABLE_MS) {
            return;
        }

        elNote.textContent = fullNote;
        elFreq.textContent = smoothedFreq.toFixed(1) + ' Hz';

        let compareTargetNote = targetNote;
        let compareTargetFreq = targetFreq;

        /* If user did not choose a target, use nearest standard guitar string */
        if (!compareTargetFreq) {
            const nearest = getNearestStandardString(smoothedFreq);
            compareTargetNote = nearest.note;
            compareTargetFreq = nearest.freq;
        }

        const cents = getCents(smoothedFreq, compareTargetFreq);
        updateTuning(cents);

        const isInTuneNow = Math.abs(cents) <= CENTS_IN_TUNE;
        highlightDetectedString(compareTargetNote, isInTuneNow);

        maybeSaveEvent(fullNote, smoothedFreq, cents, now, result.confidence);
        setMsg('Listening…');
    }

    function getNearestStandardString(freq) {
        let nearest = STANDARD_STRINGS[0];
        let minCents = Math.abs(getCents(freq, nearest.freq));

        for (let i = 1; i < STANDARD_STRINGS.length; i++) {
            const cents = Math.abs(getCents(freq, STANDARD_STRINGS[i].freq));
            if (cents < minCents) {
                minCents = cents;
                nearest = STANDARD_STRINGS[i];
            }
        }

        return nearest;
    }

    function highlightDetectedString(noteName, isInTune) {
        document.querySelectorAll('.str-btn').forEach(function (btn) {
            btn.classList.remove('detected', 'detected-in-tune');
        });

        const btn = document.querySelector('.str-btn[data-note="' + noteName + '"]');
        if (!btn) return;

        btn.classList.add(isInTune ? 'detected-in-tune' : 'detected');
    }

    /* ACF2+ pitch detection */
    function acf2Plus(inputBuf, sampleRate) {
        let SIZE = inputBuf.length;
        let rms = 0;

        for (let i = 0; i < SIZE; i++) {
            const val = inputBuf[i];
            rms += val * val;
        }

        rms = Math.sqrt(rms / SIZE);
        if (rms < 0.01) {
            return { freq: -1, confidence: 0 };
        }

        let r1 = 0;
        let r2 = SIZE - 1;
        const thres = 0.2;

        for (let i = 0; i < SIZE / 2; i++) {
            if (Math.abs(inputBuf[i]) < thres) {
                r1 = i;
                break;
            }
        }

        for (let i = 1; i < SIZE / 2; i++) {
            if (Math.abs(inputBuf[SIZE - i]) < thres) {
                r2 = SIZE - i;
                break;
            }
        }

        const buf = inputBuf.slice(r1, r2);
        SIZE = buf.length;

        const c = new Array(SIZE).fill(0);
        for (let i = 0; i < SIZE; i++) {
            for (let j = 0; j < SIZE - i; j++) {
                c[i] += buf[j] * buf[j + i];
            }
        }

        let d = 0;
        while (d + 1 < SIZE && c[d] > c[d + 1]) d++;

        let maxval = -1;
        let maxpos = -1;
        for (let i = d; i < SIZE; i++) {
            if (c[i] > maxval) {
                maxval = c[i];
                maxpos = i;
            }
        }

        if (maxpos <= 0) {
            return { freq: -1, confidence: 0 };
        }

        let T0 = maxpos;
        const x1 = (c[T0 - 1] !== undefined) ? c[T0 - 1] : c[T0];
        const x2 = c[T0];
        const x3 = (c[T0 + 1] !== undefined) ? c[T0 + 1] : c[T0];
        const a = (x1 + x3 - 2 * x2) / 2;
        const b = (x3 - x1) / 2;

        if (a) {
            T0 = T0 - b / (2 * a);
        }

        const confidence = (c[0] > 0) ? maxval / c[0] : 0;

        if (!isFinite(T0) || T0 <= 0) {
            return { freq: -1, confidence: 0 };
        }

        return {
            freq: sampleRate / T0,
            confidence: confidence
        };
    }

    function freqToNote(freq) {
        const semi = 12 * Math.log2(freq / 440);
        const midi = Math.round(semi) + 69;
        const note = NOTE_NAMES[((midi % 12) + 12) % 12];
        const octave = Math.floor(midi / 12) - 1;

        return { note: note, octave: octave };
    }

    function getCents(freq, target) {
        return 1200 * Math.log2(freq / target);
    }

    /* Update badge, note color, cents, and needle */
    function updateTuning(cents) {
        elCents.textContent = (cents >= 0 ? '+' : '') + cents.toFixed(1) + ' ¢';
        setNeedle(cents);

        if (Math.abs(cents) <= CENTS_IN_TUNE) {
            setBadge('in_tune', 'In Tune');
            elNote.classList.remove('flat', 'sharp');
            elNote.classList.add('in-tune');
        } else if (cents < 0) {
            setBadge('flat', 'Flat');
            elNote.classList.remove('in-tune', 'sharp');
            elNote.classList.add('flat');
        } else {
            setBadge('sharp', 'Sharp');
            elNote.classList.remove('in-tune', 'flat');
            elNote.classList.add('sharp');
        }
    }

    function setBadge(cls, label) {
        elBadge.className = 'tuning-badge px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider transition-all duration-300 ' + cls;
        elBadge.textContent = label;
    }

    function setNeedle(cents) {
        const clamped = Math.max(-50, Math.min(50, cents));
        const angle = (clamped / 50) * 45;

        if (elNeedle) {
            elNeedle.style.transform = 'translateX(-50%) rotate(' + angle + 'deg)';
        }
    }

    function setConf(val) {
        const pct = Math.round(Math.min(1, Math.max(0, val)) * 100);
        elConf.style.width = pct + '%';

        if (pct > 85) {
            elConf.style.background = 'linear-gradient(90deg, #6fcf97, #27ae60)';
            elConf.style.boxShadow = '0 0 12px rgba(39, 174, 96, 0.30)';
        } else if (pct > 55) {
            elConf.style.background = 'linear-gradient(90deg, #f2c94c, #6fcf97)';
            elConf.style.boxShadow = '0 0 10px rgba(111, 207, 151, 0.22)';
        } else {
            elConf.style.background = 'linear-gradient(90deg, #f2994a, #f2c94c)';
            elConf.style.boxShadow = '0 0 10px rgba(242, 201, 76, 0.18)';
        }

        elConfPc.textContent = pct + '%';
    }

    function drawWave(buf) {
        const W = canvas.offsetWidth || 560;
        const H = canvas.height = 84;
        canvas.width = W;

        ctx2d.clearRect(0, 0, W, H);
        ctx2d.beginPath();
        const gradient = ctx2d.createLinearGradient(0, 0, W, 0);
        gradient.addColorStop(0, 'rgba(242, 201, 76, 0.95)');
        gradient.addColorStop(0.5, 'rgba(111, 207, 151, 0.95)');
        gradient.addColorStop(1, 'rgba(39, 174, 96, 0.90)');
        ctx2d.strokeStyle = gradient;
        ctx2d.lineWidth = 2.4;

        const step = Math.max(1, Math.floor(buf.length / W));
        for (let i = 0; i < W; i++) {
            const v = buf[i * step] || 0;
            const y = (1 - (v + 1) / 2) * H;
            if (i === 0) ctx2d.moveTo(i, y);
            else ctx2d.lineTo(i, y);
        }

        ctx2d.stroke();
    }

    function clearWave() {
        canvas.width = canvas.offsetWidth || 560;
        ctx2d.clearRect(0, 0, canvas.width, canvas.height);
    }

    /* Save session events with throttle */
    function maybeSaveEvent(note, freq, cents, now, confidence) {
        if (!sessionId) return;
        if (now - lastSave < SAVE_DELAY_MS) return;
        if (confidence < MIN_CONFIDENCE_TO_SAVE) return;

        lastSave = now;

        const status = Math.abs(cents) <= CENTS_IN_TUNE
            ? 'in_tune'
            : (cents < 0 ? 'flat' : 'sharp');

        fetch('/student/sound-check/session/' + sessionId + '/event', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                note_name: note,
                frequency: parseFloat(freq.toFixed(2)),
                cents_deviation: parseFloat(cents.toFixed(2)),
                tuning_status: status,
                detected_at: new Date().toISOString(),
            }),
        }).catch(console.error);
    }

    function setMsg(m) {
        elMsg.textContent = m;
    }
})();
</script>
@endpush