{{--  
    This partial represents a single row in the users table. 
    It contains both the view state and the hidden inline edit form. 
--}} 
<tr id="user-row-{{ $user->user_id }}" class="border-b transition-colors duration-200 hover:bg-accent-yellow hover:bg-opacity-20"> 
     
    {{-- Checkbox --}} 
    <td class="px-4 py-3 align-middle"> 
        <input type="checkbox" class="user-checkbox cursor-pointer" value="{{ $user->user_id }}" onchange="updateBulkActions()"> 
    </td> 
     
    {{-- User ID --}} 
    <td class="px-4 py-3 align-middle font-bold text-primary-dark"> 
        {{ $user->user_id }} 
    </td> 
     
    {{-- Name (Editable) --}} 
    <td class="px-4 py-3 align-middle font-semibold text-gray-800 min-w-[200px]">
        <span id="name-view-{{ $user->user_id }}">{{ $user->full_name }}</span> 
        <input type="text" id="name-edit-{{ $user->user_id }}" value="{{ $user->full_name }}" class="input-field hidden w-full min-w-[200px]">
    </td> 
     
    {{-- Email (Editable) --}} 
    <td class="px-4 py-3 align-middle text-secondary-blue"> 
        <span id="email-view-{{ $user->user_id }}">{{ $user->user_email }}</span> 
        <input type="email" id="email-edit-{{ $user->user_id }}" value="{{ $user->user_email }}" class="input-field hidden w-full"> 
    </td> 
     
    {{-- Role --}}
    <td class="px-4 py-3 align-middle">
        @php
            $roleClass = match($user->user_role) {
                'super_admin' => '!bg-golden-yellow text-white border-golden-yellow',
                'student' => 'bg-blue-100 text-blue-800 border-blue-300',
                'instructor' => 'bg-purple-100 text-purple-800 border-purple-300',
                default => 'bg-gray-100 text-gray-800 border-gray-300'
            };
        @endphp
        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide border {{ $roleClass }}">
            {{ $user->user_role === 'super_admin' ? 'SUPER ADMIN' : ucfirst(str_replace('_', ' ', $user->user_role)) }}
        </span>
    </td>

    {{-- Status --}}
    <td class="px-4 py-3 align-middle">
        @if($user->user_role === 'super_admin')
            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide border !bg-forest-green text-white border-forest-green">
                Active
            </span>
        @else
            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide border {{ $user->is_active ? 'bg-green-100 text-green-800 border-green-300' : 'bg-red-100 text-red-800 border-red-300' }}">
                {{ $user->is_active ? 'Active' : 'Inactive' }}
            </span>
        @endif
    </td>
     
    {{-- Timestamps --}} 
    <td class="px-4 py-3 align-middle text-xs text-gray-600">{{ $user->last_login ? \Carbon\Carbon::parse($user->last_login)->diffForHumans() : 'Never' }}</td> 
    <td class="px-4 py-3 align-middle text-xs text-gray-600">{{ \Carbon\Carbon::parse($user->created_at)->format('M d, Y') }}</td> 
    <td class="px-4 py-3 align-middle text-xs text-gray-600">{{ \Carbon\Carbon::parse($user->updated_at)->diffForHumans() }}</td> 
     
    {{-- Actions --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm">
        <div class="flex gap-2">
            
            {{-- Edit/Save Button (toggles between edit and save mode) --}}
            <button onclick="toggleEdit({{ $user->user_id }})" class="relative p-2 rounded-lg transition-all duration-200 hover:bg-secondary-blue hover:text-white focus:outline-none focus:ring-2 focus:ring-secondary-blue focus:ring-opacity-50 active:scale-95 group">
                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 text-xs font-medium text-white bg-gray-900 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap pointer-events-none z-50">Edit</span>
                <svg id="edit-icon-{{ $user->user_id }}" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L15.232 5.232z"></path>
                </svg>
                <svg id="save-icon-{{ $user->user_id }}" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </button>

            {{-- Cancel Button (only shown when editing) --}}
            <button onclick="cancelEdit({{ $user->user_id }})" id="cancel-btn-{{ $user->user_id }}" class="hidden relative p-2 rounded-lg transition-all duration-200 hover:bg-secondary-blue hover:text-white focus:outline-none focus:ring-2 focus:ring-secondary-blue focus:ring-opacity-50 active:scale-95 group">
                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 text-xs font-medium text-white bg-gray-900 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap pointer-events-none z-50">Cancel</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            {{-- Reset Password Button --}}
            <button onclick="resetPassword({{ $user->user_id }})" class="relative p-2 rounded-lg transition-all duration-200 hover:bg-secondary-blue hover:text-white focus:outline-none focus:ring-2 focus:ring-secondary-blue focus:ring-opacity-50 active:scale-95 group">
                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 text-xs font-medium text-white bg-gray-900 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap pointer-events-none z-50">Reset Password</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </button>

            {{-- Activate/Deactivate Button --}}
            @if($user->is_active)
                <button onclick="deactivateUser({{ $user->user_id }})" class="relative p-2 rounded-lg transition-all duration-200 hover:bg-secondary-blue hover:text-white focus:outline-none focus:ring-2 focus:ring-secondary-blue focus:ring-opacity-50 active:scale-95 group">
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 text-xs font-medium text-white bg-gray-900 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap pointer-events-none z-50">Deactivate</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </button>
            @else
                <button onclick="activateUser({{ $user->user_id }})" class="relative p-2 rounded-lg transition-all duration-200 hover:bg-secondary-blue hover:text-white focus:outline-none focus:ring-2 focus:ring-secondary-blue focus:ring-opacity-50 active:scale-95 group">
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 text-xs font-medium text-white bg-gray-900 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap pointer-events-none z-50">Activate</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </button>
            @endif

            {{-- Delete Button --}}
            <button onclick="deleteUser({{ $user->user_id }})" class="relative p-2 rounded-lg transition-all duration-200 hover:bg-secondary-blue hover:text-white focus:outline-none focus:ring-2 focus:ring-secondary-blue focus:ring-opacity-50 active:scale-95 group">
                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 text-xs font-medium text-white bg-gray-900 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap pointer-events-none z-50">Delete</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </div>
    </td>

</tr> 
