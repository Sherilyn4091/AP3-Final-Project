@php
    // $mode: 'create' | 'edit'
    // $progress: Progress model (edit) or null (create)
    // $students: collection of students (create), optional for edit
    $isEdit = ($mode ?? 'create') === 'edit';

    $action = $isEdit
        ? route('instructor.progress.update', $progress->progress_id)
        : route('instructor.progress.store');

    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    {{-- Top row: Student (create only), Date --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Student
            </label>

            @if($isEdit)
                {{-- Instructor can edit only their own progress record; student can't be changed --}}
                <input type="text"
                       value="{{ optional($progress->student)->first_name }} {{ optional($progress->student)->last_name }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-800"
                       disabled>
                <p class="mt-1 text-xs text-gray-500">Student is locked for this record.</p>
            @else
                <select name="student_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400">
                    <option value="">Select a student...</option>
                    @foreach(($students ?? collect()) as $s)
                        <option value="{{ $s->student_id }}"
                            {{ (string)old('student_id') === (string)$s->student_id ? 'selected' : '' }}>
                            {{ $s->last_name }}, {{ $s->first_name }}
                        </option>
                    @endforeach
                </select>
                @error('student_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Only your assigned students appear here.</p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Progress Date
            </label>
            <input type="date"
                   name="progress_date"
                   value="{{ old('progress_date', $isEdit ? optional($progress->progress_date)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400">
            @error('progress_date')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Lesson Topic --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Lesson Topic</label>
        <input type="text"
               name="lesson_topic"
               value="{{ old('lesson_topic', $isEdit ? $progress->lesson_topic : '') }}"
               placeholder="e.g., Chords + strumming basics"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400">
        @error('lesson_topic')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Text areas: Skills/Techniques/Songs --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Skills Covered</label>
            <textarea name="skills_covered" rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400"
                      placeholder="What skills were practiced?">{{ old('skills_covered', $isEdit ? $progress->skills_covered : '') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Techniques Learned</label>
            <textarea name="techniques_learned" rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400"
                      placeholder="New techniques introduced">{{ old('techniques_learned', $isEdit ? $progress->techniques_learned : '') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Songs Practiced</label>
            <textarea name="songs_practiced" rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400"
                      placeholder="Songs / exercises practiced">{{ old('songs_practiced', $isEdit ? $progress->songs_practiced : '') }}</textarea>
        </div>
    </div>

    {{-- Ratings --}}
    <div class="bg-gray-50 border border-gray-100 rounded-xl p-5">
        <h3 class="font-semibold text-gray-900 mb-4">Ratings</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @php
                $ratingFields = [
                    ['name' => 'performance_rating', 'label' => 'Performance (1–10)'],
                    ['name' => 'technical_skills_rating', 'label' => 'Technical Skills (1–10)'],
                    ['name' => 'musicality_rating', 'label' => 'Musicality (1–10)'],
                    ['name' => 'effort_rating', 'label' => 'Effort (1–10)'],
                ];
            @endphp

            @foreach($ratingFields as $rf)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $rf['label'] }}</label>
                    <input type="number" min="1" max="10"
                           name="{{ $rf['name'] }}"
                           value="{{ old($rf['name'], $isEdit ? $progress->{$rf['name']} : '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400"
                           placeholder="1-10">
                    @error($rf['name'])
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Student Satisfaction (1–5)</label>
                <input type="number" min="1" max="5"
                       name="student_satisfaction"
                       value="{{ old('student_satisfaction', $isEdit ? $progress->student_satisfaction : '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400"
                       placeholder="1-5">
                @error('student_satisfaction')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Notes / Homework --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Strengths</label>
            <textarea name="strengths" rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400"
                      placeholder="What did the student do well?">{{ old('strengths', $isEdit ? $progress->strengths : '') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Areas for Improvement</label>
            <textarea name="areas_for_improvement" rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400"
                      placeholder="What needs work next?">{{ old('areas_for_improvement', $isEdit ? $progress->areas_for_improvement : '') }}</textarea>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Instructor Notes</label>
            <textarea name="instructor_notes" rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400"
                      placeholder="Notes about behavior, pacing, improvements...">{{ old('instructor_notes', $isEdit ? $progress->instructor_notes : '') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Homework</label>
            <textarea name="homework" rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400"
                      placeholder="What should the student practice?">{{ old('homework', $isEdit ? $progress->homework : '') }}</textarea>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Practice Recommendations</label>
            <textarea name="practice_recommendations" rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400"
                      placeholder="Suggested routine / tempo / schedule">{{ old('practice_recommendations', $isEdit ? $progress->practice_recommendations : '') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Next Lesson Focus</label>
            <textarea name="next_lesson_focus" rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400"
                      placeholder="What to focus on next lesson">{{ old('next_lesson_focus', $isEdit ? $progress->next_lesson_focus : '') }}</textarea>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Student Comments (optional)</label>
        <textarea name="student_comments" rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400"
                  placeholder="Any comments from the student">{{ old('student_comments', $isEdit ? $progress->student_comments : '') }}</textarea>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
        <a href="{{ route('instructor.progress.index') }}"
           class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition text-sm text-center">
            Cancel
        </a>

        <button type="submit"
                class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition text-sm">
            {{ $isEdit ? 'Update Progress' : 'Save Progress' }}
        </button>
    </div>
</form>
