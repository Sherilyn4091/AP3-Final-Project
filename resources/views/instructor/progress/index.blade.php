@extends('layouts.instructor')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header + Search + Add -->
    <div class="mb-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Progress Records</h1>
            <p class="mt-1 text-gray-600">View and manage lesson progress notes you’ve recorded</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <form method="GET" action="{{ route('instructor.progress.index') }}" class="relative w-full sm:w-80">
                <input
                    type="text"
                    name="q"
                    value="{{ $q ?? '' }}"
                    placeholder="Search student or topic..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </form>

            <a href="{{ route('instructor.progress.create') }}"
               class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition text-sm">
                + Add Progress
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-gray-50 border border-gray-200 text-gray-800 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($progress->isEmpty())
        <div class="bg-white rounded-xl border border-gray-100 p-12 text-center">
            <h3 class="text-xl font-medium text-gray-700">No progress records yet</h3>
            <p class="mt-2 text-gray-500">
                Click <span class="font-medium">Add Progress</span> after lessons to record notes and ratings.
            </p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr class="text-left text-gray-600">
                            <th class="px-6 py-3 font-medium">Date</th>
                            <th class="px-6 py-3 font-medium">Student</th>
                            <th class="px-6 py-3 font-medium">Lesson Topic</th>
                            <th class="px-6 py-3 font-medium">Ratings</th>
                            <th class="px-6 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @foreach($progress as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-900 whitespace-nowrap">
                                    {{ optional($p->progress_date)->format('Y-m-d') ?? 'N/A' }}
                                </td>

                                <td class="px-6 py-4 text-gray-900">
                                    {{ $p->student->first_name ?? '' }} {{ $p->student->last_name ?? '' }}
                                </td>

                                <td class="px-6 py-4 text-gray-700">
                                    {{ $p->lesson_topic ?? '—' }}
                                </td>

                                <td class="px-6 py-4 text-gray-700 whitespace-nowrap">
                                    P: {{ $p->performance_rating ?? '-' }}
                                    • T: {{ $p->technical_skills_rating ?? '-' }}
                                    • M: {{ $p->musicality_rating ?? '-' }}
                                    • E: {{ $p->effort_rating ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('instructor.progress.show', $p->progress_id) }}"
                                           class="inline-flex items-center px-3 py-1.5 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition text-xs">
                                            View
                                        </a>

                                        <a href="{{ route('instructor.progress.edit', $p->progress_id) }}"
                                           class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition text-xs">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>

        <div class="mt-8">
            {{ $progress->links() }}
        </div>
    @endif

</div>
@endsection
