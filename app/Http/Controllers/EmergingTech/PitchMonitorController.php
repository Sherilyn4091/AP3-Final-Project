<?php

namespace App\Http\Controllers\EmergingTech;

use App\Http\Controllers\Controller;
use App\Models\PitchMonitorSession;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/*
|--------------------------------------------------------------------------
| PitchMonitorController
|--------------------------------------------------------------------------
|
| Handles the student-facing Pitch Monitor module:
| - main page
| - session start / end
| - saving detected pitch events
| - history page
| - deleting saved sessions
|
*/

class PitchMonitorController extends Controller
{
    /**
     * Show the main Pitch Monitor page.
     */
    public function index()
    {
        return view('emerging-tech.pitch-monitor.index');
    }

    /**
     * Start a new session.
     * If the user already has an open session, reuse it.
     *
     * Debug note:
     * - This keeps the original session logic.
     * - The try-catch is added only to reveal the real Laravel/database error
     *   when the frontend receives a 500 Internal Server Error.
     */
    public function startSession(Request $request)
    {
        try {
            $request->validate([
                'source_type' => 'nullable|string|max:100',
            ]);

            $existing = PitchMonitorSession::where('user_id', Auth::id())
                ->whereNull('ended_at')
                ->latest('started_at')
                ->first();

            if ($existing) {
                return response()->json([
                    'ok' => true,
                    'session_id' => $existing->session_id,
                ]);
            }

            $session = PitchMonitorSession::create([
                'user_id' => Auth::id(),
                'source_type' => $request->source_type ?? 'microphone',
                'started_at' => now(),
            ]);

            return response()->json([
                'ok' => true,
                'session_id' => $session->session_id,
            ]);
        } catch (Throwable $error) {
            Log::error('Pitch Monitor session start failed.', [
                'user_id' => Auth::id(),
                'message' => $error->getMessage(),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => config('app.debug')
                    ? $error->getMessage()
                    : 'Unable to start pitch monitor session.',
            ], 500);
        }
    }

    /**
     * End an active session.
     */
    public function endSession(PitchMonitorSession $session)
    {
        abort_unless($session->user_id === Auth::id(), 403);

        if ($session->ended_at === null) {
            $session->update([
                'ended_at' => now(),
            ]);
        }

        return response()->json([
            'ok' => true,
        ]);
    }

    /**
     * Store one detected pitch event inside the current session.
     */
    public function storeEvent(Request $request, PitchMonitorSession $session)
    {
        abort_unless($session->user_id === Auth::id(), 403);
        abort_if($session->ended_at !== null, 422, 'Session already ended.');

        $data = $request->validate([
            'note_name' => ['required', 'string', 'max:8', 'regex:/^[A-G](#|b)?-?\d$/'],
            'frequency' => 'required|numeric|min:20|max:5000',
            'cents_deviation' => 'nullable|numeric|min:-100|max:100',
            'tuning_status' => 'nullable|in:flat,in_tune,sharp,detected',
            'confidence' => 'nullable|numeric|min:0|max:1',
            'rms' => 'nullable|numeric|min:0|max:10',
            'detected_at' => 'required|date',
        ]);

        $session->events()->create($data);

        return response()->json([
            'ok' => true,
        ]);
    }

    /**
     * Show the Pitch Monitor history page.
     *
     * Enhancements:
     * - paginated to 5 sessions per page
     * - optional server-side date filtering
     * - summary statistics for the current filtered history
     */
    public function history(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
        ]);

        $selectedDate = $validated['date'] ?? null;

        $baseQuery = PitchMonitorSession::with(['events' => function ($query) {
                $query->orderBy('detected_at', 'asc');
            }])
            ->where('user_id', Auth::id());

        if ($selectedDate) {
            $baseQuery->whereDate('started_at', $selectedDate);
        }

        /*
        |--------------------------------------------------------------------------
        | History Statistics
        |--------------------------------------------------------------------------
        |
        | The statistics use the same filtered result set as the session list.
        | This keeps the stat cards accurate when the user filters by date.
        |
        */
        $stats = $this->buildHistoryStats((clone $baseQuery)->get());

        /*
        |--------------------------------------------------------------------------
        | Paginated Sessions
        |--------------------------------------------------------------------------
        |
        | Required feature:
        | - maximum of 5 history cards per page
        |
        | withQueryString() keeps the selected date filter when changing pages.
        |
        */
        $sessions = $baseQuery
            ->orderByDesc('started_at')
            ->paginate(5)
            ->withQueryString();

        return view('emerging-tech.pitch-monitor.history', compact(
            'sessions',
            'stats',
            'selectedDate'
        ));
    }

    /**
     * Delete a session and its related pitch events.
     *
     * Safety note:
     * - The schema does not clearly show ON DELETE CASCADE.
     * - Deleting events first avoids foreign key errors.
     */
    public function deleteSession(PitchMonitorSession $session)
    {
        abort_unless($session->user_id === Auth::id(), 403);

        DB::transaction(function () use ($session) {
            $session->events()->delete();
            $session->delete();
        });

        return response()->json([
            'ok' => true,
            'message' => 'Pitch monitor session deleted successfully.',
        ]);
    }

    /**
     * Build clean summary statistics for the history page.
     */
    private function buildHistoryStats(Collection $sessions): array
    {
        $totalSeconds = 0;
        $accuracyTotal = 0;
        $accuracySessions = 0;
        $bestAccuracy = 0;
        $totalEvents = 0;
        $confidenceTotal = 0;
        $confidenceEvents = 0;

        foreach ($sessions as $session) {
            $totalSeconds += (int) ($session->duration_seconds ?? 0);

            $events = $session->events;
            $eventCount = $events->count();
            $totalEvents += $eventCount;

            if ($eventCount > 0) {
                $inTuneCount = $events->where('tuning_status', 'in_tune')->count();
                $accuracy = (int) round(($inTuneCount / $eventCount) * 100);

                $accuracyTotal += $accuracy;
                $accuracySessions++;
                $bestAccuracy = max($bestAccuracy, $accuracy);

                foreach ($events as $event) {
                    if ($event->confidence !== null) {
                        $confidenceTotal += (float) $event->confidence;
                        $confidenceEvents++;
                    }
                }
            }
        }

        return [
            'total_sessions' => $sessions->count(),
            'total_events' => $totalEvents,
            'total_seconds' => $totalSeconds,
            'total_duration_label' => $this->formatDurationForDisplay($totalSeconds),
            'avg_accuracy' => $accuracySessions > 0
                ? (int) round($accuracyTotal / $accuracySessions)
                : 0,
            'best_accuracy' => $bestAccuracy,
            'avg_confidence' => $confidenceEvents > 0
                ? round(($confidenceTotal / $confidenceEvents) * 100, 1)
                : 0,
        ];
    }

    /**
     * Format seconds into a short readable duration label.
     */
    private function formatDurationForDisplay(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0m';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }

        if ($minutes > 0) {
            return $minutes . 'm';
        }

        return $seconds . 's';
    }
}