<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit {{ $employee->name }} - Metatech Internal CRM</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-gray-50">
<div class="min-h-screen flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900">Metatech CRM</h2>
            <p class="text-sm text-gray-600 mt-1">Internal</p>
        </div>
        <nav class="mt-4">
            <a href="{{ route('internal.dashboard') }}" class="block px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-indigo-500">Dashboard</a>
            <a href="{{ route('internal.projects.index') }}" class="block px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-indigo-500">Projects</a>
            @if(auth()->user()->canManageInternalEmployees())
                <a href="{{ route('internal.employees.index') }}" class="block px-6 py-3 text-indigo-600 bg-indigo-50 border-l-4 border-indigo-500 font-medium">Team</a>
            @endif
        </nav>
    </aside>

    <main class="flex-1">
        <header class="bg-white shadow">
            <div class="px-8 py-4 flex justify-between items-center">
                <div>
                    <a href="{{ route('internal.employees.show', $employee->id) }}" class="text-sm text-indigo-600 hover:text-indigo-800 mb-2 inline-block">← Back to Employee</a>
                    <h1 class="text-2xl font-bold text-gray-900">Edit {{ $employee->name }}</h1>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-700">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <div class="p-8">
            <div class="bg-white rounded-lg shadow p-6 max-w-3xl">
                <form method="POST" action="{{ route('internal.employees.update', $employee->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                                <input type="text" name="first_name" required 
                                       value="{{ old('first_name', $employee->first_name) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                @error('first_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                                <input type="text" name="last_name" required 
                                       value="{{ old('last_name', $employee->last_name) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                @error('last_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                            <input type="email" name="email" required 
                                   value="{{ old('email', $employee->email) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                            <select name="role" required 
                                    {{ !$canChangeRole ? 'disabled' : '' }}
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 {{ !$canChangeRole ? 'bg-gray-100' : '' }}">
                                <option value="user" {{ old('role', $employee->role) === 'user' ? 'selected' : '' }}>User</option>
                                <option value="admin" {{ old('role', $employee->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                @if($canChangeRole)
                                    <option value="super_admin" {{ old('role', $employee->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                @endif
                            </select>
                            @if(!$canChangeRole)
                                <p class="mt-1 text-xs text-gray-500">Only Super Admin can change roles</p>
                            @endif
                            @error('role')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                            <select name="department"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select Department</option>
                                <option value="Sales" {{ old('department', $employee->department) === 'Sales' ? 'selected' : '' }}>Sales</option>
                                <option value="Development" {{ old('department', $employee->department) === 'Development' ? 'selected' : '' }}>Development</option>
                                <option value="Design" {{ old('department', $employee->department) === 'Design' ? 'selected' : '' }}>Design</option>
                                <option value="Accounts" {{ old('department', $employee->department) === 'Accounts' ? 'selected' : '' }}>Accounts</option>
                                <option value="HR" {{ old('department', $employee->department) === 'HR' ? 'selected' : '' }}>HR</option>
                                <option value="Marketing" {{ old('department', $employee->department) === 'Marketing' ? 'selected' : '' }}>Marketing</option>
                                <option value="Operations" {{ old('department', $employee->department) === 'Operations' ? 'selected' : '' }}>Operations</option>
                                <option value="Support" {{ old('department', $employee->department) === 'Support' ? 'selected' : '' }}>Support</option>
                            </select>
                            @error('department')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Designation</label>
                            <input type="text" name="designation" 
                                   value="{{ old('designation', $employee->designation) }}"
                                   placeholder="e.g., Manager, Developer, Designer"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            @error('designation')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Joined Date</label>
                            <input type="date" name="joined_date" 
                                   value="{{ old('joined_date', $employee->joined_date?->format('Y-m-d')) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            @error('joined_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($canManageStatus)
                        <div class="border-t pt-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Status Management</h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Employee Status *</label>
                                <select name="status" required 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="active" {{ old('status', $employee->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="suspended" {{ old('status', $employee->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    @if(auth()->user()->isInternalSuperAdmin())
                                        <option value="blocked" {{ old('status', $employee->status) === 'blocked' ? 'selected' : '' }}>Blocked</option>
                                    @endif
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status Reason (optional)</label>
                                <textarea name="status_reason" rows="3"
                                          placeholder="Reason for status change (if suspended or blocked)"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">{{ old('status_reason', $employee->status_reason) }}</textarea>
                                @error('status_reason')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        @endif

                        <div class="flex gap-4 pt-4">
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                Save Changes
                            </button>
                            <a href="{{ route('internal.employees.show', $employee->id) }}" 
                               class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300">
                                Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>

