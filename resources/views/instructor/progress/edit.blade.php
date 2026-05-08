{{-- resources/views/instructor/progress/edit.blade.php --}}
@extends('layouts.instructor')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#B4833D]">Progress</p>
            <h1 class="mt-2 text-3xl font-extrabold text-[#2F4F4F]" style="font-family: 'Sora', sans-serif;">Edit Progress</h1>
            <p class="mt-2 text-sm text-[#61677A]">Update notes, ratings, homework, and next lesson focus.</p>
        </div>
        <a href="{{ route('instructor.progress.show', $progress->progress_id) }}" class="rounded-2xl border border-[#959D90] bg-white px-4 py-2 text-sm font-bold text-[#2F4F4F] hover:bg-[#FFF6E0]">Back</a>
    </header>

    <div class="rounded-[28px] border border-[#D8D9DA] bg-white p-5 shadow-sm sm:p-6">
        @include('instructor.progress._form', ['mode' => 'edit', 'progress' => $progress])
    </div>
</div>
@endsection