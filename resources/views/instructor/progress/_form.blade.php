{{-- resources/views/instructor/progress/_form.blade.php --}}
@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $selectedStudentId = old('student_id', request('student_id'));
@endphp

<form method="POST" action="{{ $isEdit ? route('instructor.progress.update', $progress->progress_id) : route('instructor.progress.store') }}" class="space-y-5">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-[#B4833D] bg-[#FFF6E0] p-4 text-sm text-[#523D35]">
            <strong>Please check the form.</strong>
            <ul class="mt-2 list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @if(!$isEdit)
            <div>
                <label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Student / Enrollment</label>
                <select name="student_id" required class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]">
                    <option value="">Select student</option>
                    @foreach($students as $student)
                        <option value="{{ $student->student_id }}" @selected((string) $selectedStudentId === (string) $student->student_id)>
                            {{ $student->student_name }} — {{ $student->instrument_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Progress Date</label>
            <input type="date" name="progress_date" value="{{ old('progress_date', $isEdit ? optional($progress->progress_date)->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]">
        </div>

        <div class="lg:col-span-2">
            <label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Lesson Topic</label>
            <input type="text" name="lesson_topic" value="{{ old('lesson_topic', $isEdit ? $progress->lesson_topic : '') }}" class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]" placeholder="Example: Strumming pattern, scales, timing">
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div><label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Skills Covered</label><textarea name="skills_covered" rows="4" class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]">{{ old('skills_covered', $isEdit ? $progress->skills_covered : '') }}</textarea></div>
        <div><label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Techniques Learned</label><textarea name="techniques_learned" rows="4" class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]">{{ old('techniques_learned', $isEdit ? $progress->techniques_learned : '') }}</textarea></div>
        <div><label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Songs Practiced</label><textarea name="songs_practiced" rows="4" class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]">{{ old('songs_practiced', $isEdit ? $progress->songs_practiced : '') }}</textarea></div>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
        @foreach([
            'performance_rating' => 'Performance',
            'technical_skills_rating' => 'Technical',
            'musicality_rating' => 'Musicality',
            'effort_rating' => 'Effort',
            'student_satisfaction' => 'Satisfaction',
        ] as $field => $label)
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-[#61677A]">{{ $label }}</label>
                <input type="number" name="{{ $field }}" min="1" max="{{ $field === 'student_satisfaction' ? 5 : 10 }}" value="{{ old($field, $isEdit ? $progress->{$field} : '') }}" class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]" style="font-family: 'JetBrains Mono', monospace;">
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div><label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Strengths</label><textarea name="strengths" rows="4" class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]">{{ old('strengths', $isEdit ? $progress->strengths : '') }}</textarea></div>
        <div><label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Areas for Improvement</label><textarea name="areas_for_improvement" rows="4" class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]">{{ old('areas_for_improvement', $isEdit ? $progress->areas_for_improvement : '') }}</textarea></div>
    </div>

    <div><label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Instructor Notes</label><textarea name="instructor_notes" rows="4" class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]">{{ old('instructor_notes', $isEdit ? $progress->instructor_notes : '') }}</textarea></div>

    <div class="rounded-[24px] border border-[#959D90] bg-[#FFF6E0] p-4">
        <h2 class="text-lg font-bold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">Homework / Assignment Details</h2>
        <p class="mt-1 text-sm text-[#523D35]">This is saved inside the progress record. It is not a separate module.</p>
        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div><label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Homework</label><textarea name="homework" rows="5" class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]" placeholder="What should the student practice before next lesson?">{{ old('homework', $isEdit ? $progress->homework : '') }}</textarea></div>
            <div><label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Practice Recommendations</label><textarea name="practice_recommendations" rows="5" class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]" placeholder="Suggested routine, tempo, repetition, schedule">{{ old('practice_recommendations', $isEdit ? $progress->practice_recommendations : '') }}</textarea></div>
            <div><label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Next Lesson Focus</label><textarea name="next_lesson_focus" rows="5" class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]" placeholder="What should be checked next lesson?">{{ old('next_lesson_focus', $isEdit ? $progress->next_lesson_focus : '') }}</textarea></div>
        </div>
    </div>

    <div><label class="mb-1 block text-sm font-bold text-[#2F4F4F]">Student Comments</label><textarea name="student_comments" rows="3" class="w-full rounded-2xl border border-[#D8D9DA] px-4 py-3 text-sm focus:border-[#959D90] focus:ring-[#959D90]">{{ old('student_comments', $isEdit ? $progress->student_comments : '') }}</textarea></div>

    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('instructor.progress.index') }}" class="rounded-2xl border border-[#959D90] px-5 py-3 text-center text-sm font-bold text-[#2F4F4F] hover:bg-[#FFF6E0]">Cancel</a>
        <button type="submit" class="rounded-2xl bg-[#2F4F4F] px-5 py-3 text-sm font-bold text-white hover:bg-[#B4833D]">{{ $isEdit ? 'Update Progress' : 'Save Progress' }}</button>
    </div>
</form>