<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Metatech Internal CRM - Dashboard</title>
    
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
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Metatech Internal CRM</h1>
                    <p class="text-sm text-gray-600">Employee Dashboard</p>
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
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Welcome, {{ auth()->user()->first_name ?? auth()->user()->name }}!</h2>
            <p class="text-gray-600 mb-6">This is the internal Metatech CRM dashboard for employees.</p>
            
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-indigo-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-indigo-800">System Status</div>
                    <div class="mt-2 text-2xl font-bold text-indigo-900">Active</div>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-green-800">Your Role</div>
                    <div class="mt-2 text-2xl font-bold text-green-900">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</div>
                </div>
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-blue-800">Email</div>
                    <div class="mt-2 text-sm font-bold text-blue-900">{{ auth()->user()->email }}</div>
                </div>
            </div>

            <!-- Quick Actions Section -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Projects -->
                    <div class="border border-gray-200 rounded-lg p-6 hover:border-indigo-300 hover:shadow-md transition-all">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-lg font-semibold text-gray-900">Projects</h4>
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Manage your projects and tasks</p>
                        <a href="{{ route('internal.projects.index') }}" 
                           class="w-full block text-center bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                            View Projects
                        </a>
                    </div>

                    <!-- Sales Pipeline -->
                    <div class="border border-gray-200 rounded-lg p-6 hover:border-purple-300 hover:shadow-md transition-all">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-lg font-semibold text-gray-900">Sales Pipeline</h4>
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Track leads and deals with Kanban board</p>
                        <a href="{{ route('internal.deals.index') }}" 
                           class="w-full block text-center bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition-colors">
                            View Pipeline
                        </a>
                    </div>

                    <!-- Clients -->
                    <div class="border border-gray-200 rounded-lg p-6 hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-lg font-semibold text-gray-900">Clients</h4>
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Manage your digital marketing clients</p>
                        <a href="{{ route('internal.clients.index') }}" 
                           class="w-full block text-center bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            View Clients
                        </a>
                    </div>

                    <!-- Manage Internal Employees -->
                    @if(auth()->user()->canManageInternalEmployees())
                    <div class="border border-gray-200 rounded-lg p-6 hover:border-indigo-300 hover:shadow-md transition-all">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-lg font-semibold text-gray-900">Internal Employees</h4>
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Manage Metatech internal team members</p>
                        <button onclick="viewInternalEmployees()" 
                                class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                            Manage Employees
                        </button>
                    </div>
                    @endif

                    <!-- Create Internal Employee -->
                    @if(auth()->user()->canManageInternalEmployees())
                    <div class="border border-gray-200 rounded-lg p-6 hover:border-indigo-300 hover:shadow-md transition-all">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-lg font-semibold text-gray-900">Add Employee</h4>
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Invite a new Metatech employee</p>
                        <button onclick="showCreateEmployeeModal()" 
                                class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                            Add Employee
                        </button>
                    </div>
                    @endif

                    <!-- System Statistics -->
                    <div class="border border-gray-200 rounded-lg p-6 hover:border-indigo-300 hover:shadow-md transition-all">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-lg font-semibold text-gray-900">Statistics</h4>
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">View system-wide statistics and reports</p>
                        <button onclick="viewStatistics()" 
                                class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                            View Stats
                        </button>
                    </div>

                    <!-- User Management (Super Admin only) -->
                    @if(auth()->user()->canManageAllUsers())
                    <div class="border border-gray-200 rounded-lg p-6 hover:border-indigo-300 hover:shadow-md transition-all">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-lg font-semibold text-gray-900">User Management</h4>
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Block/unblock users and companies</p>
                        <button onclick="viewUserManagement()" 
                                class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                            Manage Users
                        </button>
                    </div>
                    @endif

                    <!-- System Settings -->
                    @if(auth()->user()->canManageSettings())
                    <div class="border border-gray-200 rounded-lg p-6 hover:border-indigo-300 hover:shadow-md transition-all">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-lg font-semibold text-gray-900">Settings</h4>
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Configure system settings and preferences</p>
                        <button onclick="viewSettings()" 
                                class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors">
                            Open Settings
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Create Employee Modal -->
<div id="createEmployeeModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Send Employee Invitation</h3>
            <p class="text-sm text-gray-600 mb-4">An invitation email will be sent to the employee. They will set their password when accepting the invitation.</p>
            <form id="createEmployeeForm" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">First Name *</label>
                        <input type="text" id="employeeFirstName" name="first_name" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Name *</label>
                        <input type="text" id="employeeLastName" name="last_name" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email *</label>
                    <input type="email" id="employeeEmail" name="email" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Role *</label>
                    <select id="employeeRole" name="role" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <optgroup label="Sales & Marketing">
                            <option value="metatech.sales">Sales Agent</option>
                            <option value="metatech.marketing">Marketing Specialist</option>
                        </optgroup>
                        <optgroup label="Development & Design">
                            <option value="metatech.development">Developer</option>
                            <option value="metatech.design">Designer</option>
                        </optgroup>
                        <optgroup label="Operations">
                            <option value="metatech.accounts">Accounts/Finance</option>
                            <option value="metatech.hr">HR</option>
                            <option value="metatech.executive">Executive</option>
                        </optgroup>
                        <optgroup label="Administrative">
                            <option value="metatech.admin">Admin</option>
                            @if(auth()->user() && auth()->user()->isInternalSuperAdmin())
                                <option value="metatech.super_admin">Super Admin</option>
                            @endif
                        </optgroup>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Select role based on department and responsibilities</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Department</label>
                    <select id="employeeDepartment" name="department"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Department</option>
                        <option value="Sales">Sales</option>
                        <option value="Development">Development</option>
                        <option value="Design">Design</option>
                        <option value="Accounts">Accounts</option>
                        <option value="HR">HR</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Operations">Operations</option>
                        <option value="Support">Support</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Designation</label>
                    <input type="text" id="employeeDesignation" name="designation" placeholder="e.g., Manager, Developer, Designer"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Joined Date</label>
                    <input type="date" id="employeeJoinedDate" name="joined_date"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeCreateEmployeeModal()" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Send Invitation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const token = '{{ $api_token ?? session("api_token") ?? "" }}';
