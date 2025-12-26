<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Project - Metatech Internal CRM</title>
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
            <a href="{{ route('internal.projects.index') }}" class="block px-6 py-3 text-indigo-600 bg-indigo-50 border-l-4 border-indigo-500 font-medium">Projects</a>
        </nav>
    </aside>

    <main class="flex-1">
        <header class="bg-white shadow">
            <div class="px-8 py-4">
                <h1 class="text-2xl font-bold text-gray-900">Create New Project</h1>
            </div>
        </header>

        <div class="p-8">
            <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
                <form method="POST" action="{{ route('internal.projects.store') }}">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Project Name *</label>
                        <input type="text" name="name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('name') }}">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" 
                                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Create Project
                        </button>
                        <a href="{{ route('internal.projects.index') }}" 
                           class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>

