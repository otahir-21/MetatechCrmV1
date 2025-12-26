<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Accept Invitation - Metatech CRM</title>
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Accept Staff Invitation
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    You've been invited to join <strong>{{ $invitation->company->company_name }}</strong>
                </p>
                <p class="mt-1 text-center text-xs text-gray-500">
                    Role: {{ ucfirst($invitation->role) }} | Invited by: {{ $invitation->invitedBy->name ?? $invitation->invitedBy->email }}
                </p>
            </div>
            
            <form class="mt-8 space-y-6" method="POST" action="{{ route('invitation.accept.post', $invitation->token) }}">
                @csrf
                
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="first_name" class="sr-only">First Name</label>
                        <input id="first_name" name="first_name" type="text" required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="First Name" value="{{ old('first_name') }}">
                        @error('first_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="last_name" class="sr-only">Last Name</label>
                        <input id="last_name" name="last_name" type="text" required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="Last Name" value="{{ old('last_name') }}">
                        @error('last_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="Password">
                        @error('password')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters with uppercase, lowercase, number, and special character</p>
                    </div>
                    <div>
                        <label for="password_confirmation" class="sr-only">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="Confirm Password">
                        @error('password_confirmation')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if ($errors->has('error'))
                    <div class="rounded-md bg-red-50 p-4">
                        <div class="text-sm text-red-700">
                            {{ $errors->first('error') }}
                        </div>
                    </div>
                @endif

                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Accept Invitation & Create Account
                    </button>
                </div>
                
                <p class="text-xs text-center text-gray-500">
                    This invitation expires on {{ $invitation->expires_at->format('F j, Y') }}
                </p>
            </form>
        </div>
    </div>
</body>
</html>

