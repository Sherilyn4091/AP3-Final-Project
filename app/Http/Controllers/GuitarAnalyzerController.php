<?php
# app/Http/Controllers/GuitarAnalyzerController.php

namespace App\Http\Controllers;

use App\Models\GuitarSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuitarAnalyzerController extends Controller
{
    /**
     * Show the main Sound Check / Guitar Analyzer page.
     * GET /student/sound-check
     */
    public function index()
    {
        return view('guitar-analyzer.index');
    }

    /**
     * Create a new session row when the user clicks "Start Listening".
     * Called by JavaScript via POST /student/sound-check/session/start
     * Returns the new session ID so JS can reference it for event saves.
     */
    public function startSession(Request $request)
    {
        $request->validate([
            'target_string' => 'nullable|in:E2,A2,D3,G3,B3,E4',
        ]);

        $existing = GuitarSession::where('user_id', Auth::id())
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if ($existing) {
            return response()->json(['session_id' => $existing->session_id]);
        }

        $session = GuitarSession::create([
            'user_id'       => Auth::id(),
            'target_string' => $request->target_string,
            'started_at'    => now(),
        ]);

        return response()->json(['session_id' => $session->session_id]);
    }

    /**
     * Stamp the session end time when the user clicks "Stop".
     * Called by JavaScript via POST /student/sound-check/session/{session}/end
     */
    public function endSession(GuitarSession $session)
    {
        abort_unless($session->user_id === Auth::id(), 403);

        if ($session->ended_at === null) {
            $session->update(['ended_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Save a single detected-note event to the database.
     * Called by JavaScript via POST /student/sound-check/session/{session}/event
     */
    public function storeEvent(Request $request, GuitarSession $session)
    {
        abort_unless($session->user_id === Auth::id(), 403);
        abort_if($session->ended_at !== null, 422, 'Session already ended.');

        $data = $request->validate([
            'note_name'       => ['required', 'string', 'max:4', 'regex:/^[A-G]#?\d$/'],
            'frequency'       => 'required|numeric|min:20|max:2000',
            'cents_deviation' => 'required|numeric|min:-100|max:100',
            'tuning_status'   => 'required|in:flat,in_tune,sharp',
            'detected_at'     => 'required|date',
        ]);

        $session->noteEvents()->create($data);

        return response()->json(['ok' => true]);
    }

    /**
     * Show the session history page.
     * GET /student/sound-check/history
     */
    public function history()
    {
        $sessions = GuitarSession::with(['noteEvents' => function ($q) {
            $q->orderBy('detected_at', 'asc');
        }])
            ->where('user_id', Auth::id())
            ->orderByDesc('started_at')
            ->paginate(10);

        return view('guitar-analyzer.history', compact('sessions'));
    }

    /**
     * Delete a session and all its associated note events.
     * DELETE /student/sound-check/session/{session}/delete
     */
    public function deleteSession(GuitarSession $session)
    {
        abort_unless($session->user_id === Auth::id(), 403);

        $session->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Session deleted successfully',
        ]);
    }
}