const apiBase = '/api/v1';

// Navigation Functions
function viewInternalEmployees() {
    // Redirect to employees view or open modal
    window.location.href = '/internal/employees';
}

function viewStatistics() {
    alert('Statistics view coming soon!');
    // TODO: Implement statistics view
}

function viewUserManagement() {
    // Redirect to user management view
    window.location.href = '/internal/user-management';
}

function viewSettings() {
    alert('Settings view coming soon!');
    // TODO: Implement settings view
}

// Create Employee Modal
function showCreateEmployeeModal() {
    document.getElementById('createEmployeeModal').classList.remove('hidden');
    setupCreateEmployeeForm();
}

function closeCreateEmployeeModal() {
    document.getElementById('createEmployeeModal').classList.add('hidden');
    document.getElementById('createEmployeeForm').reset();
}

function setupCreateEmployeeForm() {
    const form = document.getElementById('createEmployeeForm');
    form.onsubmit = async (e) => {
        e.preventDefault();
        
        const data = {
            first_name: document.getElementById('employeeFirstName').value,
            last_name: document.getElementById('employeeLastName').value,
            email: document.getElementById('employeeEmail').value,
            role: document.getElementById('employeeRole').value,
            department: document.getElementById('employeeDepartment').value || null,
            designation: document.getElementById('employeeDesignation').value || null,
            joined_date: document.getElementById('employeeJoinedDate').value || null,
        };
        
        try {
            const response = await fetch(`${apiBase}/internal-employee/invite`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (response.ok) {
                alert('Invitation sent successfully! The employee will receive an email to set up their account.');
                closeCreateEmployeeModal();
                // Reload page to refresh invitation list
                window.location.reload();
            } else {
                // Show validation errors if any
                if (result.errors) {
                    const errorMessages = Object.values(result.errors).flat().join('\n');
                    alert('Validation errors:\n' + errorMessages);
                } else {
                    alert(result.message || 'Error sending invitation');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error sending invitation');
        }
    };
}
</script>
</body>
</html>
