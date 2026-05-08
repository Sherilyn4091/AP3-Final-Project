{{-- resources/views/instructor/progress/index.blade.php --}}
@extends('layouts.instructor')

@section('content')
<div class="space-y-6">
    <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#B4833D]">Progress</p>
            <h1 class="mt-2 text-3xl font-extrabold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">Progress Records</h1>
            <p class="mt-2 max-w-2xl text-sm text-[#61677A]">Progress includes lesson notes, ratings, homework, practice recommendations, and next lesson focus.</p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <form method="GET" action="{{ route('instructor.progress.index') }}" class="flex gap-2">
                <input name="q" value="{{ $q ?? '' }}" placeholder="Search progress..." class="w-full rounded-2xl border border-[#D8D9DA] bg-white px-4 py-2 text-sm focus:border-[#959D90] focus:ring-[#959D90] sm:w-72">
                <button class="rounded-2xl bg-[#2F4F4F] px-4 py-2 text-sm font-bold text-white hover:bg-[#B4833D]">Search</button>
            </form>
            <a href="{{ route('instructor.progress.create') }}" class="rounded-2xl bg-[#3C4B33] px-4 py-2 text-center text-sm font-bold text-white hover:bg-[#B4833D]">Add Progress</a>
        </div>
    </header>

    <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @forelse($progress as $record)
            <article class="rounded-[26px] border border-[#D8D9DA] bg-white p-5 shadow-sm transition hover:border-[#B4833D] hover:shadow-md">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-[#61677A]">{{ $record->instrument_name ?? 'No instrument' }}</p>
                        <h2 class="mt-1 text-xl font-bold text-[#272829]" style="font-family: 'Sora', sans-serif;">{{ $record->student_name }}</h2>
                        <p class="mt-1 text-sm text-[#61677A]">{{ $record->lesson_topic ?? 'No topic' }}</p>
                    </div>
                    <p class="rounded-full bg-[#FFF6E0] px-3 py-1 text-xs font-black text-[#523D35]" style="font-family: 'JetBrains Mono', monospace;">{{ $record->performance_rating ?? '—' }}/10</p>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-[#fcf3e3] p-3"><p class="text-xs font-bold uppercase text-[#61677A]">Date</p><p class="mt-1 font-bold text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ \Carbon\Carbon::parse($record->progress_date)->format('M d, Y') }}</p></div>
                    <div class="rounded-2xl bg-[#fcf3e3] p-3"><p class="text-xs font-bold uppercase text-[#61677A]">Technical</p><p class="mt-1 font-bold text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $record->technical_skills_rating ?? '—' }}/10</p></div>
                    <div class="rounded-2xl bg-[#fcf3e3] p-3"><p class="text-xs font-bold uppercase text-[#61677A]">Effort</p><p class="mt-1 font-bold text-[#2F4F4F]" style="font-family: 'JetBrains Mono', monospace;">{{ $record->effort_rating ?? '—' }}/10</p></div>
                </div>

                @if($record->homework)
                    <div class="mt-4 rounded-2xl border border-[#D8D9DA] bg-[#FFF6E0] p-3">
                        <p class="text-xs font-bold uppercase text-[#61677A]">Homework</p>
                        <p class="mt-1 line-clamp-2 text-sm text-[#523D35]">{{ $record->homework }}</p>
                    </div>
                @endif

                <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <a href="{{ route('instructor.progress.show', $record->progress_id) }}" class="rounded-2xl bg-[#2F4F4F] px-4 py-2 text-center text-sm font-bold text-white hover:bg-[#B4833D]">View</a>
                    <a href="{{ route('instructor.progress.edit', $record->progress_id) }}" class="rounded-2xl border border-[#959D90] px-4 py-2 text-center text-sm font-bold text-[#2F4F4F] hover:bg-[#FFF6E0]">Edit</a>
                </div>
            </article>
        @empty
            <div class="rounded-[28px] border border-dashed border-[#959D90] bg-white p-10 text-center lg:col-span-2">
                <h2 class="text-2xl font-bold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">No progress records found</h2>
                <p class="mt-2 text-sm text-[#61677A]">Add a progress record after a lesson to save notes and homework.</p>
                <a href="{{ route('instructor.progress.create') }}" class="mt-5 inline-block rounded-2xl bg-[#2F4F4F] px-5 py-3 text-sm font-bold text-white hover:bg-[#B4833D]">Add Progress</a>
            </div>
        @endforelse
    </section>

    @if($progress->hasPages())
        <div>{{ $progress->links() }}</div>
    @endif
</div>
@endsection