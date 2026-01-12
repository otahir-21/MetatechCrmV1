@extends('bootstrap.layout')

@section('content')
<div class="bg-white py-8 px-6 shadow rounded-lg">
    <div class="mb-6 text-center">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
            <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Bootstrap Complete!</h2>
        <p class="mt-2 text-sm text-gray-600">
            Your system has been successfully initialized and is ready to use.
        </p>
    </div>

    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">
                    <strong>System Status:</strong> {{ $status['status'] }}<br>
                    <strong>Confirmed At:</strong> {{ $status['confirmed_at'] ? date('Y-m-d H:i:s', strtotime($status['confirmed_at'])) : 'N/A' }}
                </p>
            </div>
        </div>
    </div>

    <div class="border-t border-gray-200 pt-4">
        <dl class="space-y-2">
            <div class="flex justify-between">
                <dt class="text-sm font-medium text-gray-500">Super Admin Email:</dt>
                <dd class="text-sm text-gray-900">{{ $status['super_admin_email'] ?? 'N/A' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-sm font-medium text-gray-500">Created At:</dt>
                <dd class="text-sm text-gray-900">{{ $status['created_at'] ? date('Y-m-d H:i:s', strtotime($status['created_at'])) : 'N/A' }}</dd>
            </div>
        </dl>
    </div>

    <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
            You can now proceed to use the application normally.
        </p>
    </div>
</div>
@endsection

