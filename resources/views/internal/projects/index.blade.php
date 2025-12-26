<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Projects - Metatech Internal CRM</title>
    
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
            <a href="{{ route('internal.dashboard') }}" 
               class="block px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-indigo-500">
                Dashboard
            </a>
            <a href="{{ route('internal.projects.index') }}" 
               class="block px-6 py-3 text-indigo-600 bg-indigo-50 border-l-4 border-indigo-500 font-medium">
                Projects
            </a>
            @if(auth()->user()->canManageInternalEmployees())
                <a href="#" 
                   class="block px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-indigo-500">
                    Team
                </a>
            @endif
            @if(auth()->user()->canManageSettings())
                <a href="#" 
                   class="block px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-indigo-500">
                    Settings
                </a>
            @endif
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="px-8 py-4 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Projects</h1>
                    <p class="text-sm text-gray-600 mt-1">Manage your projects and tasks</p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-700">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="p-8">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ $canViewAll ? 'All Projects' : 'My Projects' }}
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $projects->count() }} {{ Str::plural('project', $projects->count()) }}
                    </p>
                </div>
                @if($canCreate)
                    <a href="{{ route('internal.projects.create') }}" 
                       class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                        + New Project
                    </a>
                @endif
            </div>

            @if($projects->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($projects as $project)
                        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $project->name }}</h3>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($project->status === 'active') bg-green-100 text-green-800
                                    @elseif($project->status === 'completed') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </div>
                            
                            @if($project->description)
                                <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $project->description }}</p>
                            @endif

                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <span>👤 {{ $project->users->count() }} members</span>
                                <span>📋 {{ $project->tasks->count() }} tasks</span>
                            </div>

                            <div class="flex gap-2">
                                <a href="{{ route('internal.projects.show', $project->id) }}" 
                                   class="flex-1 text-center bg-indigo-50 text-indigo-600 px-4 py-2 rounded-md hover:bg-indigo-100 transition-colors">
                                    View
                                </a>
                                @if($canViewAll || $project->created_by === auth()->id())
                                    <a href="{{ route('internal.projects.edit', $project->id) }}" 
                                       class="flex-1 text-center bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 transition-colors">
                                        Edit
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No projects found</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        @if($canCreate)
                            Get started by creating your first project.
                        @else
                            You haven't been assigned to any projects yet.
                        @endif
                    </p>
                    @if($canCreate)
                        <div class="mt-6">
                            <a href="{{ route('internal.projects.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                + New Project
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </main>
</div>
</body>
</html>

