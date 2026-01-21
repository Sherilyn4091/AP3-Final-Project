<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use App\Models\Progress;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    private function instructorIdOrAbort(): int
    {
        $userId = Auth::user()->user_id;

        $instructor = Instructor::where('user_id', $userId)->first();
        if (!$instructor) abort(403, 'Instructor profile not found.');

        return (int) $instructor->instructor_id;
    }

    public function index(Request $request)
    {
        $instructorId = $this->instructorIdOrAbort();
        $q = trim((string) $request->query('q', ''));

        $progress = Progress::query()
            ->where('instructor_id', $instructorId)
            ->with(['student:student_id,first_name,last_name'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('lesson_topic', 'ilike', "%{$q}%")
                       ->orWhereHas('student', function ($s) use ($q) {
                           $s->where('first_name', 'ilike', "%{$q}%")
                             ->orWhere('last_name', 'ilike', "%{$q}%");
                       });
                });
            })
            ->orderByDesc('progress_date')
            ->orderByDesc('progress_id')
            ->paginate(12)
            ->withQueryString();

        return view('instructor.progress.index', compact('progress', 'q'));
    }

    public function show($progressId)
    {
        $instructorId = $this->instructorIdOrAbort();

        $progress = Progress::where('progress_id', $progressId)
            ->where('instructor_id', $instructorId)
            ->with(['student:student_id,first_name,last_name'])
            ->firstOrFail();

        return view('instructor.progress.show', compact('progress'));
    }

    public function create()
    {
        $instructorId = $this->instructorIdOrAbort();

        // Only show students assigned to this instructor (same idea as your students page)
        $students = Student::whereHas('enrollments', fn($q) => $q->where('instructor_id', $instructorId))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['student_id','first_name','last_name']);

        return view('instructor.progress.create', compact('students'));
    }

    public function store(Request $request)
    {
        $instructorId = $this->instructorIdOrAbort();

        $data = $request->validate([
            'student_id' => ['required','integer'],
            'progress_date' => ['required','date'],
            'lesson_topic' => ['nullable','string','max:255'],

            'skills_covered' => ['nullable','string'],
            'techniques_learned' => ['nullable','string'],
            'songs_practiced' => ['nullable','string'],

            'performance_rating' => ['nullable','integer','between:1,10'],
            'technical_skills_rating' => ['nullable','integer','between:1,10'],
            'musicality_rating' => ['nullable','integer','between:1,10'],
            'effort_rating' => ['nullable','integer','between:1,10'],
            'student_satisfaction' => ['nullable','integer','between:1,5'],

            'strengths' => ['nullable','string'],
            'areas_for_improvement' => ['nullable','string'],
            'instructor_notes' => ['nullable','string'],
            'homework' => ['nullable','string'],
            'practice_recommendations' => ['nullable','string'],
            'next_lesson_focus' => ['nullable','string'],
            'student_comments' => ['nullable','string'],
        ]);

        // Ensure student belongs to this instructor
        $assigned = Student::where('student_id', $data['student_id'])
            ->whereHas('enrollments', fn($q) => $q->where('instructor_id', $instructorId))
            ->exists();

        if (!$assigned) abort(403, 'You are not assigned to this student.');

        $data['instructor_id'] = $instructorId;
        // Find the active/latest enrollment for this student under this instructor
        $enrollmentId = DB::table('enrollment')
            ->where('student_id', (int) $data['student_id'])
            ->where('instructor_id', $instructorId)
            ->orderByDesc('enrollment_date')
            ->orderByDesc('enrollment_id')
            ->value('enrollment_id');

        if (!$enrollmentId) {
            return back()
                ->withInput()
                ->with('error', 'No enrollment found for this student under your account.');
        }

        $data['enrollment_id'] = $enrollmentId;


        Progress::create($data);

        return redirect()->route('instructor.progress.index')->with('success', 'Progress record added.');
    }

    public function edit($progressId)
    {
        $instructorId = $this->instructorIdOrAbort();

        $progress = Progress::where('progress_id', $progressId)
            ->where('instructor_id', $instructorId)
            ->with(['student:student_id,first_name,last_name'])
            ->firstOrFail();

        return view('instructor.progress.edit', compact('progress'));
    }

    public function update(Request $request, $progressId)
    {
        $instructorId = $this->instructorIdOrAbort();

        $progress = Progress::where('progress_id', $progressId)
            ->where('instructor_id', $instructorId)
            ->firstOrFail();

        $data = $request->validate([
            'progress_date' => ['required','date'],
            'lesson_topic' => ['nullable','string','max:255'],

            'skills_covered' => ['nullable','string'],
            'techniques_learned' => ['nullable','string'],
            'songs_practiced' => ['nullable','string'],

            'performance_rating' => ['nullable','integer','between:1,10'],
            'technical_skills_rating' => ['nullable','integer','between:1,10'],
            'musicality_rating' => ['nullable','integer','between:1,10'],
            'effort_rating' => ['nullable','integer','between:1,10'],
            'student_satisfaction' => ['nullable','integer','between:1,5'],

            'strengths' => ['nullable','string'],
            'areas_for_improvement' => ['nullable','string'],   
            'instructor_notes' => ['nullable','string'],
            'homework' => ['nullable','string'],
            'practice_recommendations' => ['nullable','string'],
            'next_lesson_focus' => ['nullable','string'],
            'student_comments' => ['nullable','string'],
        ]);

        $progress->update($data);

        return redirect()->route('instructor.progress.show', $progress->progress_id)
            ->with('success', 'Progress record updated.');
    }

    // No destroy() method needed for instructor
}
