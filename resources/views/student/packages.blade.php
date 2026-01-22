{{-- resources/views/student/packages.blade.php --}}
{{-- Purpose: Browse available lesson packages for enrollment --}}
{{-- Data Source: lesson_session table WHERE is_active = TRUE --}}

@extends('layouts.student')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Available lesson packages</h1>
        <p class="mt-2 text-base text-gray-600">Choose a package that fits your learning goals</p>
    </div>

    {{-- Check if packages exist --}}
    @if($packages->isEmpty())
        <div class="bg-yellow-50 border-2 border-yellow-400 rounded-xl p-8 text-center">
            <div class="text-5xl mb-4">📦</div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">No packages available</h3>
            <p class="text-gray-700">Please check back later for available lesson packages</p>
        </div>
    @else
        {{-- Display Package Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($packages as $package)
                <div class="bg-white border-2 border-gray-200 rounded-xl shadow-sm hover:shadow-lg hover:border-indigo-400 transition-all duration-300">
                    
                    {{-- Package Header --}}
                    <div class="px-6 py-5 bg-gradient-to-br from-indigo-50 to-white border-b-2 border-gray-200">
                        <h3 class="text-xl font-bold text-gray-900">
                            {{ $package->session_name ?? $package->session_count . '-session package' }}
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $package->session_count }} sessions • {{ $package->duration_minutes }} min each
                        </p>
                    </div>

                    {{-- Package Body --}}
                    <div class="px-6 py-6">
                        
                        {{-- Price Display --}}
                        <div class="mb-6">
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-bold text-indigo-700">₱{{ number_format($package->price, 2) }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                ₱{{ number_format($package->price / $package->session_count, 2) }} per session
                            </p>
                        </div>

                        {{-- Package Features/Description --}}
                        @if($package->description)
                            <div class="mb-6">
                                <p class="text-sm text-gray-700 leading-relaxed">{{ $package->description }}</p>
                            </div>
                        @endif

                        {{-- Package Highlights --}}
                        <div class="space-y-3 mb-6">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-gray-700">{{ $package->session_count }} one-on-one lessons</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-gray-700">{{ $package->duration_minutes }} minutes per session</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-gray-700">Flexible scheduling</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-gray-700">Progress tracking included</span>
                            </div>
                        </div>

                        {{-- Enroll Now Button --}}
                        <a href="{{ route('student.enroll.form', $package->session_id) }}" 
                           class="block w-full px-5 py-3 text-center text-sm font-bold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                            Enroll now
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Additional Info Section --}}
        <div class="mt-12 bg-blue-50 border border-blue-200 rounded-xl p-6">
            <div class="flex items-start gap-4">
                <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h4 class="text-base font-bold text-gray-900 mb-2">Need help choosing?</h4>
                    <p class="text-sm text-gray-700 leading-relaxed">
                        Not sure which package is right for you? Contact our team for personalized recommendations based on your skill level and learning goals.
                    </p>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection