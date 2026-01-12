@extends('bootstrap.layout')

@section('content')
<div class="bg-white py-8 px-6 shadow rounded-lg">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Generate CRM - Create Company</h2>
        <p class="mt-2 text-sm text-gray-600">
            Create a new company. The Company Owner will receive an invitation email to set their password and activate their account.
        </p>
    </div>

    <form id="createForm" class="space-y-6">
        @csrf
        
        <div>
            <label for="company_name" class="block text-sm font-medium text-gray-700">
                Company Name *
            </label>
            <input type="text" id="company_name" name="company_name" required
                class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                placeholder="Metatech Solutions">
            <p class="mt-1 text-xs text-gray-500">Company name must be unique</p>
            <p class="mt-1 text-xs text-red-500" id="company_name-error"></p>
        </div>

        <div>
            <label for="subdomain" class="block text-sm font-medium text-gray-700">
                Subdomain *
            </label>
            <div class="mt-1 flex rounded-md shadow-sm">
                <input type="text" id="subdomain" name="subdomain" required
                    class="appearance-none relative block flex-1 px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-l-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                    placeholder="acme"
                    pattern="[a-z0-9-]+"
                    oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '')">
                <span class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm rounded-r-md">
                    .crm.metatech.ae
                </span>
            </div>
            <p class="mt-1 text-xs text-gray-500">Only lowercase letters, numbers, and hyphens. This will be the login URL for this company.</p>
            <p class="mt-1 text-xs text-red-500" id="subdomain-error"></p>
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
                Email Address
            </label>
            <input type="email" id="email" name="email" required
                class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                placeholder="admin@company.com">
            <p class="mt-1 text-xs text-red-500" id="email-error"></p>
        </div>

        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700">
                First Name
            </label>
            <input type="text" id="first_name" name="first_name" required
                class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                placeholder="John">
            <p class="mt-1 text-xs text-red-500" id="first_name-error"></p>
        </div>

        <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700">
                Last Name
            </label>
            <input type="text" id="last_name" name="last_name" required
                class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                placeholder="Doe">
            <p class="mt-1 text-xs text-red-500" id="last_name-error"></p>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
            <p class="text-sm text-blue-800">
                <strong>Note:</strong> The Company Owner will receive an invitation email to set their password and activate their account. They will be able to login at <strong>{subdomain}.crm.metatech.ae</strong> (Client portal) once they activate their account.
            </p>
        </div>

        <div class="flex space-x-4">
            <a href="/dashboard" 
               class="flex-1 flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
            </a>
            <button type="submit" id="submitBtn"
                class="flex-1 flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span id="submitText">Create Company & Send Invitation</span>
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
        el.previousElementSibling?.classList.remove('border-red-500');
    });
    
    // Disable button and show spinner
    submitBtn.disabled = true;
    submitText.classList.add('hidden');
    submitSpinner.classList.remove('hidden');
    
    const formData = {
        email: document.getElementById('email').value,
        first_name: document.getElementById('first_name').value,
        last_name: document.getElementById('last_name').value,
        company_name: document.getElementById('company_name').value,
        subdomain: document.getElementById('subdomain').value.toLowerCase().replace(/[^a-z0-9-]/g, '')
    };
    
    try {
        const response = await fetch('/api/v1/company/create', {
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
            alert('Company created successfully! An invitation email has been sent to the Company Owner.');
            // Redirect to dashboard - the list will auto-refresh
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

