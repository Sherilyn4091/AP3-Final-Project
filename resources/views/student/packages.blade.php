{{-- resources/views/student/packages.blade.php --}}
{{-- Purpose: Browse available lesson packages for enrollment --}}
{{-- Data Source: lesson_session table WHERE is_active = TRUE --}}

@extends('layouts.student')

@section('content')
<div class="min-h-full bg-[#F5F7F4] px-4 py-8 sm:px-6 lg:px-8" style="font-family: 'Inter', sans-serif;">
    <div class="mx-auto max-w-7xl space-y-8">

        {{-- Page Header --}}
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#768A96]">Lesson Packages</p>
            <h1 class="mt-2 text-2xl font-bold text-[#223030] sm:text-3xl" style="font-family: 'Sora', sans-serif;">Available lesson packages</h1>
            <p class="mt-2 text-sm text-[#44576D]">Choose a package that fits your learning goals.</p>
        </div>

        {{-- Check if packages exist --}}
        @if($packages->isEmpty())
            <div class="rounded-[28px] border border-[#D8DDD8] bg-white p-10 text-center shadow-sm">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#F5F7F4] text-[#768A96]">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2h-3.5a2 2 0 01-1.414-.586l-.5-.5A2 2 0 0011.172 3H6a2 2 0 00-2 2v14a2 2 0 002 2h6"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 19l2 2 4-4"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-xl font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">No packages available</h3>
                <p class="mt-2 text-sm text-[#44576D]">Please check back later for available lesson packages.</p>
            </div>
        @else
            {{-- Display Package Cards --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach($packages as $package)
                    <article class="flex flex-col overflow-hidden rounded-[28px] border border-[#D8DDD8] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                        {{-- Package Header --}}
                        <div class="border-b border-[#D8DDD8] bg-[#FCFCFA] px-6 py-5">
                            <p class="text-xs font-bold uppercase tracking-wide text-[#768A96]">{{ $package->session_count }} sessions</p>
                            <h2 class="mt-2 text-xl font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">
                                {{ $package->session_name ?? $package->session_count . '-Session Package' }}
                            </h2>
                            <p class="mt-1 text-sm text-[#44576D]">{{ $package->duration_minutes }} minutes each</p>
                        </div>

                        {{-- Package Body --}}
                        <div class="flex flex-1 flex-col p-6">
                            {{-- Price Display --}}
                            <div class="mb-6">
                                <p class="text-4xl font-extrabold text-[#29353C]" style="font-family: 'JetBrains Mono', monospace;">₱{{ number_format($package->price, 2) }}</p>
                                <p class="mt-1 text-xs text-[#768A96]">₱{{ number_format($package->price / max($package->session_count, 1), 2) }} per session</p>
                            </div>

                            {{-- Package Features/Description --}}
                            @if($package->description)
                                <p class="mb-6 text-sm leading-relaxed text-[#44576D]">{{ $package->description }}</p>
                            @endif

                            {{-- Package Highlights --}}
                            <div class="mb-6 space-y-3">
                                @foreach([
                                    $package->session_count . ' one-on-one lessons',
                                    $package->duration_minutes . ' minutes per session',
                                    'Flexible scheduling preferences',
                                    'Progress tracking included',
                                ] as $feature)
                                    <div class="flex items-start gap-2">
                                        <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-[#44576D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-sm font-medium text-[#44576D]">{{ $feature }}</span>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Enroll Now Button --}}
                            <a href="{{ route('student.enroll.form', $package->session_id) }}" class="mt-auto block rounded-2xl bg-[#29353C] px-5 py-3 text-center text-sm font-bold text-white shadow-sm transition hover:bg-[#223030]">
                                Enroll now
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Additional Info Section --}}
            <div class="rounded-[28px] border border-[#D8DDD8] bg-white p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-[#F5F7F4] text-[#44576D]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-[#223030]" style="font-family: 'Sora', sans-serif;">Need help choosing?</h3>
                        <p class="mt-1 text-sm leading-relaxed text-[#44576D]">Choose based on your goal and availability. Your final schedule is confirmed after your preferred days and time are reviewed.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&family=Sora:wght@500;600;700;800&display=swap');
</style>
@endpush
