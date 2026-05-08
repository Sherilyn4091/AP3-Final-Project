{{-- resources/views/instructor/progress/show.blade.php --}}
@extends('layouts.instructor')

@section('content')
<div class="space-y-6">
    <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#B4833D]">Progress Details</p>
            <h1 class="mt-2 text-3xl font-extrabold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">{{ $progress->student_name }}</h1>
            <p class="mt-2 text-sm text-[#61677A]">{{ $progress->instrument_name ?? 'No instrument' }} • {{ \Carbon\Carbon::parse($progress->progress_date)->format('F d, Y') }}</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('instructor.progress.index') }}" class="rounded-2xl border border-[#959D90] bg-white px-4 py-2 text-center text-sm font-bold text-[#2F4F4F] hover:bg-[#FFF6E0]">Back</a>
            <a href="{{ route('instructor.progress.edit', $progress->progress_id) }}" class="rounded-2xl bg-[#2F4F4F] px-4 py-2 text-center text-sm font-bold text-white hover:bg-[#B4833D]">Edit</a>
        </div>
    </header>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach([
            'Performance' => $progress->performance_rating,
            'Technical' => $progress->technical_skills_rating,
            'Musicality' => $progress->musicality_rating,
            'Effort' => $progress->effort_rating,
            'Satisfaction' => $progress->student_satisfaction,
        ] as $label => $value)
            <div class="rounded-[24px] border border-[#D8D9DA] bg-white p-5">
                <p class="text-xs font-bold uppercase text-[#61677A]">{{ $label }}</p>
                <p class="mt-2 text-3xl font-black text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $value ?? '—' }}{{ $label === 'Satisfaction' ? '/5' : '/10' }}</p>
            </div>
        @endforeach
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        <div class="rounded-[28px] border border-[#D8D9DA] bg-white p-5 shadow-sm">
            <h2 class="text-xl font-bold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">Lesson Summary</h2>
            <div class="mt-5 space-y-4 text-sm leading-6 text-[#523D35]">
                <div><p class="font-bold text-[#2F4F4F]">Lesson Topic</p><p>{{ $progress->lesson_topic ?? 'Not provided' }}</p></div>
                <div><p class="font-bold text-[#2F4F4F]">Skills Covered</p><p>{{ $progress->skills_covered ?? 'Not provided' }}</p></div>
                <div><p class="font-bold text-[#2F4F4F]">Techniques Learned</p><p>{{ $progress->techniques_learned ?? 'Not provided' }}</p></div>
                <div><p class="font-bold text-[#2F4F4F]">Songs Practiced</p><p>{{ $progress->songs_practiced ?? 'Not provided' }}</p></div>
            </div>
        </div>

        <div class="rounded-[28px] border border-[#D8D9DA] bg-white p-5 shadow-sm">
            <h2 class="text-xl font-bold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">Feedback</h2>
            <div class="mt-5 space-y-4 text-sm leading-6 text-[#523D35]">
                <div><p class="font-bold text-[#2F4F4F]">Strengths</p><p>{{ $progress->strengths ?? 'Not provided' }}</p></div>
                <div><p class="font-bold text-[#2F4F4F]">Areas for Improvement</p><p>{{ $progress->areas_for_improvement ?? 'Not provided' }}</p></div>
                <div><p class="font-bold text-[#2F4F4F]">Instructor Notes</p><p>{{ $progress->instructor_notes ?? 'Not provided' }}</p></div>
                <div><p class="font-bold text-[#2F4F4F]">Student Comments</p><p>{{ $progress->student_comments ?? 'Not provided' }}</p></div>
            </div>
        </div>
    </section>

    <section class="rounded-[28px] border border-[#D8D9DA] bg-white p-5 shadow-sm">
        <h2 class="text-xl font-bold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">Homework and Next Steps</h2>
        <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-[#D8D9DA] bg-[#FFF6E0] p-4"><p class="text-xs font-bold uppercase text-[#61677A]">Homework</p><p class="mt-2 text-sm leading-6 text-[#523D35]">{{ $progress->homework ?? 'No homework recorded.' }}</p></div>
            <div class="rounded-2xl border border-[#D8D9DA] bg-[#fcf3e3] p-4"><p class="text-xs font-bold uppercase text-[#61677A]">Practice Recommendations</p><p class="mt-2 text-sm leading-6 text-[#523D35]">{{ $progress->practice_recommendations ?? 'No recommendation recorded.' }}</p></div>
            <div class="rounded-2xl border border-[#D8D9DA] bg-[#fcf3e3] p-4"><p class="text-xs font-bold uppercase text-[#61677A]">Next Lesson Focus</p><p class="mt-2 text-sm leading-6 text-[#523D35]">{{ $progress->next_lesson_focus ?? 'No next focus recorded.' }}</p></div>
        </div>
    </section>
</div>
@endsection