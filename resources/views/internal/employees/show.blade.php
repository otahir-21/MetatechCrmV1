<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $employee->name }} - Metatech Internal CRM</title>
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
                    <a href="{{ route('internal.employees.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 mb-2 inline-block">← Back to Employees</a>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $employee->name }}</h1>
                </div>
                <div class="flex items-center gap-4">
                    @if($canEdit)
                        <a href="{{ route('internal.employees.edit', $employee->id) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Edit</a>
                    @endif
                    <span class="text-sm text-gray-700">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <div class="p-8">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Name</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $employee->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                        <p class="text-lg text-gray-900">{{ $employee->email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Department</label>
                        <p class="text-lg text-gray-900">{{ $employee->department ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Designation</label>
                        <p class="text-lg text-gray-900">{{ $employee->designation ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Role</label>
                        <span class="inline-block px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst(str_replace('_', ' ', $employee->role)) }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        <span class="inline-block px-3 py-1 text-sm rounded-full 
                            @if($employee->status === 'active') bg-green-100 text-green-800
                            @elseif($employee->status === 'suspended') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($employee->status ?? 'active') }}
                        </span>
                    </div>
                    @if($employee->joined_date)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Joined Date</label>
                        <p class="text-lg text-gray-900">{{ $employee->joined_date->format('M d, Y') }}</p>
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Member Since</label>
                        <p class="text-lg text-gray-900">{{ $employee->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>

