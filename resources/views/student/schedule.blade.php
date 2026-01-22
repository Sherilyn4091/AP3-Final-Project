@extends('layouts.student')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My schedule</h1>
        <p class="mt-2 text-base text-gray-700">Your upcoming and past lessons</p>
    </div>

    {{-- Empty State --}}
    @if($schedules->isEmpty())
        <div class="bg-white border border-gray-300 rounded-xl shadow p-12 text-center">
            <svg class="w-20 h-20 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3 class="text-xl font-bold text-gray-900 mb-2">No lessons scheduled yet</h3>
            <p class="text-base text-gray-600 font-medium">Once enrolled, your lessons will appear here</p>
        </div>
    @else
        {{-- Schedule List Grouped by Date --}}
        <div class="space-y-6">
            @foreach($schedules as $date => $dailySchedules)
                <div class="bg-white border border-gray-300 rounded-xl shadow overflow-hidden">
                    
                    {{-- Date Header --}}
                    <div class="px-6 py-4 bg-gray-100 border-b border-gray-300">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900">
                                {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}
                            </h3>
                            @if($date === now()->toDateString())
                                <span class="px-3 py-1 bg-indigo-600 text-white text-xs font-bold rounded-lg shadow-sm">Today</span>
                            @elseif(\Carbon\Carbon::parse($date)->isFuture())
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-lg border border-blue-300">Upcoming</span>
                            @else
                                <span class="px-3 py-1 bg-gray-200 text-gray-700 text-xs font-bold rounded-lg border border-gray-300">Past</span>
                            @endif
                        </div>
                    </div>

                    {{-- Lessons for this Date --}}
                    <div class="divide-y divide-gray-200">
                        @foreach($dailySchedules as $schedule)
                            <div class="px-6 py-5 hover:bg-gray-50 transition">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    
                                    {{-- Left: Time & Details --}}
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            {{-- Time --}}
                                            <div class="flex items-center gap-2 text-gray-900">
                                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span class="font-bold">
                                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} – 
                                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                                </span>
                                            </div>

                                            {{-- Status Badge --}}
                                            @php
                                                $statusColors = [
                                                    'scheduled' => 'bg-blue-100 text-blue-800 border-blue-300',
                                                    'completed' => 'bg-green-100 text-green-800 border-green-300',
                                                    'cancelled' => 'bg-red-100 text-red-800 border-red-300',
                                                    'no_class' => 'bg-gray-100 text-gray-700 border-gray-300',
                                                    'rescheduled' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                                    'substitute' => 'bg-orange-100 text-orange-800 border-orange-300',
                                                    'no_show' => 'bg-red-100 text-red-800 border-red-300',
                                                    'in_progress' => 'bg-purple-100 text-purple-800 border-purple-300',
                                                ];
                                                $statusClass = $statusColors[$schedule->status] ?? 'bg-gray-100 text-gray-700 border-gray-300';
                                            @endphp
                                            <span class="px-3 py-1 text-xs font-bold rounded-lg border {{ $statusClass }}">
                                                {{ ucfirst(str_replace('_', ' ', $schedule->status)) }}
                                            </span>
                                        </div>

                                        {{-- Instructor --}}
                                        <div class="flex items-center gap-2 text-gray-700 mb-1 ml-7">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <span class="text-sm font-semibold">
                                                Instructor: {{ $schedule->instructor->first_name ?? 'N/A' }} {{ $schedule->instructor->last_name ?? '' }}
                                            </span>
                                        </div>

                                        {{-- Room --}}
                                        <div class="flex items-center gap-2 text-gray-600 ml-7">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span class="text-sm">Room: {{ $schedule->room_number ?? 'TBA' }}</span>
                                        </div>

                                        {{-- Lesson Topic --}}
                                        @if($schedule->lesson_topic)
                                            <div class="flex items-center gap-2 text-gray-600 mt-1 ml-7">
                                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <span class="text-sm font-medium">{{ $schedule->lesson_topic }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Right: View Details Button --}}
                                    <div>
                                        <button onclick="showLessonDetails({{ $schedule->schedule_id }})"
                                                class="px-4 py-2 text-sm font-semibold text-indigo-700 hover:text-white hover:bg-indigo-600 rounded-lg transition border border-indigo-300 hover:border-indigo-600 shadow-sm">
                                            View details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

{{-- Lesson Details Modal --}}
<div id="lessonModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white px-6 py-4 border-b border-gray-300 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">Lesson details</h3>
            <button onclick="closeLessonModal()" class="text-gray-500 hover:text-gray-700 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="lessonDetailsContent" class="p-6">
            <div class="text-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
                <p class="text-gray-600 mt-4 font-medium">Loading...</p>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript for Modal --}}
<script>
function showLessonDetails(scheduleId) {
    const modal = document.getElementById('lessonModal');
    const content = document.getElementById('lessonDetailsContent');
    
    modal.classList.remove('hidden');
    
    // Fetch lesson details
    fetch(`/student/schedule/${scheduleId}`)
        .then(response => response.json())
        .then(data => {
            content.innerHTML = `
                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                            <p class="text-sm font-bold text-gray-600 mb-1 uppercase tracking-wide">Date</p>
                            <p class="text-base font-bold text-gray-900">${data.schedule_date}</p>
                        </div>
                        <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                            <p class="text-sm font-bold text-gray-600 mb-1 uppercase tracking-wide">Time</p>
                            <p class="text-base font-bold text-gray-900">${data.start_time} – ${data.end_time}</p>
                        </div>
                    </div>
                    
                    ${data.lesson_content ? `
                        <div class="bg-blue-50 border border-blue-300 rounded-lg p-4">
                            <p class="text-sm font-bold text-blue-900 mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Lesson content
                            </p>
                            <p class="text-gray-800 leading-relaxed">${data.lesson_content}</p>
                        </div>
                    ` : ''}
                    
                    ${data.notes ? `
                        <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4">
                            <p class="text-sm font-bold text-yellow-900 mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                </svg>
                                Notes
                            </p>
                            <p class="text-gray-800 leading-relaxed">${data.notes}</p>
                        </div>
                    ` : ''}
                </div>
            `;
        })
        .catch(error => {
            content.innerHTML = `
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto mb-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-red-600 font-semibold">Error loading details</p>
                </div>
            `;
            console.error('Error:', error);
        });
}

function closeLessonModal() {
    document.getElementById('lessonModal').classList.add('hidden');
}

// Close modal on outside click
document.getElementById('lessonModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLessonModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLessonModal();
    }
});
</script>
@endsection