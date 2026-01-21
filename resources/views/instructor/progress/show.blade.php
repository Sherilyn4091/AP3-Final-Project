@extends('layouts.instructor')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Progress Details</h1>
            <p class="mt-1 text-gray-600">
                {{ optional($progress->student)->first_name }} {{ optional($progress->student)->last_name }}
                • {{ optional($progress->progress_date)->format('Y-m-d') ?? 'N/A' }}
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('instructor.progress.index') }}"
               class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition text-sm">
                Back
            </a>

            <a href="{{ route('instructor.progress.edit', $progress->progress_id) }}"
               class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition text-sm">
                Edit
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-6">

        {{-- Lesson Summary --}}
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Lesson Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Lesson Topic</p>
                    <p class="font-medium text-gray-900">{{ $progress->lesson_topic ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Progress Date</p>
                    <p class="font-medium text-gray-900">{{ optional($progress->progress_date)->format('Y-m-d') ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Ratings --}}
        <div class="bg-gray-50 border border-gray-100 rounded-xl p-5">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Ratings</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Performance</p>
                    <p class="font-medium text-gray-900">{{ $progress->performance_rating ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Technical</p>
                    <p class="font-medium text-gray-900">{{ $progress->technical_skills_rating ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Musicality</p>
                    <p class="font-medium text-gray-900">{{ $progress->musicality_rating ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Effort</p>
                    <p class="font-medium text-gray-900">{{ $progress->effort_rating ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Student Satisfaction</p>
                    <p class="font-medium text-gray-900">{{ $progress->student_satisfaction ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Skills Covered</p>
                <p class="mt-1 text-gray-900 whitespace-pre-line">{{ $progress->skills_covered ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Techniques Learned</p>
                <p class="mt-1 text-gray-900 whitespace-pre-line">{{ $progress->techniques_learned ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Songs Practiced</p>
                <p class="mt-1 text-gray-900 whitespace-pre-line">{{ $progress->songs_practiced ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Instructor Notes</p>
                <p class="mt-1 text-gray-900 whitespace-pre-line">{{ $progress->instructor_notes ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Strengths</p>
                <p class="mt-1 text-gray-900 whitespace-pre-line">{{ $progress->strengths ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Areas for Improvement</p>
                <p class="mt-1 text-gray-900 whitespace-pre-line">{{ $progress->areas_for_improvement ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Homework</p>
                <p class="mt-1 text-gray-900 whitespace-pre-line">{{ $progress->homework ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Next Lesson Focus</p>
                <p class="mt-1 text-gray-900 whitespace-pre-line">{{ $progress->next_lesson_focus ?? '—' }}</p>
            </div>
        </div>

        <div class="text-sm">
            <p class="text-gray-500">Student Comments</p>
            <p class="mt-1 text-gray-900 whitespace-pre-line">{{ $progress->student_comments ?? '—' }}</p>
        </div>

    </div>
</div>
@endsection