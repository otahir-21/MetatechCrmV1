<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $deal->title }} - Metatech Internal CRM</title>
    
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
                <a href="{{ route('internal.deals.index') }}" class="text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Deal Details</h1>
                    <p class="text-sm text-gray-600">{{ $deal->title }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('internal.deals.edit', $deal) }}" 
                   class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                    Edit Deal
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
            <!-- Main Deal Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Deal Overview -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Deal Overview</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Title</p>
                            <p class="text-lg font-medium text-gray-900">{{ $deal->title }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Value</p>
                            <p class="text-lg font-medium text-gray-900">{{ $deal->currency }} {{ number_format($deal->value, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Stage</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($deal->stage == 'won') bg-green-100 text-green-800
                                @elseif($deal->stage == 'lost') bg-red-100 text-red-800
                                @else bg-blue-100 text-blue-800
                                @endif">
                                {{ ucwords(str_replace('_', ' ', $deal->stage)) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Priority</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($deal->priority == 'high') bg-red-100 text-red-800
                                @elseif($deal->priority == 'medium') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($deal->priority) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Client Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Client Information</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Client Name</p>
                            <p class="text-base font-medium text-gray-900">{{ $deal->client->name }}</p>
                        </div>
                        @if($deal->client->contact_person)
                        <div>
                            <p class="text-sm text-gray-600">Contact Person</p>
                            <p class="text-base text-gray-900">{{ $deal->client->contact_person }}</p>
                        </div>
                        @endif
                        @if($deal->client->phone)
                        <div>
                            <p class="text-sm text-gray-600">Phone</p>
                            <p class="text-base text-gray-900">{{ $deal->client->phone }}</p>
                        </div>
                        @endif
                        @if($deal->client->email)
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="text-base text-gray-900">{{ $deal->client->email }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Deal Contact Person -->
                @if($deal->contact_person_name || $deal->contact_person_phone || $deal->contact_person_email)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Deal Contact Person</h2>
                    <div class="space-y-3">
                        @if($deal->contact_person_name)
                        <div>
                            <p class="text-sm text-gray-600">Name</p>
                            <p class="text-base text-gray-900">{{ $deal->contact_person_name }}</p>
                        </div>
                        @endif
                        @if($deal->contact_person_phone)
                        <div>
                            <p class="text-sm text-gray-600">Phone</p>
                            <p class="text-base text-gray-900">{{ $deal->contact_person_phone }}</p>
                        </div>
                        @endif
                        @if($deal->contact_person_email)
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="text-base text-gray-900">{{ $deal->contact_person_email }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Notes -->
                @if($deal->notes)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Notes</h2>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $deal->notes }}</p>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Assignment Info -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Assignment</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Assigned To</p>
                            <p class="text-base font-medium text-gray-900">{{ $deal->assignedUser->first_name }} {{ $deal->assignedUser->last_name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Dates -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Dates</h3>
                    <div class="space-y-3">
                        @if($deal->expected_close_date)
                        <div>
                            <p class="text-sm text-gray-600">Expected Close Date</p>
                            <p class="text-base text-gray-900">{{ $deal->expected_close_date->format('M d, Y') }}</p>
                        </div>
                        @endif
                        <div>
                            <p class="text-sm text-gray-600">Created</p>
                            <p class="text-base text-gray-900">{{ $deal->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Last Updated</p>
                            <p class="text-base text-gray-900">{{ $deal->updated_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Info</h3>
                    <div class="space-y-3">
                        @if($deal->lead_source)
                        <div>
                            <p class="text-sm text-gray-600">Lead Source</p>
                            <p class="text-base text-gray-900">{{ $deal->lead_source }}</p>
                        </div>
                        @endif
                        <div>
                            <p class="text-sm text-gray-600">Deal ID</p>
                            <p class="text-base text-gray-900">#{{ $deal->id }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('internal.deals.edit', $deal) }}" 
                           class="w-full block text-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                            Edit Deal
                        </a>
                        <form action="{{ route('internal.deals.destroy', $deal) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this deal?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                Delete Deal
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

