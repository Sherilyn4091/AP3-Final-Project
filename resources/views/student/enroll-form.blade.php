{{-- resources/views/student/enroll-form.blade.php --}}
{{-- Purpose: Process new student enrollment --}}
{{-- Allows student to select instrument, genre, instructor, and confirm package enrollment --}}

@extends('layouts.student')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Enroll in package</h1>
        <p class="text-sm text-gray-600 mt-1">Complete your enrollment details below</p>
    </div>

    {{-- Selected Package Summary --}}
    <div class="bg-indigo-50 border-2 border-indigo-200 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-700">Selected package</p>
                <p class="text-lg font-bold text-gray-900">{{ $package->session_name ?? $package->session_count . '-session package' }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">Total price</p>
                <p class="text-2xl font-bold text-indigo-700">₱{{ number_format($package->price, 2) }}</p>
            </div>
        </div>
    </div>

    {{-- Enrollment Form --}}
    <form action="{{ route('student.enroll.process') }}" method="POST" class="bg-white border border-gray-300 rounded-xl shadow-sm p-6">
        @csrf
        <input type="hidden" name="session_id" value="{{ $package->session_id }}">

        {{-- Instrument Selection --}}
        <div class="mb-5">
            <label class="block text-sm font-semibold text-gray-900 mb-2">Primary instrument</label>
            <select name="instrument_id" id="instrument_id" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="" class="text-gray-500">Select an instrument</option>
                @foreach(DB::table('instrument')->where('is_active', true)->orderBy('instrument_name')->get() as $instrument)
                    <option value="{{ $instrument->instrument_id }}" class="text-gray-900">{{ $instrument->instrument_name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Preferred Genre --}}
        <div class="mb-5">
            <label class="block text-sm font-semibold text-gray-900 mb-2">Preferred genre</label>
            <select name="preferred_genre_id" id="preferred_genre_id"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="" class="text-gray-500">Select a genre (optional)</option>
                @foreach(DB::table('genre')->where('is_active', true)->orderBy('genre_name')->get() as $genre)
                    <option value="{{ $genre->genre_id }}" class="text-gray-900">{{ $genre->genre_name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Instructor Selection --}}
        <div class="mb-5">
            <label class="block text-sm font-semibold text-gray-900 mb-2">Select instructor</label>
            <select name="instructor_id" id="instructor_id" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="" class="text-gray-500">Select an instructor</option>
                @foreach($instructors as $instructor)
                    <option value="{{ $instructor->instructor_id }}" class="text-gray-900">
                        {{ $instructor->first_name }} {{ $instructor->last_name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Start Date --}}
        <div class="mb-5">
            <label class="block text-sm font-semibold text-gray-900 mb-2">Start date</label>
            <input type="date" name="start_date" id="start_date" required
                   min="{{ date('Y-m-d') }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        {{-- Notes --}}
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-900 mb-2">Notes (optional)</label>
            <textarea name="notes" id="notes" rows="3"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Any special requests or information..."></textarea>
        </div>

        {{-- Action Buttons --}}
        <div class="flex gap-3 pt-4 border-t border-gray-200">
            <a href="{{ route('student.packages') }}"
               class="flex-1 px-5 py-2.5 text-center text-sm font-semibold bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300 transition">
                Cancel
            </a>
            <button type="submit"
                    class="flex-1 px-5 py-2.5 text-sm font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-sm">
                Confirm enrollment
            </button>
        </div>
    </form>

</div>

{{-- Dynamic Instructor Filtering Script --}}
<script>
document.getElementById('instrument_id').addEventListener('change', function() {
    const instrumentId = this.value;
    const instructorSelect = document.getElementById('instructor_id');
    
    if (!instrumentId) {
        instructorSelect.innerHTML = '<option value="" class="text-gray-500">Select an instructor</option>';
        return;
    }
    
    // Fetch instructors with matching specialization
    fetch(`/student/api/instructors-by-instrument/${instrumentId}`)
        .then(response => response.json())
        .then(data => {
            instructorSelect.innerHTML = '<option value="" class="text-gray-500">Select an instructor</option>';
            data.forEach(instructor => {
                const option = document.createElement('option');
                option.value = instructor.instructor_id;
                option.className = 'text-gray-900';
                option.textContent = `${instructor.first_name} ${instructor.last_name}`;
                instructorSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading instructors:', error));
});
</script>
@endsection