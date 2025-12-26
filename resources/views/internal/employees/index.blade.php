<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employees - Metatech Internal CRM</title>
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
            @if(auth()->user()->canManageSettings())
                <a href="#" class="block px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-indigo-500">Settings</a>
            @endif
        </nav>
    </aside>

    <main class="flex-1">
        <header class="bg-white shadow">
            <div class="px-8 py-4 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Employees</h1>
                    <p class="text-sm text-gray-600 mt-1">Manage internal team members</p>
                </div>
                <div class="flex items-center gap-4">
                    <button onclick="showCreateEmployeeModal()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">+ Add Employee</button>
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

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <form method="GET" action="{{ route('internal.employees.index') }}" class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="all" {{ $currentStatus === 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ $currentStatus === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="blocked" {{ $currentStatus === 'blocked' ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <select name="department" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="all" {{ $currentDepartment === 'all' ? 'selected' : '' }}>All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ $currentDepartment === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Filter</button>
                        <a href="{{ route('internal.employees.index') }}" class="ml-2 bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300">Clear</a>
                    </div>
                </form>
            </div>

            <!-- Employees Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Designation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($employees as $employee)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $employee->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $employee->department ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $employee->designation ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                        {{ ucfirst(str_replace('_', ' ', $employee->role)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        @if($employee->status === 'active') bg-green-100 text-green-800
                                        @elseif($employee->status === 'suspended') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($employee->status ?? 'active') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('internal.employees.show', $employee->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                    @if($canManageStatus)
                                        <a href="{{ route('internal.employees.edit', $employee->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">No employees found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-sm text-gray-600">
                Total: {{ $employees->count() }} {{ Str::plural('employee', $employees->count()) }}
            </div>
        </div>
    </main>
</div>

<!-- Create Employee Modal (same as dashboard) -->
<script>
function showCreateEmployeeModal() {
    window.location.href = '{{ route("internal.dashboard") }}';
}
</script>
</body>
</html>

