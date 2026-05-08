<?php
// app/Http/Controllers/Instructor/ProgressController.php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    private function instructorIdOrAbort(): int
    {
        $instructorId = DB::table('instructor')
            ->where('user_id', Auth::user()->user_id)
            ->value('instructor_id');

        if (!$instructorId) {
            abort(403, 'Instructor profile not found.');
        }

        return (int) $instructorId;
    }

    /**
     * List progress records owned by this instructor.
     */
    public function index(Request $request)
    {
        $instructorId = $this->instructorIdOrAbort();
        $q = trim((string) $request->query('q', ''));

        $progress = DB::table('progress as p')
            ->join('student as st', 'st.student_id', '=', 'p.student_id')
            ->leftJoin('enrollment as e', 'e.enrollment_id', '=', 'p.enrollment_id')
            ->leftJoin('instrument as ins', 'ins.instrument_id', '=', 'e.instrument_id')
            ->where('p.instructor_id', $instructorId)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('st.first_name', 'ilike', "%{$q}%")
                        ->orWhere('st.last_name', 'ilike', "%{$q}%")
                        ->orWhere('p.lesson_topic', 'ilike', "%{$q}%")
                        ->orWhere('p.homework', 'ilike', "%{$q}%")
                        ->orWhere('p.next_lesson_focus', 'ilike', "%{$q}%");
                });
            })
            ->select([
                'p.*',
                'ins.instrument_name',
                DB::raw("TRIM(st.first_name || ' ' || st.last_name) as student_name"),
            ])
            ->orderByDesc('p.progress_date')
            ->orderByDesc('p.progress_id')
            ->paginate(12)
            ->withQueryString();

        return view('instructor.progress.index', compact('progress', 'q'));
    }

    /**
     * Show one progress record.
     */
    public function show($progressId)
    {
        $instructorId = $this->instructorIdOrAbort();

        $progress = DB::table('progress as p')
            ->join('student as st', 'st.student_id', '=', 'p.student_id')
            ->leftJoin('enrollment as e', 'e.enrollment_id', '=', 'p.enrollment_id')
            ->leftJoin('instrument as ins', 'ins.instrument_id', '=', 'e.instrument_id')
            ->leftJoin('schedule as s', 's.schedule_id', '=', 'p.schedule_id')
            ->where('p.progress_id', $progressId)
            ->where('p.instructor_id', $instructorId)
            ->select([
                'p.*',
                'e.total_sessions',
                'e.completed_sessions',
                'e.remaining_sessions',
                'ins.instrument_name',
                's.schedule_date',
                's.start_time',
                's.end_time',
                DB::raw("TRIM(st.first_name || ' ' || st.last_name) as student_name"),
            ])
            ->first();

        if (!$progress) {
            abort(404, 'Progress record not found.');
        }

        return view('instructor.progress.show', compact('progress'));
    }

    /**
     * Show add progress form.
     */
    public function create()
    {
        $instructorId = $this->instructorIdOrAbort();

        $students = DB::table('enrollment as e')
            ->join('student as st', 'st.student_id', '=', 'e.student_id')
            ->join('instrument as ins', 'ins.instrument_id', '=', 'e.instrument_id')
            ->where('e.instructor_id', $instructorId)
            ->where('e.status', 'active')
            ->select([
                'st.student_id',
                DB::raw("TRIM(st.first_name || ' ' || st.last_name) as student_name"),
                'ins.instrument_name',
                'e.enrollment_id',
            ])
            ->orderBy('st.last_name')
            ->orderBy('st.first_name')
            ->get();

        return view('instructor.progress.create', compact('students'));
    }

    /**
     * Store progress, homework, and practice recommendations.
     */
    public function store(Request $request)
    {
        $instructorId = $this->instructorIdOrAbort();
        $data = $this->validatedProgressData($request, true);

        $enrollment = DB::table('enrollment')
            ->where('student_id', (int) $data['student_id'])
            ->where('instructor_id', $instructorId)
            ->orderByDesc('enrollment_date')
            ->orderByDesc('enrollment_id')
            ->first();

        if (!$enrollment) {
            return back()->withInput()->with('error', 'No enrollment found for this student under your account.');
        }

        $data['instructor_id'] = $instructorId;
        $data['enrollment_id'] = $enrollment->enrollment_id;

        Progress::create($data);

        return redirect()->route('instructor.progress.index')->with('success', 'Progress record saved successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit($progressId)
    {
        $instructorId = $this->instructorIdOrAbort();

        $progress = Progress::where('progress_id', $progressId)
            ->where('instructor_id', $instructorId)
            ->firstOrFail();

        return view('instructor.progress.edit', compact('progress'));
    }

    /**
     * Update only instructor-owned progress record.
     */
    public function update(Request $request, $progressId)
    {
        $instructorId = $this->instructorIdOrAbort();

        $progress = Progress::where('progress_id', $progressId)
            ->where('instructor_id', $instructorId)
            ->firstOrFail();

        $progress->update($this->validatedProgressData($request, false));

        return redirect()->route('instructor.progress.show', $progress->progress_id)
            ->with('success', 'Progress record updated successfully.');
    }

    /**
     * Shared progress validation. Keeps controller short and avoids duplicated rules.
     */
    private function validatedProgressData(Request $request, bool $requiresStudent): array
    {
        return $request->validate([
            'student_id' => [$requiresStudent ? 'required' : 'sometimes', 'integer', 'exists:student,student_id'],
            'progress_date' => ['required', 'date'],
            'lesson_topic' => ['nullable', 'string', 'max:255'],
            'skills_covered' => ['nullable', 'string'],
            'techniques_learned' => ['nullable', 'string'],
            'songs_practiced' => ['nullable', 'string'],
            'performance_rating' => ['nullable', 'integer', 'between:1,10'],
            'technical_skills_rating' => ['nullable', 'integer', 'between:1,10'],
            'musicality_rating' => ['nullable', 'integer', 'between:1,10'],
            'effort_rating' => ['nullable', 'integer', 'between:1,10'],
            'student_satisfaction' => ['nullable', 'integer', 'between:1,5'],
            'strengths' => ['nullable', 'string'],
            'areas_for_improvement' => ['nullable', 'string'],
            'instructor_notes' => ['nullable', 'string'],
            'homework' => ['nullable', 'string'],
            'practice_recommendations' => ['nullable', 'string'],
            'next_lesson_focus' => ['nullable', 'string'],
            'student_comments' => ['nullable', 'string'],
        ]);
    }
}