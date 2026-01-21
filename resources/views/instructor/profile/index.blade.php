@extends('layouts.instructor')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-3xl font-bold mb-8">My Profile</h1>

    <div class="bg-white rounded-xl shadow-sm border p-8">
        <form method="POST" action="{{ route('instructor.profile.update') }}">
            @csrf
            @method('PATCH')

            <!-- Bio -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Bio / About Me</label>
                <textarea name="bio" rows="5" class="w-full rounded-lg border-gray-300">{{ $instructor->bio ?? '' }}</textarea>
            </div>

            <!-- Teaching Style -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Teaching Style</label>
                <textarea name="teaching_style" rows="3" class="w-full rounded-lg border-gray-300">{{ $instructor->teaching_style ?? '' }}</textarea>
            </div>

            <!-- Availability -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Available Days</label>
                    <input type="text" name="available_days" value="{{ $instructor->available_days ?? '' }}" 
                           class="w-full rounded-lg border-gray-300" placeholder="Mon, Wed, Fri">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Time Slots</label>
                    <input type="text" name="preferred_time_slots" value="{{ $instructor->preferred_time_slots ?? '' }}" 
                           class="w-full rounded-lg border-gray-300" placeholder="9AM-12PM, 2PM-6PM">
                </div>
            </div>

            <!-- Max Students -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Max Students Per Day</label>
                <input type="number" name="max_students_per_day" value="{{ $instructor->max_students_per_day ?? 8 }}" 
                       min="1" max="20" class="w-full rounded-lg border-gray-300">
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Save Profile
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
