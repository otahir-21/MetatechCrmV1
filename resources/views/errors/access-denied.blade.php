<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Metatech CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
        <div class="text-center">
            <div class="mb-4">
                <svg class="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Access Denied</h1>
            <p class="text-gray-600 mb-4">{{ $message ?? 'You are not authorized to access this system.' }}</p>
            @if(isset($allowed_url))
                <p class="text-sm text-gray-500 mb-6">Please login at: <strong class="text-indigo-600">{{ $allowed_url }}</strong></p>
            @endif
            <a href="/login" class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                Go to Login
            </a>
        </div>
    </div>
</body>
</html>

