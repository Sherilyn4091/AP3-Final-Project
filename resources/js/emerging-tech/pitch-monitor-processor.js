/*
  resources/js/emerging-tech/pitch-monitor-processor.js

  AudioWorklet Processor for real-time pitch extraction.

  This version:
  - Tries Essentia PitchYinProbabilistic when available
  - Falls back to JavaScript McLeod-style pitch detection
  - Keeps the graph working even if Essentia CDN fails
*/

let essentia = null;

try {
    if (typeof Essentia !== 'undefined' && typeof Module !== 'undefined') {
        essentia = new Essentia(Module);
    }
} catch (error) {
    console.warn('Essentia initialization failed. Falling back to JS pitch detection.', error);
    essentia = null;
}

class SimpleRingBuffer {
    constructor(length, channelCount) {
        this._readIndex = 0;
        this._writeIndex = 0;
        this._framesAvailable = 0;
        this._length = length;
        this._channelCount = channelCount;
        this._channelData = [];

        for (let i = 0; i < channelCount; i++) {
            this._channelData[i] = new Float32Array(length);
        }
    }

    get framesAvailable() {
        return this._framesAvailable;
    }

    push(arraySequence) {
        if (!arraySequence || !arraySequence.length || !arraySequence[0]) {
            return;
        }

        const sourceLength = arraySequence[0].length;

        for (let i = 0; i < sourceLength; i++) {
            const writeIndex = (this._writeIndex + i) % this._length;

            for (let channel = 0; channel < this._channelCount; channel++) {
                const sourceChannel = arraySequence[channel] || arraySequence[0];
                this._channelData[channel][writeIndex] = sourceChannel[i] || 0;
            }
        }

        this._writeIndex = (this._writeIndex + sourceLength) % this._length;
        this._framesAvailable = Math.min(this._framesAvailable + sourceLength, this._length);
    }

    pull(arraySequence) {
        if (this._framesAvailable === 0) {
            return;
        }

        const destinationLength = arraySequence[0].length;

        for (let i = 0; i < destinationLength; i++) {
            const readIndex = (this._readIndex + i) % this._length;

            for (let channel = 0; channel < this._channelCount; channel++) {
                arraySequence[channel][i] = this._channelData[channel][readIndex];
            }
        }

        this._readIndex = (this._readIndex + destinationLength) % this._length;
        this._framesAvailable = Math.max(this._framesAvailable - destinationLength, 0);
    }
}

class PitchMonitorProcessor extends AudioWorkletProcessor {
    constructor(options) {
        super();

        const processorOptions = options.processorOptions || {};

        this._bufferSize = processorOptions.bufferSize || 4096;
        this._sampleRate = processorOptions.sampleRate || sampleRate;
        this._channelCount = 1;

        this._inputRingBuffer = new SimpleRingBuffer(this._bufferSize, this._channelCount);
        this._accumulatedData = [new Float32Array(this._bufferSize)];

        this._frameSize = processorOptions.frameSize || 2048;
        this._hopSize = processorOptions.hopSize || 256;

        this._lowestFreq = processorOptions.minFrequency || 50;
        this._highestFreq = processorOptions.maxFrequency || 3951.066;

        this._rmsThreshold = processorOptions.rmsThreshold || 0.003;

        this._lastGoodPitch = 0;
        this._recentPitches = [];
        this._maxRecentPitches = 5;
    }

    process(inputList) {
        const input = inputList[0];

        if (!input || !input.length || !input[0]) {
            return true;
        }

        this._inputRingBuffer.push(input);

        if (this._inputRingBuffer.framesAvailable >= this._bufferSize) {
            this._inputRingBuffer.pull(this._accumulatedData);
            this._processPitchDetection(this._accumulatedData[0]);

            this._accumulatedData = [new Float32Array(this._bufferSize)];
        }

        return true;
    }

