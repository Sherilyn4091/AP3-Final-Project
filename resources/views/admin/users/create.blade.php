{{-- 
    ============================================================================ 
    ADD NEW USER PAGE - resources/views/admin/users/create.blade.php 
    ============================================================================ 
    *   **Live Role-Based Form**: Dynamically shows/hides fields based on the selected user role. 
    *   **Password Strength Indicator**: Visual feedback for password strength with eye toggle. 
    *   **Auto-formatting**: Names auto-capitalize, emails auto-lowercase. 
    *   **Email Validation**: Real-time email format checking. 
    *   **Rich Form Inputs**: Styled inputs with SVG icons for professional look. 
    *   **Vibrant Colors**: Uses warm-coral, forest-green, golden-yellow from CSS. 
    *   **Validation Feedback**: Ready for backend validation messages. 
    ============================================================================ 
--}} 
 
<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <title>Add New User - Admin Dashboard</title> 
    @vite(['resources/css/style.css', 'resources/js/app.js']) 
</head> 
<body class="bg-light-gray"> 
 
@include('layouts.admin-header') 
 
<main class="lg:ml-64 min-h-screen bg-light-gray"> 
 
    {{-- Header Section --}} 
    <header class="bg-gradient-to-r from-primary-dark to-secondary-blue shadow-lg p-6"> 
        <div class="flex items-center"> 
            <a href="{{ route('admin.users.index') }}" class="text-accent-yellow hover:text-white transition-colors mr-4"> 
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path> 
                </svg> 
            </a> 
            <div> 
                <h1 class="text-3xl font-bold text-white">Create New User</h1> 
                <p class="text-accent-yellow-light mt-1">Add a new user to the system and assign their role</p> 
            </div> 
        </div> 
    </header> 
 
    {{-- Success/Error Messages --}} 
    @if(session('success')) 
        <div class="mx-6 mt-6"> 
            <div class="bg-forest-green text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 animate-fade-in"> 
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path> 
                </svg> 
                <span class="font-semibold">{{ session('success') }}</span> 
            </div> 
        </div> 
    @endif 
 
    @if(session('error')) 
        <div class="mx-6 mt-6"> 
            <div class="bg-warm-coral text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 animate-fade-in"> 
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path> 
                </svg> 
                <span class="font-semibold">{{ session('error') }}</span> 
            </div> 
        </div> 
    @endif 
 
    <div class="p-4 lg:p-6"> 
        <div class="card p-8 max-w-5xl mx-auto"> 
            <form action="{{ route('admin.users.store') }}" method="POST" id="createUserForm"> 
                @csrf 

                {{-- Validation Errors Display --}}
                @if ($errors->any())
                    <div class="bg-warm-coral text-white px-6 py-4 rounded-lg shadow-lg mb-6">
                        <h3 class="font-bold text-lg mb-2">Please fix the following errors:</h3>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
 
                {{-- Core User Information --}} 
                <div class="border-b-2 border-golden-yellow pb-8 mb-8"> 
                    <div class="flex items-center space-x-3 mb-6"> 
                        <div class="bg-golden-yellow p-3 rounded-lg"> 
                            <svg class="w-6 h-6 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path> 
                            </svg> 
                        </div> 
                        <h2 class="text-2xl font-bold text-primary-dark">Basic Information</h2> 
                    </div> 
 
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6"> 
                        {{-- First Name --}} 
                        <div> 
                            <label for="first_name" class="block text-sm font-semibold text-primary-dark mb-2"> 
                                First Name <span class="text-warm-coral">*</span> 
                            </label> 
                            <div class="relative"> 
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"> 
                                    <svg class="w-5 h-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path> 
                                    </svg> 
                                </div> 
                                <input type="text" id="first_name" name="first_name" class="input-field"  
                                       placeholder="e.g., Juan" required value="{{ old('first_name') }}"> 
                            </div> 
                            @error('first_name')<p class="text-warm-coral text-xs mt-1 font-medium">{{ $message }}</p>@enderror 
                        </div> 
 
                        {{-- Last Name --}} 
                        <div> 
                            <label for="last_name" class="block text-sm font-semibold text-primary-dark mb-2"> 
                                Last Name <span class="text-warm-coral">*</span> 
                            </label> 
                            <div class="relative"> 
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"> 
                                    <svg class="w-5 h-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path> 
                                    </svg> 
                                </div> 
                                <input type="text" id="last_name" name="last_name" class="input-field"  
                                       placeholder="e.g., Dela Cruz" required value="{{ old('last_name') }}"> 
                            </div> 
                            @error('last_name')<p class="text-warm-coral text-xs mt-1 font-medium">{{ $message }}</p>@enderror 
                        </div> 
 
                        {{-- Email Address --}} 
                        <div> 
                            <label for="email" class="block text-sm font-semibold text-primary-dark mb-2"> 
                                Email Address <span class="text-warm-coral">*</span> 
                            </label> 
                            <div class="relative"> 
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"> 
                                    <svg class="w-5 h-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path> 
                                    </svg> 
                                </div> 
                                <input type="email" id="email" name="email" class="input-field"  
                                       placeholder="e.g., juan@musiclab.com" required value="{{ old('email') }}"> 
                            </div> 
                            <p id="email-validation-msg" class="text-xs mt-1 hidden"></p> 
                            @error('email')<p class="text-warm-coral text-xs mt-1 font-medium">{{ $message }}</p>@enderror 
                        </div> 
 
                        {{-- Phone Number --}} 
                        <div> 
                            <label for="phone" class="block text-sm font-semibold text-primary-dark mb-2"> 
                                Phone Number (11 digits) 
                            </label> 
                            <div class="relative"> 
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"> 
                                    <svg class="w-5 h-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path> 
                                    </svg> 
                                </div> 
                                <input type="tel" id="phone" name="phone" class="input-field"  
                                       placeholder="e.g., 09171234567" maxlength="11" pattern="\d{11}" value="{{ old('phone') }}"> 
                            </div> 
                            @error('phone')<p class="text-warm-coral text-xs mt-1 font-medium">{{ $message }}</p>@enderror 
                        </div> 
 
                        {{-- Password with Strength Indicator --}} 
                        <div> 
                            <label for="password" class="block text-sm font-semibold text-primary-dark mb-2"> 
                                Password <span class="text-warm-coral">*</span> 
                            </label> 
                            <div class="relative"> 
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"> 
                                    <svg class="w-5 h-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path> 
                                    </svg> 
                                </div> 
                                <input type="password" id="password" name="password" class="input-field pr-12"  
                                       placeholder="Enter a strong password" required> 
                                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center"> 
                                    <svg id="eyeOpen" class="w-5 h-5 text-secondary-blue hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path> 
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path> 
                                    </svg> 
                                    <svg id="eyeClosed" class="w-5 h-5 text-secondary-blue hover:text-primary-dark transition-colors hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path> 
                                    </svg> 
                                </button> 
                            </div> 
                            {{-- Password Strength Indicator --}} 
                            <div class="mt-2 space-y-2"> 
                                <div class="flex space-x-1"> 
                                    <div id="strength-bar-1" class="h-1 flex-1 bg-gray-200 rounded transition-all"></div> 
                                    <div id="strength-bar-2" class="h-1 flex-1 bg-gray-200 rounded transition-all"></div> 
                                    <div id="strength-bar-3" class="h-1 flex-1 bg-gray-200 rounded transition-all"></div> 
                                    <div id="strength-bar-4" class="h-1 flex-1 bg-gray-200 rounded transition-all"></div> 
                                </div> 
                                <p id="password-strength-text" class="text-xs font-medium text-gray-500"> 
                                    Must include: 8+ characters, uppercase, lowercase, number, symbol 
                                </p> 
                            </div> 
                            @error('password')<p class="text-warm-coral text-xs mt-1 font-medium">{{ $message }}</p>@enderror 
                        </div> 
 
                        {{-- Assign Role --}} 
                        <div> 
                            <label for="role" class="block text-sm font-semibold text-primary-dark mb-2"> 
                                Assign Role <span class="text-warm-coral">*</span> 
                            </label> 
                            <div class="relative"> 
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"> 
                                    <svg class="w-5 h-5 text-secondary-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path> 
                                    </svg> 
                                </div> 
                                <select id="role" name="role" class="input-field" required> 
                                    <option value="" disabled selected>Select a role...</option> 
                                    <option value="student">Student</option> 
                                    <option value="instructor">Instructor</option> 
                                    <option value="sales">Sales Staff</option> 
                                    <option value="all_around_staff">All-Around Staff</option> 
                                </select> 
                            </div> 
                            @error('role')<p class="text-warm-coral text-xs mt-1 font-medium">{{ $message }}</p>@enderror 
                        </div> 
                    </div> 
                </div> 
 
                {{-- Dynamic Section for Instructor Details --}} 
                <div id="instructor-fields" class="hidden animate-fade-in"> 
                    <div class="flex items-center space-x-3 mb-6"> 
                        <div class="bg-warm-coral p-3 rounded-lg"> 
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path> 
                            </svg> 
                        </div> 
                        <h3 class="text-2xl font-bold text-primary-dark">Instructor Details</h3> 
                    </div> 
 
                    {{-- Professional Info --}} 
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6"> 
                        <div> 
                            <label for="years_of_experience" class="block text-sm font-semibold text-primary-dark mb-2"> 
                                Years of Experience <span class="text-warm-coral">*</span> 
                            </label> 
                            <input type="number" 
                                id="years_of_experience" 
                                name="instructor[years_of_experience]"  
                                class="input-field" 
                                placeholder="e.g., 5" 
                                min="0" 
                                value="{{ old('instructor.years_of_experience') }}">
                            @error('instructor.years_of_experience')<p class="text-warm-coral text-xs mt-1 font-medium">{{ $message }}</p>@enderror 
                        </div> 
                        <div> 
                            <label for="education_level" class="block text-sm font-semibold text-primary-dark mb-2">Education Level</label> 
                            <input type="text" id="education_level" name="instructor[education_level]"  
                                class="input-field" placeholder="e.g., Bachelor's Degree" value="{{ old('instructor.education_level') }}"> 
                            @error('instructor.education_level')<p class="text-warm-coral text-xs mt-1 font-medium">{{ $message }}</p>@enderror 
                        </div>
                        <div> 
                            <label for="education_level" class="block text-sm font-semibold text-primary-dark mb-2">Education Level</label> 
                            <input type="text" id="education_level" name="instructor[education_level]"  
                                   class="input-field" placeholder="e.g., Bachelor's Degree" value="{{ old('instructor.education_level') }}"> 
                            @error('instructor.education_level')<p class="text-warm-coral text-xs mt-1 font-medium">{{ $message }}</p>@enderror 
                        </div> 
                        <div> 
                            <label for="music_degree" class="block text-sm font-semibold text-primary-dark mb-2">Music Degree</label> 
                            <input type="text" id="music_degree" name="instructor[music_degree]"  
                                   class="input-field" placeholder="e.g., B.A. in Music Performance" value="{{ old('instructor.music_degree') }}"> 
                            @error('instructor.music_degree')<p class="text-warm-coral text-xs mt-1 font-medium">{{ $message }}</p>@enderror 
                        </div> 
                        <div> 
                            <label for="teaching_style" class="block text-sm font-semibold text-primary-dark mb-2">Teaching Style</label> 
                            <input type="text" id="teaching_style" name="instructor[teaching_style]"  
                                   class="input-field" placeholder="e.g., Suzuki, Kodály, Orff" value="{{ old('instructor.teaching_style') }}"> 
                            @error('instructor.teaching_style')<p class="text-warm-coral text-xs mt-1 font-medium">{{ $message }}</p>@enderror 
                        </div> 
                        <div> 
                            <label for="languages_spoken" class="block text-sm font-semibold text-primary-dark mb-2">Languages Spoken</label> 
                            <input type="text" id="languages_spoken" name="instructor[languages_spoken]"  
                                   class="input-field" placeholder="e.g., English, Spanish, Tagalog" value="{{ old('instructor.languages_spoken') }}"> 
                            @error('instructor.languages_spoken')<p class="text-warm-coral text-xs mt-1 font-medium">{{ $message }}</p>@enderror 
                        </div> 
                    </div> 
 
                    {{-- Biography and Certifications --}} 
                    <div class="grid grid-cols-1 gap-6 mb-6"> 
                        <div> 
                            <label for="bio" class="block text-sm font-semibold text-primary-dark mb-2">Biography</label> 
                            <textarea id="bio" name="instructor[bio]" class="textarea-field" rows="3"  
                                      placeholder="A brief introduction about the instructor...">{{ old('instructor.bio') }}</textarea> 
                            @error('instructor.bio')<p class="text-warm-coral text-xs mt-1 font-medium">{{ $message }}</p>@enderror 
                        </div> 
                        <div> 
                            <label for="certifications" class="block text-sm font-semibold text-primary-dark mb-2">Certifications</label> 
                            <textarea id="certifications" name="instructor[certifications]" class="textarea-field" rows="3"  
                                      placeholder="List any relevant certifications, one per line.">{{ old('instructor.certifications') }}</textarea> 
                            @error('instructor.certifications')<p class="text-warm-coral text-xs mt-1 font-medium">{{ $message }}</p>@enderror 
                        </div> 
                    </div> 
 
                    {{-- Specializations --}} 
                    <div class="mb-6"> 
                        <label class="block text-sm font-semibold text-primary-dark mb-2">Specializations</label> 
                        <div class="p-4 border-2 border-forest-green rounded-lg bg-gray-50"> 
                            <div id="specialization-pills" class="flex flex-wrap gap-2 min-h-[2rem]"> 
                                <p class="text-sm text-gray-500">No specializations assigned yet.</p> 
                            </div> 
                            <button type="button" id="assign-spec-btn" class="mt-4 bg-forest-green hover:bg-forest-green-dark text-white font-semibold py-2 px-6 rounded-lg transition-all duration-300 active:scale-95"> 
                                Assign Specializations 
                            </button> 
                        </div> 
                    </div> 
 
                    {{-- Availability --}} 
                    <div class="p-6 border-2 border-secondary-blue rounded-lg bg-gray-50"> 
                        <h4 class="text-lg font-bold text-primary-dark mb-4">Availability Settings</h4> 
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6"> 
                            <div class="md:col-span-2 flex items-center"> 
                                <input type="checkbox" id="is_available" name="instructor[is_available]"  
                                       class="checkbox-custom" checked> 
                                <label for="is_available" class="ml-3 text-sm font-medium text-gray-900"> 
                                    Available for new students 
                                </label> 
                            </div> 
                            <div> 
                                <label for="max_students_per_day" class="block text-sm font-semibold text-primary-dark mb-2">Max Students Per Day</label> 
                                <input type="number" id="max_students_per_day" name="instructor[max_students_per_day]"  
                                       class="input-field" placeholder="e.g., 8" min="1" value="{{ old('instructor.max_students_per_day', 8) }}"> 
                                @error('instructor.max_students_per_day')<p class="text-warm-coral text-xs mt-1 font-medium">{{ $message }}</p>@enderror 
                            </div> 
                            <div> 
                                <label for="preferred_time_slots" class="block text-sm font-semibold text-primary-dark mb-2">Preferred Time Slots</label> 
                                <input type="text" id="preferred_time_slots" name="instructor[preferred_time_slots]"  
                                       class="input-field" placeholder="e.g., 2:00 PM - 6:00 PM" value="{{ old('instructor.preferred_time_slots') }}"> 
                                @error('instructor.preferred_time_slots')<p class="text-warm-coral text-xs mt-1 font-medium">{{ $message }}</p>@enderror 
                            </div> 
                            <div class="md:col-span-2"> 
                                <label class="block text-sm font-semibold text-primary-dark mb-3">Available Days</label> 
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3"> 
                                    @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day) 
                                        <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg hover:bg-accent-yellow-light hover:border-forest-green cursor-pointer transition-all"> 
                                            <input type="checkbox" name="instructor[available_days][]" value="{{ $day }}"  
                                                   class="checkbox-custom"> 
                                            <span class="ml-2 text-sm font-medium text-gray-700">{{ $day }}</span> 
                                        </label> 
                                    @endforeach 
                                </div> 
                            </div> 
                        </div> 
                    </div> 
                </div> 
 
                {{-- Form Actions --}} 
                <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t-2 border-gray-200"> 
                    <a href="{{ route('admin.users.index') }}" class="px-8 py-3 bg-gray-200 hover:bg-gray-300 text-primary-dark font-semibold rounded-lg transition-all duration-300 active:scale-95"> 
                        Cancel 
                    </a> 
                    <button type="submit" class="px-8 py-3 bg-forest-green hover:bg-forest-green-dark text-white font-bold rounded-lg shadow-lg transition-all duration-300 active:scale-95"> 
                        Create User 
                    </button> 
                </div> 
            </form> 
        </div> 
    </div> 
