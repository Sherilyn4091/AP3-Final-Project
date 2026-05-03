/*
  resources/js/emerging-tech/pitch-monitor-controller.js

  Pitch Monitor Controller

  - silence gap markers
  - graph line break during silence
  - moving time axis
  - stale pitch clearing
  - optional Essentia CDN loading
  - JavaScript fallback support through the processor
*/

(function () {
    'use strict';

    // =====================================================================
    // CONSTANTS
    // =====================================================================

    const AUDIO_CONFIG = {
        bufferSize: 4096,
        frameSize: 2048,
        hopSize: 256,
        minConfidence: 0.12,
        minFrequency: 50,
        maxFrequency: 3951.066,
        saveDelayMs: 800,
        minConfidenceToSave: 0.45,
        rmsThreshold: 0.003,
    };

    const UI_CONFIG = {
        maxPitchPoints: 720,
        smoothingFactor: 0.34,
        canvasRefreshRate: 16,
        stalePitchMs: 1500,
        gapMarkerIntervalMs: 160,
    };

    const GRAPH_CONFIG = {
        minFrequency: 65.406,
        maxFrequency: 3951.066,
        timeWindowSeconds: 12,

        /*
          Wider graph padding

          Purpose:
          - Prevents the left Pitch (Hz) title from overlapping with Hz labels.
          - Gives the right Pitch Class title and labels enough space.
          - Keeps the plotted pitch contour inside a clean readable area.
        */
        leftPadding: 118,
        rightPadding: 112,
        topPadding: 24,
        bottomPadding: 50,
        maxGapMs: 1200,
    };

    const NOTE_NAMES = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];

    const CONFIG = window.pitchMonitorConfig || {};
    const PROCESSOR_FILE_URL = CONFIG.processorUrl;
    const ESSENTIA_WASM_URL = CONFIG.essentiaWasmUrl || 'https://cdn.jsdelivr.net/npm/essentia.js@0.1.3/dist/essentia-wasm.umd.js';
    const ESSENTIA_CORE_URL = CONFIG.essentiaCoreUrl || 'https://cdn.jsdelivr.net/npm/essentia.js@0.1.3/dist/essentia.js-core.umd.js';

    // =====================================================================
    // STATE MANAGEMENT
    // =====================================================================

    const state = {
        audio: {
            context: null,
            mediaStream: null,
            micSource: null,
            workletNode: null,
            muteGain: null,
            analyser: null,
            waveBuffer: null,
            workletBlobUrl: null,
        },
        session: {
            id: null,
            isRunning: false,
            isPaused: false,
            startedAt: null,
            pausedAt: null,
            totalPausedMs: 0,
            elapsedAtStop: 0,
            lastSaveTime: 0,
        },
        pitch: {
            points: [],
            smoothedValue: 0,
            current: 0,
            confidence: 0,
            rms: 0,
            lastDetectedAt: 0,
            lastGapAt: 0,
            lastAlgorithm: '',
        },
        animation: {
            pitchGraphId: null,
            waveformId: null,
            lastPitchDrawTime: 0,
        },
    };

    // =====================================================================
    // DOM ELEMENTS
    // =====================================================================

    const DOM = {
        noteDisplay: document.getElementById('noteDisplay'),
        frequencyDisplay: document.getElementById('frequencyDisplay'),
        centsDisplay: document.getElementById('centsDisplay'),
        nearestNoteText: document.getElementById('nearestNoteText'),
        liveStatusText: document.getElementById('liveStatusText'),
        statusBadge: document.getElementById('statusBadge'),

        confidenceBar: document.getElementById('confidenceBar'),
        confidenceText: document.getElementById('confidenceText'),
        rmsBar: document.getElementById('rmsBar'),
        rmsText: document.getElementById('rmsText'),

        pitchCanvas: document.getElementById('pitchCanvas'),
        pitchCtx: document.getElementById('pitchCanvas')?.getContext('2d'),
        waveCanvas: document.getElementById('waveCanvas'),
        waveCtx: document.getElementById('waveCanvas')?.getContext('2d'),

        btnStart: document.getElementById('btnStart'),
        btnPause: document.getElementById('btnPause'),
        btnResume: document.getElementById('btnResume'),
        btnReset: document.getElementById('btnReset'),
    };

    // =====================================================================
    // UTILITY FUNCTIONS
    // =====================================================================

    function frequencyToNote(frequency) {
        const midi = Math.round(12 * Math.log2(frequency / 440) + 69);
        const noteIndex = ((midi % 12) + 12) % 12;
        const octave = Math.floor(midi / 12) - 1;

        return {
            midi,
            note: NOTE_NAMES[noteIndex],
            octave,
            full: NOTE_NAMES[noteIndex] + octave,
        };
    }

    function midiToFrequency(midi) {
        return 440 * Math.pow(2, (midi - 69) / 12);
    }

    function getCentsDeviation(frequency, midi) {
        const reference = midiToFrequency(midi);
        return 1200 * Math.log2(frequency / reference);
    }

    function getTuningStatus(cents) {
        const threshold = 10;

        if (Math.abs(cents) <= threshold) {
            return 'in_tune';
        }

        return cents < 0 ? 'flat' : 'sharp';
    }

    async function createWorkletUrl(files) {
        const texts = [];

        for (const item of files) {
            const file = typeof item === 'string' ? item : item.url;
            const optional = typeof item === 'object' && item.optional;

            try {
                const response = await fetch(file);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                texts.push(await response.text());
            } catch (error) {
                if (optional) {
                    console.warn(`Optional AudioWorklet dependency skipped: ${file}`, error);
                    continue;
                }

                throw new Error(`Unable to load AudioWorklet dependency: ${file}`);
            }
        }

        texts.unshift('var exports = {};');

        const blob = new Blob([texts.join('\n')], {
            type: 'application/javascript',
        });

        return URL.createObjectURL(blob);
    }

    function buildSessionUrl(template, sessionId) {
        return String(template || '').replace('__SESSION_ID__', encodeURIComponent(sessionId));
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    /*
      Pause-safe elapsed time helper

      Purpose:
      - While running, time continues.
      - While paused, time freezes.
      - After reset, the graph returns to the original 12s to 0s idle window.
    */
    function getElapsedSeconds() {
        if (state.session.isRunning && state.session.startedAt) {
            const now = state.session.isPaused && state.session.pausedAt
                ? state.session.pausedAt
                : performance.now();

            return Math.max(
                0,
                (now - state.session.startedAt - state.session.totalPausedMs) / 1000
            );
        }

        return state.session.elapsedAtStop || 0;
    }

    function formatSeconds(seconds) {
        if (seconds < 10) {
            return seconds.toFixed(1) + 's';
        }

        return Math.round(seconds) + 's';
    }

    function trimPitchPoints() {
        const now = performance.now();
        const keepForMs = (GRAPH_CONFIG.timeWindowSeconds + 1) * 1000;

        while (
            state.pitch.points.length > 0 &&
            now - state.pitch.points[0].time > keepForMs
        ) {
            state.pitch.points.shift();
        }

        while (state.pitch.points.length > UI_CONFIG.maxPitchPoints) {
            state.pitch.points.shift();
        }
    }

    function pushGapPoint() {
        const now = performance.now();

        if (now - state.pitch.lastGapAt < UI_CONFIG.gapMarkerIntervalMs) {
            return;
        }

        state.pitch.points.push({
            time: now,
            pitch: 0,
            confidence: 0,
            algorithm: 'gap',
        });

        state.pitch.lastGapAt = now;
        trimPitchPoints();
    }

    // =====================================================================
    // UI MANAGER
    // =====================================================================

    const UIManager = {
        setStatusBadge(text, bgColor = '#EEF2F4', textColor = '#44576D') {
            if (!DOM.statusBadge) return;

            DOM.statusBadge.textContent = text;
            DOM.statusBadge.style.backgroundColor = bgColor;
            DOM.statusBadge.style.color = textColor;
        },

        resetAll() {
            if (DOM.noteDisplay) DOM.noteDisplay.textContent = '--';
            if (DOM.frequencyDisplay) DOM.frequencyDisplay.textContent = '-- Hz';
            if (DOM.centsDisplay) DOM.centsDisplay.textContent = '0.0 ¢';
            if (DOM.confidenceBar) DOM.confidenceBar.style.width = '0%';
            if (DOM.confidenceText) DOM.confidenceText.textContent = '0%';
            if (DOM.rmsBar) DOM.rmsBar.style.width = '0%';
            if (DOM.rmsText) DOM.rmsText.textContent = '0.0000';
            if (DOM.nearestNoteText) DOM.nearestNoteText.textContent = '--';

            if (DOM.liveStatusText) {
                DOM.liveStatusText.textContent = 'Press Start to begin microphone-based pitch monitoring.';
            }

            this.setStatusBadge('Idle');

            state.pitch.points = [];
            state.pitch.smoothedValue = 0;
            state.pitch.current = 0;
            state.pitch.confidence = 0;
            state.pitch.rms = 0;
            state.pitch.lastDetectedAt = 0;
            state.pitch.lastGapAt = 0;
            state.pitch.lastAlgorithm = '';

            state.session.isRunning = false;
            state.session.isPaused = false;
            state.session.startedAt = null;
            state.session.pausedAt = null;
            state.session.totalPausedMs = 0;
            state.session.elapsedAtStop = 0;
            state.session.lastSaveTime = 0;

            this.setButtonState('idle');

            CanvasManager.drawPitchContour();
            CanvasManager.clearWaveform();
        },

        updatePitchDisplay(noteInfo, cents) {
            if (DOM.noteDisplay) DOM.noteDisplay.textContent = noteInfo.full;

            if (DOM.frequencyDisplay) {
                DOM.frequencyDisplay.textContent = state.pitch.smoothedValue.toFixed(2) + ' Hz';
            }

            if (DOM.centsDisplay) {
                DOM.centsDisplay.textContent = (cents >= 0 ? '+' : '') + cents.toFixed(2) + ' ¢';
            }

            if (DOM.nearestNoteText) DOM.nearestNoteText.textContent = noteInfo.full;
        },

        updateConfidenceMeter(confidence) {
            const percent = Math.round(clamp((confidence || 0) * 100, 0, 100));

            if (DOM.confidenceBar) {
                DOM.confidenceBar.style.width = percent + '%';

                if (percent < 40) {
                    DOM.confidenceBar.style.background = 'linear-gradient(90deg, #959D90, #768A96)';
                } else if (percent < 70) {
                    DOM.confidenceBar.style.background = 'linear-gradient(90deg, #768A96, #5E81AC)';
                } else {
                    DOM.confidenceBar.style.background = 'linear-gradient(90deg, #88C0D0, #223030)';
                }
            }

            if (DOM.confidenceText) DOM.confidenceText.textContent = percent + '%';
        },

        updateSignalMeter(rms) {
            const safeRms = Math.max(0, rms || 0);
            const percent = clamp(Math.sqrt(safeRms) * 450, 0, 100);

            if (DOM.rmsBar) {
                DOM.rmsBar.style.width = percent + '%';

                if (percent < 55) {
                    DOM.rmsBar.style.background = 'linear-gradient(90deg, #6FAF7A, #A3BE8C)';
                } else if (percent < 82) {
                    DOM.rmsBar.style.background = 'linear-gradient(90deg, #A3BE8C, #EBCB8B)';
                } else {
                    DOM.rmsBar.style.background = 'linear-gradient(90deg, #EBCB8B, #BF616A)';
                }
            }

            if (DOM.rmsText) DOM.rmsText.textContent = safeRms.toFixed(4);
        },

        updateStatusMessage(status) {
            const messages = {
                in_tune: {
                    text: 'Stable pitch detected and close to the nearest tempered note.',
                    badge: 'In Tune',
                    bg: '#F1F3EF',
                    color: '#223030',
                },
                flat: {
                    text: 'Pitch detected below the nearest tempered note.',
                    badge: 'Flat',
                    bg: '#EEF2F4',
                    color: '#44576D',
                },
                sharp: {
                    text: 'Pitch detected above the nearest tempered note.',
                    badge: 'Sharp',
                    bg: '#F6EFEC',
                    color: '#523D35',
                },
            };

            const msg = messages[status] || messages.sharp;

            if (DOM.liveStatusText) DOM.liveStatusText.textContent = msg.text;
            this.setStatusBadge(msg.badge, msg.bg, msg.color);
        },

        updateListeningState(message = 'Listening... no stable pitch detected yet.') {
            if (DOM.liveStatusText) {
                DOM.liveStatusText.textContent = message;
            }

            this.setStatusBadge('Listening', '#EEF2F4', '#44576D');
        },

        clearStalePitchReadout() {
            if (!state.session.isRunning || state.session.isPaused) return;
            if (!state.pitch.lastDetectedAt) return;

            const staleFor = performance.now() - state.pitch.lastDetectedAt;

            if (staleFor < UI_CONFIG.stalePitchMs) {
                return;
            }

            if (DOM.noteDisplay) DOM.noteDisplay.textContent = '--';
            if (DOM.frequencyDisplay) DOM.frequencyDisplay.textContent = '-- Hz';
            if (DOM.centsDisplay) DOM.centsDisplay.textContent = '0.0 ¢';
            if (DOM.nearestNoteText) DOM.nearestNoteText.textContent = '--';

            this.updateListeningState();
        },

        /*
          Button state manager

          Modes:
          - idle: Start enabled, Pause/Resume disabled, Reset enabled
          - starting: all recording controls disabled while microphone loads
          - running: Start disabled, Pause enabled, Resume disabled, Reset enabled
          - paused: Start disabled, Pause disabled, Resume enabled, Reset enabled
        */
        setButtonState(mode = 'idle') {
            const isIdle = mode === 'idle';
            const isStarting = mode === 'starting';
            const isRunning = mode === 'running';
            const isPaused = mode === 'paused';

            if (DOM.btnStart) {
                DOM.btnStart.disabled = isStarting || isRunning || isPaused;
            }

            if (DOM.btnPause) {
                DOM.btnPause.disabled = !isRunning;
            }

            if (DOM.btnResume) {
                DOM.btnResume.disabled = !isPaused;
            }

            if (DOM.btnReset) {
                DOM.btnReset.disabled = isStarting && !isIdle;
            }
        },

        showError(message) {
            this.setStatusBadge('Error', '#F6EFEC', '#523D35');

            if (DOM.liveStatusText) {
                DOM.liveStatusText.textContent = message;
            }
        },
    };

    // =====================================================================
    // CANVAS MANAGER
    // =====================================================================

    const CanvasManager = {
        drawPitchContour() {
            const canvas = DOM.pitchCanvas;
            const ctx = DOM.pitchCtx;

            if (!canvas || !ctx) return;

            const size = this._resizeCanvas(canvas);
            const width = size.width;
            const height = size.height;

            this._pruneOldPoints();
            this._drawGraphBackground(ctx, width, height);
            this._drawSemitoneGridLines(ctx, width, height);
            this._drawHzGuides(ctx, width, height);
            this._drawPitchClassLabels(ctx, width, height);
            this._drawTimeLabels(ctx, width, height);
            this._drawContourLine(ctx, width, height);
            this._drawGraphAxisTitles(ctx, width, height);
        },

        _resizeCanvas(canvas) {
            const rect = canvas.getBoundingClientRect();
            const devicePixelRatio = window.devicePixelRatio || 1;
            const cssWidth = Math.max(640, Math.floor(rect.width || canvas.clientWidth || 900));
            const cssHeight = Math.max(320, Math.floor(rect.height || canvas.clientHeight || 520));

            if (canvas.width !== Math.floor(cssWidth * devicePixelRatio)) {
                canvas.width = Math.floor(cssWidth * devicePixelRatio);
            }

            if (canvas.height !== Math.floor(cssHeight * devicePixelRatio)) {
                canvas.height = Math.floor(cssHeight * devicePixelRatio);
            }

            const ctx = canvas.getContext('2d');
            ctx.setTransform(devicePixelRatio, 0, 0, devicePixelRatio, 0, 0);

            return {
                width: cssWidth,
                height: cssHeight,
            };
        },

        _drawGraphBackground(ctx, width, height) {
            ctx.clearRect(0, 0, width, height);

            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, width, height);

            ctx.strokeStyle = '#D8DDD8';
            ctx.lineWidth = 1;
            ctx.strokeRect(
                GRAPH_CONFIG.leftPadding,
                GRAPH_CONFIG.topPadding,
                width - GRAPH_CONFIG.leftPadding - GRAPH_CONFIG.rightPadding,
                height - GRAPH_CONFIG.topPadding - GRAPH_CONFIG.bottomPadding
            );
        },

        _drawSemitoneGridLines(ctx, width, height) {
            const chartLeft = GRAPH_CONFIG.leftPadding;
            const chartRight = width - GRAPH_CONFIG.rightPadding;
            const minMidi = Math.floor(12 * Math.log2(GRAPH_CONFIG.minFrequency / 440) + 69);
            const maxMidi = Math.ceil(12 * Math.log2(GRAPH_CONFIG.maxFrequency / 440) + 69);

            ctx.lineWidth = 1;

            for (let midi = minMidi; midi <= maxMidi; midi++) {
                const noteName = NOTE_NAMES[((midi % 12) + 12) % 12];
                const frequency = midiToFrequency(midi);

                if (frequency < GRAPH_CONFIG.minFrequency || frequency > GRAPH_CONFIG.maxFrequency) {
                    continue;
                }

                const y = this._frequencyToY(frequency, height);
                const isNaturalNote = !noteName.includes('#');

                ctx.strokeStyle = isNaturalNote
                    ? 'rgba(118, 138, 150, 0.14)'
                    : 'rgba(118, 138, 150, 0.055)';

                ctx.beginPath();
                ctx.moveTo(chartLeft, y);
                ctx.lineTo(chartRight, y);
                ctx.stroke();
            }
        },

        _drawHzGuides(ctx, width, height) {
            const hzLines = [65.406, 100, 200, 500, 1000, 2000, 3951.066];
            const chartLeft = GRAPH_CONFIG.leftPadding;
            const chartRight = width - GRAPH_CONFIG.rightPadding;
            const labelX = Math.max(24, chartLeft - 92);

            ctx.lineWidth = 1;
            ctx.font = '11px Inter, sans-serif';
            ctx.textBaseline = 'middle';
            ctx.textAlign = 'left';

            hzLines.forEach((hz) => {
                const y = this._frequencyToY(hz, height);

                ctx.strokeStyle = '#EEF1EC';
                ctx.beginPath();
                ctx.moveTo(chartLeft, y);
                ctx.lineTo(chartRight, y);
                ctx.stroke();

                ctx.fillStyle = '#768A96';

                const label = hz < 100
                    ? hz.toFixed(3) + ' Hz'
                    : Math.round(hz).toLocaleString() + ' Hz';

                ctx.fillText(label, labelX, y);
            });

            ctx.textAlign = 'start';
        },

        _drawPitchClassLabels(ctx, width, height) {
            const chartRight = width - GRAPH_CONFIG.rightPadding;
            const labelX = chartRight + 12;
            const minMidi = Math.floor(12 * Math.log2(GRAPH_CONFIG.minFrequency / 440) + 69);
            const maxMidi = Math.ceil(12 * Math.log2(GRAPH_CONFIG.maxFrequency / 440) + 69);
            const allowedNotes = ['C', 'E', 'G', 'A'];
            const currentNote = state.pitch.lastDetectedAt
                ? frequencyToNote(state.pitch.current || state.pitch.smoothedValue || 0).note
                : null;
            let lastLabelY = null;

            ctx.textBaseline = 'middle';
            ctx.textAlign = 'left';

            for (let midi = minMidi; midi <= maxMidi; midi++) {
                const noteName = NOTE_NAMES[((midi % 12) + 12) % 12];

                if (!allowedNotes.includes(noteName)) {
                    continue;
                }

                const frequency = midiToFrequency(midi);

                if (frequency < GRAPH_CONFIG.minFrequency || frequency > GRAPH_CONFIG.maxFrequency) {
                    continue;
                }

                const y = this._frequencyToY(frequency, height);

                if (y < GRAPH_CONFIG.topPadding + 8 || y > height - GRAPH_CONFIG.bottomPadding - 8) {
                    continue;
                }

                if (lastLabelY !== null && Math.abs(y - lastLabelY) < 13) {
                    continue;
                }

                const octave = Math.floor(midi / 12) - 1;
                const isActive = currentNote === noteName;

                ctx.font = isActive ? 'bold 10px Inter, sans-serif' : '10px Inter, sans-serif';
                ctx.fillStyle = isActive ? '#223030' : '#768A96';
                ctx.fillText(noteName + octave, labelX, y);
                lastLabelY = y;
            }

            ctx.textAlign = 'start';
        },

        _drawTimeLabels(ctx, width, height) {
            const chartLeft = GRAPH_CONFIG.leftPadding;
            const chartRight = width - GRAPH_CONFIG.rightPadding;
            const chartWidth = chartRight - chartLeft;
            const bottomY = height - 20;
            const steps = 6;
            const elapsed = getElapsedSeconds();

            ctx.font = '10px Inter, sans-serif';
            ctx.fillStyle = '#768A96';
            ctx.textBaseline = 'alphabetic';
            ctx.textAlign = 'center';

            for (let i = 0; i <= steps; i++) {
                const x = chartLeft + (chartWidth / steps) * i;
                let label;

                if (!state.session.isRunning && elapsed === 0) {
                    const ageSeconds = GRAPH_CONFIG.timeWindowSeconds - ((GRAPH_CONFIG.timeWindowSeconds / steps) * i);
                    label = Math.round(ageSeconds) + 's';
                } else {
                    const leftTime = Math.max(0, elapsed - GRAPH_CONFIG.timeWindowSeconds);
                    const rightTime = elapsed;
                    const progress = i / steps;
                    const timeValue = leftTime + ((rightTime - leftTime) * progress);

                    label = formatSeconds(timeValue);
                }

                ctx.fillText(label, x, bottomY);
            }

            ctx.textAlign = 'start';
        },

        _drawGraphAxisTitles(ctx, width, height) {
            const chartRight = width - GRAPH_CONFIG.rightPadding;

            ctx.font = '11px Inter, sans-serif';
            ctx.fillStyle = '#768A96';
            ctx.textBaseline = 'alphabetic';
            ctx.textAlign = 'center';

            ctx.fillText('Time (seconds)', Math.floor(width / 2), height - 5);

            ctx.save();
            ctx.translate(12, Math.floor(height / 2));
            ctx.rotate(-Math.PI / 2);
            ctx.fillText('Pitch (Hz)', 0, 0);
            ctx.restore();

            ctx.save();
            ctx.translate(chartRight + 78, Math.floor(height / 2));
            ctx.rotate(Math.PI / 2);
            ctx.fillText('Pitch Class', 0, 0);
            ctx.restore();

            ctx.textAlign = 'start';
        },

        _drawContourLine(ctx, width, height) {
            const now = performance.now();
            const chartLeft = GRAPH_CONFIG.leftPadding;
            const chartRight = width - GRAPH_CONFIG.rightPadding;
            const chartWidth = chartRight - chartLeft;

            const validPoints = state.pitch.points.filter((point) => {
                const age = (now - point.time) / 1000;
                return age <= GRAPH_CONFIG.timeWindowSeconds;
            });

            if (validPoints.length === 0) {
                return;
            }

            const gradient = ctx.createLinearGradient(chartLeft, 0, chartRight, 0);
            gradient.addColorStop(0, 'rgba(118, 138, 150, 0.20)');
            gradient.addColorStop(0.45, 'rgba(68, 87, 109, 0.72)');
            gradient.addColorStop(1, 'rgba(34, 48, 48, 0.98)');

            ctx.strokeStyle = gradient;
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.shadowColor = 'rgba(34, 48, 48, 0.14)';
            ctx.shadowBlur = 8;

            ctx.beginPath();

            let hasStarted = false;
            let previousPoint = null;

            validPoints.forEach((point) => {
                const age = (now - point.time) / 1000;
                const x = chartRight - ((age / GRAPH_CONFIG.timeWindowSeconds) * chartWidth);

                if (!point.pitch || point.pitch <= 0) {
                    hasStarted = false;
                    previousPoint = point;
                    return;
                }

                const y = this._frequencyToY(point.pitch, height);

                const hasLargeGap = previousPoint
                    ? point.time - previousPoint.time > GRAPH_CONFIG.maxGapMs
                    : false;

                if (!hasStarted || hasLargeGap) {
                    ctx.moveTo(x, y);
                    hasStarted = true;
                } else {
                    ctx.lineTo(x, y);
                }

                previousPoint = point;
            });

            ctx.stroke();
            ctx.shadowBlur = 0;

            const latestVoicedPoint = [...validPoints].reverse().find((point) => {
                return point.pitch && point.pitch > 0;
            });

            if (!latestVoicedPoint) {
                return;
            }

            const latestAge = (now - latestVoicedPoint.time) / 1000;
            const latestX = chartRight - ((latestAge / GRAPH_CONFIG.timeWindowSeconds) * chartWidth);
            const latestY = this._frequencyToY(latestVoicedPoint.pitch, height);

            ctx.fillStyle = '#223030';
            ctx.beginPath();
            ctx.arc(latestX, latestY, 4, 0, Math.PI * 2);
            ctx.fill();
        },

        _frequencyToY(frequency, height) {
            const chartTop = GRAPH_CONFIG.topPadding;
            const chartBottom = height - GRAPH_CONFIG.bottomPadding;
            const chartHeight = chartBottom - chartTop;

            const clampedFrequency = clamp(
                frequency,
                GRAPH_CONFIG.minFrequency,
                GRAPH_CONFIG.maxFrequency
            );

            const ratio = Math.log(clampedFrequency / GRAPH_CONFIG.minFrequency)
                / Math.log(GRAPH_CONFIG.maxFrequency / GRAPH_CONFIG.minFrequency);

            return chartBottom - (ratio * chartHeight);
        },

        _pruneOldPoints() {
            trimPitchPoints();
        },

        startPitchLoop() {
            const animate = (timestamp) => {
                if (!state.session.isRunning || state.session.isPaused) {
                    return;
                }

                if (
                    !state.animation.lastPitchDrawTime ||
                    timestamp - state.animation.lastPitchDrawTime >= UI_CONFIG.canvasRefreshRate
                ) {
                    UIManager.clearStalePitchReadout();
                    this.drawPitchContour();
                    state.animation.lastPitchDrawTime = timestamp;
                }

                state.animation.pitchGraphId = requestAnimationFrame(animate);
            };

            state.animation.pitchGraphId = requestAnimationFrame(animate);
        },

        drawWaveform() {
            if (!state.audio.analyser || !state.audio.waveBuffer || !DOM.waveCanvas || !DOM.waveCtx) {
                return;
            }

            const canvas = DOM.waveCanvas;
            const ctx = DOM.waveCtx;
            const size = this._resizeCanvas(canvas);
            const width = size.width;
            const height = size.height;

            state.audio.analyser.getFloatTimeDomainData(state.audio.waveBuffer);

            ctx.clearRect(0, 0, width, height);

            const gradient = ctx.createLinearGradient(0, 0, width, 0);
            gradient.addColorStop(0, '#6FAF7A');
            gradient.addColorStop(0.55, '#EBCB8B');
            gradient.addColorStop(1, '#BF616A');

            ctx.strokeStyle = gradient;
            ctx.lineWidth = 2.4;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            ctx.beginPath();

            const step = Math.max(1, Math.floor(state.audio.waveBuffer.length / width));

            for (let i = 0; i < width; i++) {
                const sample = state.audio.waveBuffer[i * step] || 0;
                const y = (1 - ((sample + 1) / 2)) * height;

                if (i === 0) {
                    ctx.moveTo(i, y);
                } else {
                    ctx.lineTo(i, y);
                }
            }

            ctx.stroke();

            ctx.strokeStyle = 'rgba(34, 48, 48, 0.12)';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(0, height / 2);
            ctx.lineTo(width, height / 2);
            ctx.stroke();

            if (state.session.isRunning && !state.session.isPaused) {
                state.animation.waveformId = requestAnimationFrame(() => {
                    CanvasManager.drawWaveform();
                });
            }
        },

        clearWaveform() {
            if (!DOM.waveCanvas || !DOM.waveCtx) {
                return;
            }

            const canvas = DOM.waveCanvas;
            const ctx = DOM.waveCtx;
            const size = this._resizeCanvas(canvas);

            ctx.clearRect(0, 0, size.width, size.height);
        },
    };

    // =====================================================================
    // BACKEND API
    // =====================================================================

    const BackendAPI = {
        async startSession() {
            const response = await fetch(CONFIG.startSessionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CONFIG.csrfToken,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    source_type: 'microphone',
                }),
            });

            const text = await response.text();
            let data = {};

            try {
                data = text ? JSON.parse(text) : {};
            } catch (error) {
                data = {
                    message: text || 'Invalid JSON response from server.',
                };
            }

            if (!response.ok) {
                throw new Error(data.message || 'Failed to start pitch monitor session.');
            }

            return data.session_id;
        },

        async endSession(sessionId) {
            if (!sessionId) return;

            await fetch(buildSessionUrl(CONFIG.endSessionUrlTemplate, sessionId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CONFIG.csrfToken,
                    Accept: 'application/json',
                },
            });
        },

        async saveEvent(sessionId, eventData) {
            if (!sessionId) return;

            const now = Date.now();

            if (now - state.session.lastSaveTime < AUDIO_CONFIG.saveDelayMs) {
                return;
            }

            if ((eventData.confidence || 0) < AUDIO_CONFIG.minConfidenceToSave) {
                return;
            }

            state.session.lastSaveTime = now;

            try {
                await fetch(buildSessionUrl(CONFIG.storeEventUrlTemplate, sessionId), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CONFIG.csrfToken,
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({
                        note_name: eventData.note_name,
                        frequency: Number(eventData.frequency.toFixed(2)),
                        cents_deviation: Number(eventData.cents_deviation.toFixed(2)),
                        confidence: Number((eventData.confidence || 0).toFixed(4)),
                        rms: Number((eventData.rms || 0).toFixed(5)),
                        tuning_status: eventData.tuning_status,
                        detected_at: new Date().toISOString(),
                    }),
                });
            } catch (error) {
                console.error('Failed to save pitch event:', error);
            }
        },
    };

    // =====================================================================
    // AUDIO MANAGER
    // =====================================================================

    const AudioManager = {
        async start() {
            try {
                if (!PROCESSOR_FILE_URL || !CONFIG.startSessionUrl || !CONFIG.csrfToken) {
                    throw new Error('Pitch Monitor configuration is missing.');
                }

                UIManager.resetAll();
                UIManager.setButtonState('starting');
                UIManager.setStatusBadge('Starting', '#EEF2F4', '#44576D');

                if (DOM.liveStatusText) {
                    DOM.liveStatusText.textContent = 'Starting microphone and audio analyzer...';
                }

                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                state.audio.context = new AudioContextClass({
                    latencyHint: 'interactive',
                });

                state.audio.mediaStream = await navigator.mediaDevices.getUserMedia({
                    audio: {
                        echoCancellation: false,
                        noiseSuppression: false,
                        autoGainControl: false,
                        channelCount: 1,
                    },
                    video: false,
                });

                if (state.audio.context.state === 'suspended') {
                    await state.audio.context.resume();
                }

                state.audio.micSource = state.audio.context.createMediaStreamSource(state.audio.mediaStream);
                state.audio.analyser = state.audio.context.createAnalyser();
                state.audio.analyser.fftSize = 4096;
                state.audio.analyser.smoothingTimeConstant = 0.08;
                state.audio.waveBuffer = new Float32Array(state.audio.analyser.fftSize);

                state.audio.muteGain = state.audio.context.createGain();
                state.audio.muteGain.gain.setValueAtTime(0, state.audio.context.currentTime);

                state.audio.workletBlobUrl = await createWorkletUrl([
                    {
                        url: ESSENTIA_WASM_URL,
                        optional: true,
                    },
                    {
                        url: ESSENTIA_CORE_URL,
                        optional: true,
                    },
                    PROCESSOR_FILE_URL,
                ]);

                await state.audio.context.audioWorklet.addModule(state.audio.workletBlobUrl);

                state.audio.workletNode = new AudioWorkletNode(state.audio.context, 'pitch-monitor-processor', {
                    processorOptions: {
                        bufferSize: AUDIO_CONFIG.bufferSize,
                        frameSize: AUDIO_CONFIG.frameSize,
                        hopSize: AUDIO_CONFIG.hopSize,
                        sampleRate: state.audio.context.sampleRate,
                        minFrequency: AUDIO_CONFIG.minFrequency,
                        maxFrequency: AUDIO_CONFIG.maxFrequency,
                        rmsThreshold: AUDIO_CONFIG.rmsThreshold,
                    },
                });

                state.audio.workletNode.port.onmessage = (event) => {
                    this.onPitchDetected(event.data);
                };

                state.audio.micSource.connect(state.audio.analyser);
                state.audio.micSource.connect(state.audio.workletNode);
                state.audio.workletNode.connect(state.audio.muteGain);
                state.audio.muteGain.connect(state.audio.context.destination);

                state.session.id = await BackendAPI.startSession();
                state.session.isRunning = true;
                state.session.isPaused = false;
                state.session.startedAt = performance.now();
                state.session.pausedAt = null;
                state.session.totalPausedMs = 0;
                state.session.elapsedAtStop = 0;

                UIManager.setButtonState('running');
                UIManager.setStatusBadge('Listening', '#EEF2F4', '#44576D');

                if (DOM.liveStatusText) {
                    DOM.liveStatusText.textContent = 'Listening to microphone input. Play or sing one clear note at a time.';
                }

                CanvasManager.startPitchLoop();
                CanvasManager.drawWaveform();
            } catch (error) {
                console.error('Failed to start pitch monitor:', error);
                UIManager.showError(error.message || 'Unable to start Pitch Monitor. The session could not be created.');
                await this.stop(false);
            }
        },

        onPitchDetected(data) {
            if (state.session.isPaused) {
                return;
            }

            const { pitch, confidence, rms, algorithm } = data || {};

            state.pitch.rms = rms || 0;
            state.pitch.confidence = confidence || 0;
            state.pitch.lastAlgorithm = algorithm || '';

            UIManager.updateSignalMeter(rms || 0);
            UIManager.updateConfidenceMeter(confidence || 0);

            if (!pitch || pitch < AUDIO_CONFIG.minFrequency || pitch > AUDIO_CONFIG.maxFrequency) {
                pushGapPoint();

                if ((rms || 0) >= AUDIO_CONFIG.rmsThreshold) {
                    UIManager.updateListeningState('Sound detected, but no stable single pitch yet. Try one clear note closer to the microphone.');
                }

                return;
            }

            if ((confidence || 0) < AUDIO_CONFIG.minConfidence) {
                pushGapPoint();
                UIManager.updateListeningState('Pitch is present, but confidence is still low. Hold one note steadily.');
                return;
            }

            if (state.pitch.smoothedValue === 0) {
                state.pitch.smoothedValue = pitch;
            } else {
                const centsJump = Math.abs(1200 * Math.log2(pitch / state.pitch.smoothedValue));
                const adaptiveSmoothing = centsJump > 180 ? 0.72 : UI_CONFIG.smoothingFactor;

                state.pitch.smoothedValue =
                    (state.pitch.smoothedValue * (1 - adaptiveSmoothing)) +
                    (pitch * adaptiveSmoothing);
            }

            const noteInfo = frequencyToNote(state.pitch.smoothedValue);
            const cents = getCentsDeviation(state.pitch.smoothedValue, noteInfo.midi);
            const status = getTuningStatus(cents);

            state.pitch.current = state.pitch.smoothedValue;
            state.pitch.confidence = confidence;
            state.pitch.rms = rms;
            state.pitch.lastDetectedAt = performance.now();

            state.pitch.points.push({
                time: performance.now(),
                pitch: state.pitch.smoothedValue,
                confidence: confidence || 0,
                algorithm: algorithm || 'hybrid',
            });

            trimPitchPoints();

            UIManager.updatePitchDisplay(noteInfo, cents);
            UIManager.updateStatusMessage(status);

            BackendAPI.saveEvent(state.session.id, {
                note_name: noteInfo.full,
                frequency: state.pitch.smoothedValue,
                cents_deviation: cents,
                confidence: confidence || 0,
                rms: rms || 0,
                tuning_status: status,
            });
        },

        pause() {
            if (!state.session.isRunning || state.session.isPaused) {
                return;
            }

            state.session.isPaused = true;
            state.session.pausedAt = performance.now();

            if (state.animation.pitchGraphId) {
                cancelAnimationFrame(state.animation.pitchGraphId);
                state.animation.pitchGraphId = null;
            }

            if (state.animation.waveformId) {
                cancelAnimationFrame(state.animation.waveformId);
                state.animation.waveformId = null;
            }

            UIManager.setButtonState('paused');
            UIManager.setStatusBadge('Paused', '#EEF2F4', '#44576D');

            if (DOM.liveStatusText) {
                DOM.liveStatusText.textContent = 'Pitch monitor paused. The current display is frozen until Resume is clicked.';
            }

            CanvasManager.drawPitchContour();
        },

        resume() {
            if (!state.session.isRunning || !state.session.isPaused) {
                return;
            }

            const pauseDuration = performance.now() - state.session.pausedAt;

            state.session.totalPausedMs += pauseDuration;
            state.session.pausedAt = null;
            state.session.isPaused = false;

            /*
              Keep the graph visually frozen during pause.

              Without shifting point timestamps, old pitch points would age while
              paused and suddenly jump/disappear when Resume is clicked.
            */
            state.pitch.points = state.pitch.points.map((point) => ({
                ...point,
                time: point.time + pauseDuration,
            }));

            if (state.pitch.lastDetectedAt) {
                state.pitch.lastDetectedAt += pauseDuration;
            }

            if (state.pitch.lastGapAt) {
                state.pitch.lastGapAt += pauseDuration;
            }

            UIManager.setButtonState('running');
            UIManager.setStatusBadge('Listening', '#EEF2F4', '#44576D');

            if (DOM.liveStatusText) {
                DOM.liveStatusText.textContent = 'Listening resumed. Continue playing or singing one clear note at a time.';
            }

            CanvasManager.startPitchLoop();
            CanvasManager.drawWaveform();
        },

        async stop(shouldEndSession = true) {
            if (state.session.startedAt) {
                state.session.elapsedAtStop = getElapsedSeconds();
            }

            state.session.isRunning = false;
            state.session.isPaused = false;
            state.session.pausedAt = null;

            if (state.animation.pitchGraphId) {
                cancelAnimationFrame(state.animation.pitchGraphId);
                state.animation.pitchGraphId = null;
            }

            if (state.animation.waveformId) {
                cancelAnimationFrame(state.animation.waveformId);
                state.animation.waveformId = null;
            }

            if (state.audio.mediaStream) {
                state.audio.mediaStream.getTracks().forEach((track) => track.stop());
                state.audio.mediaStream = null;
            }

            try {
                state.audio.micSource?.disconnect();
                state.audio.workletNode?.disconnect();
                state.audio.muteGain?.disconnect();
                state.audio.analyser?.disconnect();
            } catch (error) {
                console.warn('Audio disconnect warning:', error);
            }

            state.audio.micSource = null;
            state.audio.workletNode = null;
            state.audio.muteGain = null;
            state.audio.analyser = null;
            state.audio.waveBuffer = null;

            if (state.audio.context) {
                await state.audio.context.close();
                state.audio.context = null;
            }

            if (state.audio.workletBlobUrl) {
                URL.revokeObjectURL(state.audio.workletBlobUrl);
                state.audio.workletBlobUrl = null;
            }

            if (shouldEndSession && state.session.id) {
                await BackendAPI.endSession(state.session.id);
            }

            state.session.id = null;
            state.session.startedAt = null;
            state.session.pausedAt = null;
            state.session.totalPausedMs = 0;

            UIManager.setButtonState('idle');

            if (shouldEndSession) {
                UIManager.setStatusBadge('Stopped', '#F6EFEC', '#523D35');

                if (DOM.liveStatusText) {
                    DOM.liveStatusText.textContent = 'Pitch monitor stopped.';
                }
            }

            CanvasManager.drawPitchContour();
        },
    };

    // =====================================================================
    // EVENT HANDLERS
    // =====================================================================

    DOM.btnStart?.addEventListener('click', () => {
        AudioManager.start();
    });

    DOM.btnPause?.addEventListener('click', () => {
        AudioManager.pause();
    });

    DOM.btnResume?.addEventListener('click', () => {
        AudioManager.resume();
    });

    DOM.btnReset?.addEventListener('click', async () => {
        await AudioManager.stop();
        UIManager.resetAll();
    });

    window.addEventListener('resize', () => {
        CanvasManager.drawPitchContour();
        CanvasManager.clearWaveform();
    });

    UIManager.resetAll();
})();