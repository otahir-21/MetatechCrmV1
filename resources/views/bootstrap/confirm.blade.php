@extends('bootstrap.layout')

@section('content')
<div class="bg-white py-8 px-6 shadow rounded-lg">
    <div class="mb-6">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
            <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 text-center">Metatech CRM - Bootstrap Confirmation Required</h2>
        <p class="mt-2 text-sm text-gray-600 text-center">
            Super Admin has been created. Please login and confirm bootstrap completion to activate the system.
        </p>
    </div>

    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Next Step:</strong> You need to login with your Super Admin credentials and confirm bootstrap completion using the API endpoint or admin panel.
                </p>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="border-t border-gray-200 pt-4">
            <dl class="space-y-2">
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Super Admin Email:</dt>
                    <dd class="text-sm text-gray-900">{{ $status['super_admin_email'] ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Status:</dt>
                    <dd class="text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            {{ $status['status'] }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="mt-6">
            <p class="text-xs text-gray-500 text-center">
                To confirm bootstrap, use the API endpoint:<br>
                <code class="bg-gray-100 px-2 py-1 rounded">POST /api/v1/bootstrap/confirm</code><br>
                with JWT authentication token.
            </p>
        </div>
    </div>
</div>
@endsection

