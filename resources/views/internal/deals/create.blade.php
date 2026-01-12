<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create New Deal - Metatech Internal CRM</title>
    
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
                <a href="{{ route('internal.dashboard') }}" class="text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create New Deal</h1>
                    <p class="text-sm text-gray-600">Add a new sales opportunity</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
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
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('internal.deals.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Deal Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Deal Title *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                           placeholder="e.g., Social Media Marketing Package">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Client Selection -->
                <div>
                    <label for="client_id" class="block text-sm font-medium text-gray-700">Client *</label>
                    <select name="client_id" id="client_id" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Select a client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                    <a href="{{ route('internal.clients.create') }}" class="mt-1 text-sm text-purple-600 hover:text-purple-800">
                        + Create new client
                    </a>
                    @error('client_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deal Value & Currency -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="value" class="block text-sm font-medium text-gray-700">Deal Value *</label>
                        <input type="number" name="value" id="value" value="{{ old('value') }}" required step="0.01" min="0"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                               placeholder="10000.00">
                        @error('value')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
                        <select name="currency" id="currency"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            <option value="AED" {{ old('currency') == 'AED' ? 'selected' : '' }}>AED</option>
                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                            <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                            <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP</option>
                        </select>
                    </div>
                </div>

                <!-- Priority & Stage -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                        <select name="priority" id="priority"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            <option value="High" {{ old('priority') == 'High' ? 'selected' : '' }}>High</option>
                            <option value="Medium" {{ old('priority', 'Medium') == 'Medium' ? 'selected' : '' }}>Medium</option>
                            <option value="Low" {{ old('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>
                    <div>
                        <label for="stage" class="block text-sm font-medium text-gray-700">Initial Stage</label>
                        <select name="stage" id="stage"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            <option value="New Lead" {{ old('stage', 'New Lead') == 'New Lead' ? 'selected' : '' }}>New Lead</option>
                            <option value="Contacted" {{ old('stage') == 'Contacted' ? 'selected' : '' }}>Contacted</option>
                            <option value="Qualified" {{ old('stage') == 'Qualified' ? 'selected' : '' }}>Qualified</option>
                        </select>
                    </div>
                </div>

                <!-- Assigned To -->
                <div>
                    <label for="assigned_to" class="block text-sm font-medium text-gray-700">Assign To Sales Agent *</label>
                    @if($salesUsers->count() > 0)
                        <select name="assigned_to" id="assigned_to" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Select sales agent</option>
                            @foreach($salesUsers as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to', auth()->id()) == $user->id ? 'selected' : '' }}>
                                    {{ $user->first_name }} {{ $user->last_name }} 
                                    @if($user->designation)
                                        - {{ $user->designation }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    @else
                        <div class="mt-1 block w-full px-3 py-2 border border-yellow-300 bg-yellow-50 rounded-md">
                            <p class="text-sm text-yellow-800">⚠️ No sales agents available. Please add a sales team member first.</p>
                            <a href="{{ route('internal.dashboard') }}" class="text-sm text-purple-600 hover:text-purple-800 font-medium">
                                → Go to Dashboard to Add Employee
                            </a>
                        </div>
                    @endif
                    @error('assigned_to')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Expected Close Date -->
                <div>
                    <label for="expected_close_date" class="block text-sm font-medium text-gray-700">Expected Close Date</label>
                    <input type="date" name="expected_close_date" id="expected_close_date" value="{{ old('expected_close_date') }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>

                <!-- Contact Person Details -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Person (Optional)</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="contact_person_name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="contact_person_name" id="contact_person_name" value="{{ old('contact_person_name') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="Contact person name">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="contact_person_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                <input type="text" name="contact_person_phone" id="contact_person_phone" value="{{ old('contact_person_phone') }}"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                                       placeholder="+971 50 123 4567">
                            </div>
                            <div>
                                <label for="contact_person_email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="contact_person_email" id="contact_person_email" value="{{ old('contact_person_email') }}"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                                       placeholder="contact@company.com">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lead Source -->
                <div>
                    <label for="lead_source" class="block text-sm font-medium text-gray-700">Lead Source</label>
                    <input type="text" name="lead_source" id="lead_source" value="{{ old('lead_source') }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                           placeholder="e.g., Website, Referral, Cold Call">
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" id="notes" rows="4"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                              placeholder="Any additional notes about this deal...">{{ old('notes') }}</textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-6 border-t">
                    <a href="{{ route('internal.deals.index') }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                        Create Deal
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>

