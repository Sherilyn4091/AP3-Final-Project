@extends('layouts.instructor')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Progress</h1>
            <p class="mt-1 text-gray-600">Update notes and ratings for this lesson</p>
        </div>

        <a href="{{ route('instructor.progress.show', $progress->progress_id) }}"
           class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition text-sm">
            Back
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        @include('instructor.progress._form', [
            'mode' => 'edit',
            'progress' => $progress,
        ])
    </div>
</div>
@endsection