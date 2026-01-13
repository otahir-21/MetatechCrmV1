<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $client->name }} - Metatech Internal CRM</title>
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-gray-50">
<div class="min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('internal.clients.index') }}" class="text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Client Details</h1>
                    <p class="text-sm text-gray-600">{{ $client->name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('internal.clients.edit', $client) }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Edit Client
                </a>
                <span class="text-sm text-gray-700">{{ auth()->user()->name ?? auth()->user()->email }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Client Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Client Overview -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Client Information</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Client Name</p>
                            <p class="text-lg font-medium text-gray-900">{{ $client->name }}</p>
                        </div>
                        @if($client->contact_person)
                        <div>
                            <p class="text-sm text-gray-600">Contact Person</p>
                            <p class="text-base text-gray-900">{{ $client->contact_person }}</p>
                        </div>
                        @endif
                        @if($client->phone)
                        <div>
                            <p class="text-sm text-gray-600">Phone</p>
                            <p class="text-base text-gray-900">{{ $client->phone }}</p>
                        </div>
                        @endif
                        @if($client->email)
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="text-base text-gray-900">{{ $client->email }}</p>
                        </div>
                        @endif
                        @if($client->address)
                        <div>
                            <p class="text-sm text-gray-600">Address</p>
                            <p class="text-base text-gray-900">{{ $client->address }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Deals List -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">Deals ({{ $client->deals->count() }})</h2>
                        <a href="{{ route('internal.deals.create', ['client_id' => $client->id]) }}" 
                           class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm">
                            + New Deal
                        </a>
                    </div>
                    
                    @if($client->deals->count() > 0)
                    <div class="space-y-3">
                        @foreach($client->deals as $deal)
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-purple-300 transition-all">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ $deal->title }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ $deal->currency }} {{ number_format($deal->value, 2) }}</p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($deal->stage == 'Won') bg-green-100 text-green-800
                                            @elseif($deal->stage == 'Lost') bg-red-100 text-red-800
                                            @else bg-blue-100 text-blue-800
                                            @endif">
                                            {{ $deal->stage }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($deal->priority == 'High') bg-red-100 text-red-800
                                            @elseif($deal->priority == 'Medium') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $deal->priority }}
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('internal.deals.show', $deal) }}" 
                                   class="text-purple-600 hover:text-purple-900 text-sm font-medium">
                                    View
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p class="mt-2">No deals yet for this client</p>
                        <a href="{{ route('internal.deals.create', ['client_id' => $client->id]) }}" 
                           class="mt-4 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                            Create First Deal
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Statistics -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Total Deals</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $client->deals->count() }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Value</p>
                            <p class="text-2xl font-bold text-gray-900">AED {{ number_format($client->deals->sum('value'), 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Won Deals</p>
                            <p class="text-2xl font-bold text-green-600">{{ $client->deals->where('stage', 'Won')->count() }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Active Deals</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $client->deals->whereNotIn('stage', ['Won', 'Lost'])->count() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Metadata -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Metadata</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Created By</p>
                            <p class="text-base text-gray-900">{{ $client->creator->first_name }} {{ $client->creator->last_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Created</p>
                            <p class="text-base text-gray-900">{{ $client->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Last Updated</p>
                            <p class="text-base text-gray-900">{{ $client->updated_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Client ID</p>
                            <p class="text-base text-gray-900">#{{ $client->id }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('internal.clients.edit', $client) }}" 
                           class="w-full block text-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Edit Client
                        </a>
                        <a href="{{ route('internal.deals.create', ['client_id' => $client->id]) }}" 
                           class="w-full block text-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                            Create Deal
                        </a>
                        <form action="{{ route('internal.clients.destroy', $client) }}" method="POST" 
                              onsubmit="return confirm('Are you sure? This will also delete all deals for this client.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                Delete Client
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>

