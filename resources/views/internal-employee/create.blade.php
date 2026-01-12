@extends('bootstrap.layout')

@section('content')
<div class="bg-white py-8 px-6 shadow rounded-lg">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Create Internal Employee</h2>
        <p class="mt-2 text-sm text-gray-600">
            Create credentials for Metatech internal CRM employee (crm.metatech.ae)
        </p>
    </div>

    <form id="createForm" class="space-y-6">
        @csrf
        
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
                Email Address *
            </label>
            <input type="email" id="email" name="email" required
                class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                placeholder="employee@metatech.ae">
            <p class="mt-1 text-xs text-red-500" id="email-error"></p>
        </div>

        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700">
                First Name *
            </label>
            <input type="text" id="first_name" name="first_name" required
                class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                placeholder="John">
            <p class="mt-1 text-xs text-red-500" id="first_name-error"></p>
        </div>

        <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700">
                Last Name *
            </label>
            <input type="text" id="last_name" name="last_name" required
                class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                placeholder="Doe">
            <p class="mt-1 text-xs text-red-500" id="last_name-error"></p>
        </div>

        <div>
            <label for="role" class="block text-sm font-medium text-gray-700">
                Role *
            </label>
            <select id="role" name="role" required
                class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm">
                <option value="">Select Role</option>
                <option value="user">User</option>
                <option value="admin">Admin</option>
                <option value="super_admin">Super Admin</option>
            </select>
            <p class="mt-1 text-xs text-gray-500">Role for internal CRM access</p>
            <p class="mt-1 text-xs text-red-500" id="role-error"></p>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
                Password *
            </label>
            <input type="password" id="password" name="password" required
                class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                placeholder="SecurePass123!">
            <p class="mt-1 text-xs text-gray-500">
                Must be at least 8 characters with uppercase, lowercase, number, and special character
            </p>
            <p class="mt-1 text-xs text-red-500" id="password-error"></p>
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                Confirm Password *
            </label>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                placeholder="SecurePass123!">
            <p class="mt-1 text-xs text-red-500" id="password_confirmation-error"></p>
        </div>

        <div class="flex space-x-4">
            <a href="/dashboard" 
               class="flex-1 flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
            </a>
            <button type="submit" id="submitBtn"
                class="flex-1 flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span id="submitText">Create Internal Employee</span>
                <span id="submitSpinner" class="hidden">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </div>
    </form>
</div>

<script>
const token = localStorage.getItem('auth_token');

if (!token) {
    window.location.href = '/login';
}

document.getElementById('createForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');
    
    // Clear previous errors
    document.querySelectorAll('[id$="-error"]').forEach(el => {
        el.textContent = '';
    });
    document.querySelectorAll('input, select').forEach(el => {
        el.classList.remove('border-red-500');
    });
    
    // Disable button and show spinner
    submitBtn.disabled = true;
    submitText.classList.add('hidden');
    submitSpinner.classList.remove('hidden');
    
    const formData = {
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
        password_confirmation: document.getElementById('password_confirmation').value,
        first_name: document.getElementById('first_name').value,
        last_name: document.getElementById('last_name').value,
        role: document.getElementById('role').value
    };
    
    try {
        const response = await fetch('/api/v1/internal-employee/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token,
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (response.ok) {
            alert('Internal Employee created successfully!');
            window.location.href = '/dashboard';
        } else {
            // Handle errors
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const errorElement = document.getElementById(field + '-error');
                    const inputElement = document.getElementById(field);
                    if (errorElement && inputElement) {
                        errorElement.textContent = data.errors[field][0];
                        errorElement.classList.add('text-red-500');
                        inputElement.classList.add('border-red-500');
                    }
                });
            } else {
                alert(data.message || 'An error occurred');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    } finally {
        // Re-enable button
        submitBtn.disabled = false;
        submitText.classList.remove('hidden');
        submitSpinner.classList.add('hidden');
    }
});
</script>
@endsection