</main> 
 
{{-- Toast Notification Container --}} 
<div id="toast-container" class="fixed bottom-5 right-5 z-[100] space-y-3"></div> 
 
{{-- Specialization Modal --}} 
<div id="specialization-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4"> 
    <div class="bg-white rounded-lg max-w-lg w-full p-6 shadow-2xl animate-fade-in"> 
        <div class="flex items-center justify-between mb-6"> 
            <h3 class="text-2xl font-bold text-primary-dark">Assign Specializations</h3> 
            <button type="button" id="close-spec-modal-btn" class="text-gray-400 hover:text-gray-600 transition-colors"> 
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"> 
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path> 
                </svg> 
            </button> 
        </div> 
         
        <div class="space-y-3 max-h-96 overflow-y-auto scrollbar-custom"> 
            @foreach($specializations as $specialization) 
                <label class="flex items-center space-x-3 p-4 border-2 border-gray-300 rounded-lg hover:bg-accent-yellow-light hover:border-forest-green cursor-pointer transition-all"> 
                    <input type="checkbox" name="specializations[]" value="{{ $specialization->specialization_id }}"  
                           class="checkbox-custom specialization-checkbox"> 
                    <span class="text-gray-800 font-medium">{{ $specialization->specialization_name }}</span> 
                </label> 
            @endforeach 
        </div> 
         
        <div class="mt-6"> 
            <label for="primary_specialization" class="block text-sm font-semibold text-primary-dark mb-2"> 
                Primary Specialization 
            </label> 
            <select name="primary_specialization" id="primary_specialization" class="input-field"> 
                <option value="">None</option> 
                @foreach($specializations as $specialization) 
                    <option value="{{ $specialization->specialization_id }}">{{ $specialization->specialization_name }}</option> 
                @endforeach 
            </select> 
        </div> 
         
        <div class="flex justify-end space-x-4 mt-6"> 
            <button type="button" id="close-spec-modal-btn-2" class="close-spec-modal px-6 py-3 bg-gray-200 hover:bg-gray-300 text-primary-dark font-semibold rounded-lg transition-all duration-300 active:scale-95"> 
                Cancel 
            </button> 
            <button type="button" id="save-spec-modal-btn" class="px-6 py-3 bg-forest-green hover:bg-forest-green-dark text-white font-bold rounded-lg transition-all duration-300 active:scale-95"> 
                Save Specializations 
            </button> 
        </div> 
    </div> 
</div> 
 
{{-- Script to show toast notifications based on session data --}} 
@if (session('success')) 
    <script> 
        document.addEventListener('DOMContentLoaded', function() { 
            showToast("{{ session('success') }}", 'success'); 
        }); 
    </script> 
@endif 
 
@if (session('error')) 
    <script> 
        document.addEventListener('DOMContentLoaded', function() { 
            showToast("{{ session('error') }}", 'error'); 
        }); 
    </script> 
@endif 
 
</body> 
</html>