<?php

namespace App\Http\Controllers\EmergingTech;

use App\Http\Controllers\Controller;
use App\Models\PitchMonitorSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'tuning_status' => 'nullable|in:flat,in_tune,sharp',
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
     */
    public function history()
    {
        $sessions = PitchMonitorSession::with(['events' => function ($query) {
                $query->orderBy('detected_at', 'asc');
            }])
            ->where('user_id', Auth::id())
            ->orderByDesc('started_at')
            ->paginate(10);

        return view('emerging-tech.pitch-monitor.history', compact('sessions'));
    }

    /**
     * Delete a session and its related pitch events.
     */
    public function deleteSession(PitchMonitorSession $session)
    {
        abort_unless($session->user_id === Auth::id(), 403);

        $session->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Pitch monitor session deleted successfully.',
        ]);
    }
}