<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Suspended - Metatech Internal CRM</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-gray-50">
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mb-6">
            <svg class="mx-auto h-16 w-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Account Suspended</h1>
        <p class="text-gray-600 mb-6">
            Your account has been suspended. Please contact your administrator for more information.
        </p>
        @if(auth()->user() && auth()->user()->status_reason)
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6 text-left">
                <p class="text-sm text-yellow-800">
                    <strong>Reason:</strong> {{ auth()->user()->status_reason }}
                </p>
            </div>
        @endif
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                Logout
            </button>
        </form>
    </div>
</div>
</body>
</html>