    _processPitchDetection(monoBuffer) {
        try {
            const rms = this._calculateRms(monoBuffer);
            const preparedBuffer = this._prepareBuffer(monoBuffer);

            if (rms < this._rmsThreshold) {
                this.port.postMessage({
                    pitch: 0,
                    confidence: 0,
                    rms: rms,
                    algorithm: 'silence',
                });
                return;
            }

            let best = {
                pitch: 0,
                confidence: 0,
                algorithm: 'none',
            };

            const yinResult = this._runEssentiaPitchYinProbabilistic(preparedBuffer);

            if (yinResult.confidence > best.confidence) {
                best = yinResult;
            }

            const mpmResult = this._runMcLeodPitchMethod(preparedBuffer);

            if (mpmResult.confidence > best.confidence + 0.08 || !best.pitch) {
                best = mpmResult;
            }

            if (!this._isUsablePitch(best.pitch, best.confidence)) {
                this.port.postMessage({
                    pitch: 0,
                    confidence: best.confidence || 0,
                    rms: rms || 0,
                    algorithm: best.algorithm || 'unstable',
                });
                return;
            }

            const correctedPitch = this._correctOctaveJump(best.pitch);
            const smoothedPitch = this._medianSmooth(correctedPitch);

            this._lastGoodPitch = smoothedPitch;

            this.port.postMessage({
                pitch: smoothedPitch || 0,
                confidence: best.confidence || 0,
                rms: rms || 0,
                algorithm: best.algorithm || 'hybrid',
            });
        } catch (error) {
            console.error('Pitch detection error:', error);

            this.port.postMessage({
                pitch: 0,
                confidence: 0,
                rms: 0,
                algorithm: 'error',
            });
        }
    }

    _calculateRms(buffer) {
        let sum = 0;

        for (let i = 0; i < buffer.length; i++) {
            const value = buffer[i] || 0;
            sum += value * value;
        }

        return Math.sqrt(sum / buffer.length);
    }

    _prepareBuffer(buffer) {
        const prepared = new Float32Array(buffer.length);
        let mean = 0;
        let peak = 0;

        for (let i = 0; i < buffer.length; i++) {
            mean += buffer[i] || 0;
        }

        mean = mean / buffer.length;

        for (let i = 0; i < buffer.length; i++) {
            const value = (buffer[i] || 0) - mean;
            prepared[i] = value;
            peak = Math.max(peak, Math.abs(value));
        }

        if (peak > 0) {
            for (let i = 0; i < prepared.length; i++) {
                prepared[i] = prepared[i] / peak;
            }
        }

        return prepared;
    }

    _runEssentiaPitchYinProbabilistic(buffer) {
        if (!essentia || typeof essentia.PitchYinProbabilistic !== 'function') {
            return {
                pitch: 0,
                confidence: 0,
                algorithm: 'essentia_unavailable',
            };
        }

        try {
            const vector = essentia.arrayToVector(buffer);

            const output = essentia.PitchYinProbabilistic(
                vector,
                this._frameSize,
                this._hopSize,
                0.005,
                'zero',
                false,
                this._sampleRate
            );

            const pitchFrames = output.pitch ? essentia.vectorToArray(output.pitch) : [];
            const confidenceFrames = output.voicedProbabilities
                ? essentia.vectorToArray(output.voicedProbabilities)
                : [];

            return this._summarizePitchTrack(
                pitchFrames,
                confidenceFrames,
                'essentia-pyin'
            );
        } catch (error) {
            console.warn('Essentia PitchYinProbabilistic failed.', error);

            return {
                pitch: 0,
                confidence: 0,
                algorithm: 'essentia_failed',
            };
        }
    }

    _summarizePitchTrack(pitchFrames, confidenceFrames, algorithm) {
        const candidates = [];

        for (let i = 0; i < pitchFrames.length; i++) {
            const pitch = Number(pitchFrames[i] || 0);
            const confidence = Number(confidenceFrames[i] || 0);

            if (
                pitch >= this._lowestFreq &&
                pitch <= this._highestFreq &&
                confidence >= 0.08 &&
                Number.isFinite(pitch)
            ) {
                candidates.push({
                    pitch,
                    confidence,
                });
            }
        }

        if (!candidates.length) {
            return {
                pitch: 0,
                confidence: 0,
                algorithm,
            };
        }

        candidates.sort((a, b) => a.pitch - b.pitch);

        const medianPitch = candidates[Math.floor(candidates.length / 2)].pitch;

        const cleanCandidates = candidates.filter((candidate) => {
            const centsAway = Math.abs(1200 * Math.log2(candidate.pitch / medianPitch));
            return centsAway <= 80;
        });

        const usable = cleanCandidates.length ? cleanCandidates : candidates;

        let weightedPitch = 0;
        let confidenceSum = 0;

        usable.forEach((candidate) => {
            weightedPitch += candidate.pitch * candidate.confidence;
            confidenceSum += candidate.confidence;
        });

        const pitch = confidenceSum > 0
            ? weightedPitch / confidenceSum
            : medianPitch;

        const confidence = confidenceSum / usable.length;

        return {
            pitch,
            confidence: Math.max(0, Math.min(1, confidence)),
            algorithm,
        };
    }

