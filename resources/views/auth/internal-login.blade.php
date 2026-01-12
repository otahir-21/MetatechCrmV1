@extends('bootstrap.layout')

@section('content')
<div class="bg-white py-8 px-6 shadow rounded-lg">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 text-center">Metatech Internal CRM</h2>
        <p class="mt-2 text-sm text-gray-600 text-center">
            Employee Login
        </p>
    </div>

    <form id="loginForm" method="POST" action="{{ route('login.post') }}" class="space-y-6">
        @csrf
        
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
                Email Address
            </label>
            <input type="email" id="email" name="email" required autofocus
                class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                placeholder="employee@metatech.ae">
            <p class="mt-1 text-xs text-red-500" id="email-error"></p>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
                Password
            </label>
            <input type="password" id="password" name="password" required
                class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                placeholder="Enter your password">
            <p class="mt-1 text-xs text-red-500" id="password-error"></p>
        </div>

        <div>
            <button type="submit" id="submitBtn"
                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span id="submitText">Login</span>
                <span id="submitSpinner" class="hidden">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </div>

        <div class="text-center">
            <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                Forgot your password?
            </a>
        </div>
    </form>
</div>

@if ($errors->any())
<div class="mb-4 rounded-md bg-red-50 p-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">
                There were errors with your submission:
            </h3>
            <div class="mt-2 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if ($errors->has('email'))
        var emailInput = document.getElementById('email');
        var emailError = document.getElementById('email-error');
        if (emailInput) emailInput.classList.add('border-red-500');
        if (emailError) emailError.textContent = {!! json_encode($errors->first('email')) !!};
    @endif
    
    @if ($errors->has('password'))
        var passwordInput = document.getElementById('password');
        var passwordError = document.getElementById('password-error');
        if (passwordInput) passwordInput.classList.add('border-red-500');
        if (passwordError) passwordError.textContent = {!! json_encode($errors->first('password')) !!};
    @endif
});
</script>
@endif

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');
    
    // Clear previous errors
    document.querySelectorAll('[id$="-error"]').forEach(el => {
        el.textContent = '';
    });
    document.querySelectorAll('input').forEach(el => {
        el.classList.remove('border-red-500');
    });
    
    // Disable button and show spinner
    submitBtn.disabled = true;
    submitText.classList.add('hidden');
    submitSpinner.classList.remove('hidden');
    
    // Form will submit normally, allowing Laravel to handle it
});
</script>
@endsection