    _runMcLeodPitchMethod(buffer) {
        const sampleRateValue = this._sampleRate;
        const minLag = Math.max(2, Math.floor(sampleRateValue / this._highestFreq));
        const maxLag = Math.min(buffer.length - 2, Math.ceil(sampleRateValue / this._lowestFreq));
        const nsdf = new Float32Array(maxLag + 1);

        let highestPeak = 0;
        let bestLag = 0;

        for (let tau = minLag; tau <= maxLag; tau++) {
            let acf = 0;
            let divisor = 0;
            const limit = buffer.length - tau;

            for (let i = 0; i < limit; i++) {
                const x = buffer[i] || 0;
                const y = buffer[i + tau] || 0;

                acf += x * y;
                divisor += (x * x) + (y * y);
            }

            nsdf[tau] = divisor > 0 ? (2 * acf) / divisor : 0;

            if (nsdf[tau] > highestPeak) {
                highestPeak = nsdf[tau];
            }
        }

        if (highestPeak < 0.35) {
            return {
                pitch: 0,
                confidence: Math.max(0, highestPeak),
                algorithm: 'mpm',
            };
        }

        const cutoff = highestPeak * 0.93;

        for (let tau = minLag + 1; tau < maxLag - 1; tau++) {
            const isLocalMaximum = nsdf[tau] > nsdf[tau - 1] && nsdf[tau] >= nsdf[tau + 1];

            if (isLocalMaximum && nsdf[tau] >= cutoff) {
                bestLag = tau;
                break;
            }
        }

        if (!bestLag) {
            for (let tau = minLag; tau <= maxLag; tau++) {
                if (nsdf[tau] === highestPeak) {
                    bestLag = tau;
                    break;
                }
            }
        }

        if (!bestLag) {
            return {
                pitch: 0,
                confidence: 0,
                algorithm: 'mpm',
            };
        }

        const betterLag = this._parabolicInterpolation(nsdf, bestLag);
        const pitch = sampleRateValue / betterLag;

        return {
            pitch,
            confidence: Math.max(0, Math.min(1, nsdf[bestLag] || highestPeak)),
            algorithm: 'mpm',
        };
    }

    _parabolicInterpolation(values, index) {
        const previous = values[index - 1] || 0;
        const current = values[index] || 0;
        const next = values[index + 1] || 0;
        const denominator = previous - (2 * current) + next;

        if (Math.abs(denominator) < 1e-12) {
            return index;
        }

        const shift = 0.5 * (previous - next) / denominator;
        return index + Math.max(-1, Math.min(1, shift));
    }

    _isUsablePitch(pitch, confidence) {
        return Number.isFinite(pitch) &&
            pitch >= this._lowestFreq &&
            pitch <= this._highestFreq &&
            confidence >= 0.12;
    }

    _correctOctaveJump(pitch) {
        if (!this._lastGoodPitch || !pitch) {
            return pitch;
        }

        let corrected = pitch;
        const lastPitch = this._lastGoodPitch;
        const centsAway = Math.abs(1200 * Math.log2(corrected / lastPitch));

        if (centsAway > 650 && centsAway < 1350) {
            const half = corrected / 2;
            const double = corrected * 2;
            const halfDistance = Math.abs(1200 * Math.log2(half / lastPitch));
            const doubleDistance = Math.abs(1200 * Math.log2(double / lastPitch));
            const originalDistance = Math.abs(1200 * Math.log2(corrected / lastPitch));

            if (half >= this._lowestFreq && halfDistance < originalDistance) {
                corrected = half;
            } else if (double <= this._highestFreq && doubleDistance < originalDistance) {
                corrected = double;
            }
        }

        return corrected;
    }

    _medianSmooth(pitch) {
        if (!pitch || !Number.isFinite(pitch)) {
            return pitch;
        }

        this._recentPitches.push(pitch);

        if (this._recentPitches.length > this._maxRecentPitches) {
            this._recentPitches.shift();
        }

        const sorted = [...this._recentPitches].sort((a, b) => a - b);

        return sorted[Math.floor(sorted.length / 2)] || pitch;
    }
}

registerProcessor('pitch-monitor-processor', PitchMonitorProcessor);