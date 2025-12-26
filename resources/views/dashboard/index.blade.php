<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Metatech CRM - Dashboard</title>
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-gray-50">
<div class="flex h-screen bg-gray-50">
    <!-- Left Sidebar - Company List -->
    <div class="w-80 bg-white border-r border-gray-200 overflow-y-auto">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Companies</h2>
            <button id="refreshCompanies" class="mt-2 text-sm text-indigo-600 hover:text-indigo-800">
                Refresh
            </button>
        </div>
        
        <!-- Action Buttons -->
        <div class="p-4 border-b border-gray-200 space-y-2">
            <a href="/company/create" 
               class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Generate CRM
            </a>
            <a href="/internal-employee/create" 
               class="w-full flex justify-center py-2 px-4 border border-indigo-300 rounded-md shadow-sm text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Create Internal Employee
            </a>
            <a href="{{ route('audit-logs.index') }}" 
               class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Audit Logs
            </a>
            <button onclick="showInvitations()" 
               class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Company Invitations
            </button>
        </div>

        <!-- Company List -->
        <div id="companyList" class="divide-y divide-gray-200">
            <div class="p-4 text-center text-gray-500">
                Loading companies...
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 overflow-y-auto">
        <!-- Header with Logout -->
        <div class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Metatech CRM - Product Owner Dashboard</h1>
                <p class="mt-1 text-sm text-gray-600">Overview of all companies</p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">{{ auth()->user()->email ?? 'User' }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 text-sm font-medium">
                        Logout
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Default Overview/Stats View -->
        <div id="overviewView" class="p-8">
            <div class="mb-6">

            <!-- Statistics Cards -->
            <div id="statsCards" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Stats will be loaded here -->
            </div>

            <!-- Dummy Graph Placeholder -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Companies Overview</h3>
                <div class="h-64 flex items-center justify-center border-2 border-dashed border-gray-300 rounded">
                    <p class="text-gray-500">Graph placeholder - Companies growth chart</p>
                </div>
            </div>
        </div>

        <!-- Users Management View (Hidden by default) -->
        <div id="usersManagementView" class="hidden p-8">
            <div class="mb-6">
                <button onclick="showOverview()" class="text-indigo-600 hover:text-indigo-800 mb-4">
                    ← Back to Overview
                </button>
                <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
                <p class="mt-2 text-sm text-gray-600">Block or unblock users and internal employees</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">All Users</h3>
                <div id="usersList" class="space-y-4">
                    <div class="text-center text-gray-500">Loading users...</div>
                </div>
            </div>
        </div>

        <!-- Company Invitations View (Hidden by default) -->
        <div id="invitationsView" class="p-8 hidden" style="min-height: 400px; background-color: #f9fafb;">
            <div class="mb-6">
                <button onclick="showOverview()" class="text-indigo-600 hover:text-indigo-800 mb-4">
                    ← Back to Overview
                </button>
                <h1 class="text-2xl font-bold text-gray-900">Company Owner Invitations</h1>
                <p class="mt-2 text-sm text-gray-600">View and manage all company owner invitations</p>
            </div>

            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <div class="mb-4 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">All Invitations</h3>
                        <button onclick="loadInvitations()" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                            Refresh
                        </button>
                    </div>
                    <div id="invitationsList" class="overflow-x-auto">
                        <div class="text-center text-gray-500 py-8">Loading invitations...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Company Details View (Hidden by default) -->
        <div id="companyDetailsView" class="hidden p-8">
            <div class="mb-6">
                <button id="backToOverview" class="text-indigo-600 hover:text-indigo-800 mb-4">
                    ← Back to Overview
                </button>
                <h1 id="companyDetailsTitle" class="text-2xl font-bold text-gray-900"></h1>
            </div>

            <!-- Company Info Card -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Company Information</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4" id="companyInfo">
                    <!-- Company details will be loaded here -->
                </dl>
            </div>

            <!-- Dummy Graphs -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Activity Graph</h3>
                    <div class="h-48 flex items-center justify-center border-2 border-dashed border-gray-300 rounded">
                        <p class="text-gray-500">Activity chart placeholder</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Usage Statistics</h3>
                    <div class="h-48 flex items-center justify-center border-2 border-dashed border-gray-300 rounded">
                        <p class="text-gray-500">Usage chart placeholder</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Get token from localStorage or from server-side rendered data
let token = localStorage.getItem('auth_token');

// If no token in localStorage, use the token passed from server
@if(isset($api_token) && $api_token)
    token = '{{ $api_token }}';
    localStorage.setItem('auth_token', token);
@endif

if (!token) {
    window.location.href = '/login';
}

// Load companies list
async function loadCompanies() {
    try {
        const response = await fetch('/api/v1/company/', {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const data = await response.json();
            displayCompanies(data.data);
        } else {
            if (response.status === 401) {
                window.location.href = '/login';
            } else {
                console.error('Error loading companies');
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Display companies in sidebar
function displayCompanies(companies) {
    const companyList = document.getElementById('companyList');
    
    if (companies.length === 0) {
        companyList.innerHTML = '<div class="p-4 text-center text-gray-500">No companies found. Click "Generate CRM" to create one.</div>';
        return;
    }

    companyList.innerHTML = companies.map(company => {
        const statusClass = company.status === 'blocked' ? 'bg-red-100 text-red-800' : 
                           company.status === 'suspended' ? 'bg-yellow-100 text-yellow-800' : 
                           'bg-green-100 text-green-800';
        const blockButtonText = company.status === 'blocked' || company.status === 'suspended' ? 'Unblock' : 'Block';
        const blockButtonClass = company.status === 'blocked' || company.status === 'suspended' ? 
                                 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700';
        
        return `
        <div class="p-4 hover:bg-gray-50 company-item-wrapper">
            <div class="cursor-pointer company-item" data-company-id="${company.id}" data-company-record-id="${company.company_id || company.id}">
                <div class="font-medium text-gray-900">${company.company_name}</div>
                <div class="text-sm text-gray-500 mt-1">${company.email}</div>
                <div class="text-xs text-indigo-600 mt-1 font-medium flex items-center justify-between">
                    <span id="subdomain-sidebar-${company.id}">${company.subdomain}.crm.metatech.ae</span>
                    <button onclick="event.stopPropagation(); editSubdomain(${company.id}, '${company.subdomain || ''}')" 
                        class="text-indigo-600 hover:text-indigo-800 text-xs font-medium ml-2 px-2 py-1 rounded hover:bg-indigo-50">
                        Edit
                    </button>
                </div>
                <div class="text-xs text-gray-400 mt-1">Created: ${new Date(company.created_at).toLocaleDateString()}</div>
                <div class="mt-2 flex items-center justify-between">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${statusClass}">
                        ${company.status}
                    </span>
                    <button onclick="event.stopPropagation(); toggleCompanyBlock(${company.company_id || company.id}, '${company.status}', '${company.company_name.replace(/'/g, "\\'")}', ${company.id})" 
                        class="text-xs font-medium px-2 py-1 rounded text-white ${blockButtonClass}">
                        ${blockButtonText}
                    </button>
                </div>
            </div>
        </div>
        `;
    }).join('');

    // Add click event listeners
    document.querySelectorAll('.company-item').forEach(item => {
        item.addEventListener('click', function(e) {
            // Don't trigger if clicking on a button or interactive element
            if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
                return;
            }
            const companyId = this.dataset.companyId;
            if (companyId) {
                loadCompanyDetails(companyId);
            }
        });
    });
}

// Load company details
async function loadCompanyDetails(companyId) {
    try {
        const response = await fetch(`/api/v1/company/${companyId}`, {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const data = await response.json();
            displayCompanyDetails(data.data);
        } else {
            console.error('Error loading company details');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Display company details
function displayCompanyDetails(company) {
    // Hide overview, show details
    document.getElementById('overviewView').classList.add('hidden');
    document.getElementById('companyDetailsView').classList.remove('hidden');

    // Update title
    document.getElementById('companyDetailsTitle').textContent = company.company_name;

    // Update company info
    document.getElementById('companyInfo').innerHTML = `
        <div>
            <dt class="text-sm font-medium text-gray-500">Company Name</dt>
            <dd class="mt-1 text-sm text-gray-900">${company.company_name}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Email</dt>
            <dd class="mt-1 text-sm text-gray-900">${company.email}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Contact Person</dt>
            <dd class="mt-1 text-sm text-gray-900">${company.first_name} ${company.last_name}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Created Date</dt>
            <dd class="mt-1 text-sm text-gray-900">${new Date(company.created_at).toLocaleString()}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Number of Admins</dt>
            <dd class="mt-1 text-sm text-gray-900">${company.admin_count}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Number of Users</dt>
            <dd class="mt-1 text-sm text-gray-900">${company.user_count}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Subscription Status</dt>
            <dd class="mt-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    ${company.subscription_status}
                </span>
            </dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Status</dt>
            <dd class="mt-1 flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${company.status === 'blocked' ? 'bg-red-100 text-red-800' : company.status === 'suspended' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'}" id="company-status-detail-${company.id}">
                    ${company.status}
                </span>
                <button onclick="toggleCompanyBlock(${company.company_id || company.id}, '${company.status}', '${company.company_name.replace(/'/g, "\\'")}', ${company.id})" 
                    class="px-3 py-1 text-xs font-medium rounded text-white ${company.status === 'blocked' || company.status === 'suspended' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'}">
                    ${company.status === 'blocked' || company.status === 'suspended' ? 'Unblock Company' : 'Block Company'}
                </button>
            </dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Subdomain</dt>
            <dd class="mt-1 flex items-center gap-2">
                <span class="text-sm text-gray-900" id="subdomain-display-${company.id}">${company.subdomain || 'Not set'}.crm.metatech.ae</span>
                <button onclick="editSubdomain(${company.id}, '${company.subdomain || ''}')" 
                    class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                    Edit
                </button>
            </dd>
        </div>
    `;
}

// Load statistics
async function loadStats() {
    try {
        const response = await fetch('/api/v1/company/stats', {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const data = await response.json();
            displayStats(data.data);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Display statistics
function displayStats(stats) {
    document.getElementById('statsCards').innerHTML = `
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">Total Companies</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">${stats.total_companies}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">Active Companies</div>
            <div class="mt-2 text-3xl font-bold text-green-600">${stats.active_companies}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">Total Admins</div>
            <div class="mt-2 text-3xl font-bold text-blue-600">${stats.total_admins}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">Total Users</div>
            <div class="mt-2 text-3xl font-bold text-purple-600">${stats.total_users}</div>
        </div>
    `;
}

// Back to overview
document.getElementById('backToOverview').addEventListener('click', function() {
    showOverview();
});

function showOverview() {
    const overviewView = document.getElementById('overviewView');
    const companyDetailsView = document.getElementById('companyDetailsView');
    const usersManagementView = document.getElementById('usersManagementView');
    const invitationsView = document.getElementById('invitationsView');
    
    if (overviewView) {
        overviewView.style.display = 'block';
        overviewView.classList.remove('hidden');
    }
    if (companyDetailsView) {
        companyDetailsView.style.display = 'none';
        companyDetailsView.classList.add('hidden');
    }
    if (usersManagementView) {
        usersManagementView.style.display = 'none';
        usersManagementView.classList.add('hidden');
    }
    if (invitationsView) {
        invitationsView.style.display = 'none';
        invitationsView.classList.add('hidden');
    }
}

function showUsersManagement() {
    const overviewView = document.getElementById('overviewView');
    const companyDetailsView = document.getElementById('companyDetailsView');
    const usersManagementView = document.getElementById('usersManagementView');
    const invitationsView = document.getElementById('invitationsView');
    
    if (overviewView) {
        overviewView.style.display = 'none';
        overviewView.classList.add('hidden');
    }
    if (companyDetailsView) {
        companyDetailsView.style.display = 'none';
        companyDetailsView.classList.add('hidden');
    }
    if (usersManagementView) {
        usersManagementView.style.display = 'block';
        usersManagementView.classList.remove('hidden');
    }
    if (invitationsView) {
        invitationsView.style.display = 'none';
        invitationsView.classList.add('hidden');
    }
    loadUsers();
}

function showInvitations() {
    try {
        console.log('=== showInvitations called ===');
        
        // Hide all other views first
        const overviewView = document.getElementById('overviewView');
        const companyDetailsView = document.getElementById('companyDetailsView');
        const usersManagementView = document.getElementById('usersManagementView');
        const invitationsView = document.getElementById('invitationsView');
        
        console.log('Elements found:', {
            overviewView: !!overviewView,
            companyDetailsView: !!companyDetailsView,
            usersManagementView: !!usersManagementView,
            invitationsView: !!invitationsView
        });
        
        if (!invitationsView) {
            console.error('invitationsView element not found!');
            alert('Error: Invitations view not found. Please refresh the page.');
            return;
        }
        
        // Hide all views - use same pattern as showUsersManagement
        if (overviewView) {
            overviewView.style.display = 'none';
            overviewView.classList.add('hidden');
        }
        if (companyDetailsView) {
            companyDetailsView.style.display = 'none';
            companyDetailsView.classList.add('hidden');
        }
        if (usersManagementView) {
            usersManagementView.style.display = 'none';
            usersManagementView.classList.add('hidden');
        }
        
        // Show invitations view - use same pattern as showUsersManagement
        invitationsView.style.display = 'block';
        invitationsView.classList.remove('hidden');
        
        console.log('Invitations view should be visible now, loading invitations...');
        
        // Load invitations data
        loadInvitations();
    } catch (error) {
        console.error('Error in showInvitations:', error);
        alert('Error showing invitations view: ' + error.message + '\n\nCheck console for details.');
    }
}

// Load all company owner invitations
async function loadInvitations() {
    console.log('loadInvitations called');
    const invitationsListDiv = document.getElementById('invitationsList');
    if (!invitationsListDiv) {
        console.error('invitationsList element not found!');
        return;
    }
    
    invitationsListDiv.innerHTML = '<div class="text-center text-gray-500 py-8">Loading invitations...</div>';
    
    try {
        console.log('Fetching invitations from API...');
        const response = await fetch('/api/v1/company/invitations', {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        });

        console.log('API response status:', response.status);
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            console.error('API error:', errorData);
            invitationsListDiv.innerHTML = `<div class="text-center text-red-500 py-8">Error loading invitations: ${errorData.message || 'Unknown error'}. Please try again.</div>`;
            return;
        }

        const data = await response.json();
        console.log('API response data:', JSON.stringify(data, null, 2));
        const invitations = data.data || [];
        console.log('Invitations count:', invitations.length);
        console.log('Invitations array:', invitations);

        if (invitations.length === 0) {
            invitationsListDiv.innerHTML = '<div class="text-center text-gray-500 py-8">No invitations found. Create a company to send an invitation.</div>';
            return;
        }

        console.log('Rendering invitations table with', invitations.length, 'items');
        invitationsListDiv.innerHTML = `
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subdomain</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${invitations.map(invitation => {
                        const statusClass = invitation.status === 'accepted' ? 'bg-green-100 text-green-800' : 
                                           invitation.status === 'expired' ? 'bg-red-100 text-red-800' : 
                                           'bg-yellow-100 text-yellow-800';
                        const expiresDate = invitation.expires_at ? new Date(invitation.expires_at).toLocaleDateString() : 'N/A';
                        const acceptedDate = invitation.accepted_at ? new Date(invitation.accepted_at).toLocaleDateString() : '-';
                        
                        return `
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">${invitation.company_name}</div>
                                    ${invitation.first_name || invitation.last_name ? 
                                        `<div class="text-sm text-gray-500">${(invitation.first_name || '')} ${(invitation.last_name || '')}</div>` 
                                        : ''}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">${invitation.email}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">${invitation.subdomain}.crm.metatech.ae</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                                        ${invitation.status.charAt(0).toUpperCase() + invitation.status.slice(1)}
                                    </span>
                                    ${invitation.accepted ? `<div class="text-xs text-gray-500 mt-1">Accepted: ${acceptedDate}</div>` : ''}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    ${new Date(invitation.created_at).toLocaleDateString()}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    ${expiresDate}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    ${invitation.status === 'pending' ? 
                                        `<button onclick="cancelInvitation(${invitation.id}, '${invitation.email.replace(/'/g, "\\'")}')" 
                                            class="text-red-600 hover:text-red-900">Cancel</button>` 
                                        : '-'}
                                </td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
        `;
    } catch (error) {
        console.error('Error loading invitations:', error);
        const invitationsListDiv = document.getElementById('invitationsList');
        if (invitationsListDiv) {
            invitationsListDiv.innerHTML = `<div class="text-center text-red-500 py-8">Error loading invitations: ${error.message}. Please check the browser console for details.</div>`;
        } else {
            alert('Error: Could not find invitations list element. Error: ' + error.message);
        }
    }
}

// Cancel an invitation
async function cancelInvitation(invitationId, email) {
    if (!confirm(`Are you sure you want to cancel the invitation for ${email}?`)) {
        return;
    }

    try {
        const response = await fetch(`/api/v1/company/invitations/${invitationId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (response.ok) {
            alert('Invitation cancelled successfully');
            loadInvitations();
        } else {
            const data = await response.json();
            alert(data.message || 'Error cancelling invitation');
        }
    } catch (error) {
        console.error('Error cancelling invitation:', error);
        alert('Error cancelling invitation. Please try again.');
    }
}

// Load all users
async function loadUsers() {
    try {
        // Load internal employees
        const internalResponse = await fetch('/api/v1/internal-employee/', {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        });

        // Load company admins (we'll get from companies endpoint)
        const companiesResponse = await fetch('/api/v1/company/', {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        });

        const usersListDiv = document.getElementById('usersList');
        usersListDiv.innerHTML = '<div class="text-center text-gray-500">Loading users...</div>';

        let html = '<div class="divide-y divide-gray-200">';

        if (internalResponse.ok) {
            const internalData = await internalResponse.json();
            if (internalData.data && internalData.data.length > 0) {
                html += '<div class="mb-6"><h4 class="text-md font-semibold text-gray-700 mb-3">Internal Employees</h4>';
                internalData.data.forEach(user => {
                    const statusClass = user.status === 'blocked' ? 'bg-red-100 text-red-800' : 
                                       user.status === 'suspended' ? 'bg-yellow-100 text-yellow-800' : 
                                       'bg-green-100 text-green-800';
                    const blockButtonText = user.status === 'blocked' || user.status === 'suspended' ? 'Unblock' : 'Block';
                    const blockButtonClass = user.status === 'blocked' || user.status === 'suspended' ? 
                                             'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700';
                    
                    html += `
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900">${user.name || user.email}</div>
                                <div class="text-sm text-gray-500">${user.email} - ${user.role}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                                    ${user.status || 'active'}
                                </span>
                                <button onclick="toggleUserBlock(${user.id}, '${user.status || 'active'}', '${(user.name || user.email).replace(/'/g, "\\'")}')" 
                                    class="px-3 py-1 text-xs font-medium rounded text-white ${blockButtonClass}">
                                    ${blockButtonText}
                                </button>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
            }
        }

        if (companiesResponse.ok) {
            const companiesData = await companiesResponse.json();
            if (companiesData.data && companiesData.data.length > 0) {
                html += '<div><h4 class="text-md font-semibold text-gray-700 mb-3">Company Super Admins</h4>';
                companiesData.data.forEach(company => {
                    const statusClass = company.status === 'blocked' ? 'bg-red-100 text-red-800' : 
                                       company.status === 'suspended' ? 'bg-yellow-100 text-yellow-800' : 
                                       'bg-green-100 text-green-800';
                    const blockButtonText = company.status === 'blocked' || company.status === 'suspended' ? 'Unblock' : 'Block';
                    const blockButtonClass = company.status === 'blocked' || company.status === 'suspended' ? 
                                             'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700';
                    
                    html += `
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900">${company.company_name}</div>
                                <div class="text-sm text-gray-500">${company.email} - ${company.first_name} ${company.last_name}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                                    ${company.status}
                                </span>
                                <button onclick="toggleUserBlock(${company.id}, '${company.status}', '${company.company_name.replace(/'/g, "\\'")}')" 
                                    class="px-3 py-1 text-xs font-medium rounded text-white ${blockButtonClass}">
                                    ${blockButtonText}
                                </button>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
            }
        }

        html += '</div>';
        usersListDiv.innerHTML = html;

    } catch (error) {
        console.error('Error loading users:', error);
        document.getElementById('usersList').innerHTML = '<div class="text-center text-red-500">Error loading users. Please try again.</div>';
    }
}

// Refresh companies
document.getElementById('refreshCompanies').addEventListener('click', function() {
    loadCompanies();
});

// Block/Unblock Company
async function toggleCompanyBlock(companyId, currentStatus, companyName, userId) {
    // Use userId as fallback if companyId (Company record ID) doesn't exist
    const idToUse = companyId || userId;
    
    if (!idToUse) {
        alert('Company ID is missing. Please refresh the page and try again.');
        return;
    }
    
    const isBlocked = currentStatus === 'blocked' || currentStatus === 'suspended';
    const action = isBlocked ? 'unblock' : 'block';
    const confirmMessage = isBlocked 
        ? `Are you sure you want to unblock "${companyName}"?`
        : `Are you sure you want to block "${companyName}"? This will prevent all users in this company from logging in.`;
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    let reason = '';
    if (!isBlocked) {
        reason = prompt('Please provide a reason for blocking (optional):');
        if (reason === null) {
            return; // User cancelled
        }
    }
    
    try {
        const url = `/api/v1/user-management/companies/${idToUse}/${action}`;
        const options = {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };
        
        if (reason !== null && reason.trim()) {
            options.body = JSON.stringify({ reason: reason.trim() });
        }
        
        const response = await fetch(url, options);
        const data = await response.json();
        
        if (response.ok) {
            alert(data.message || `Company ${action}ed successfully!`);
            loadCompanies();
            // If company details view is open, reload it
            const companyDetailsView = document.getElementById('companyDetailsView');
            if (!companyDetailsView.classList.contains('hidden')) {
                loadCompanies(); // This will refresh the sidebar
            }
        } else {
            alert(data.message || `Failed to ${action} company`);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}

// Edit subdomain
function editSubdomain(companyId, currentSubdomain) {
    const newSubdomain = prompt('Enter new subdomain (lowercase letters, numbers, and hyphens only):', currentSubdomain);
    
    if (newSubdomain === null) {
        return; // User cancelled
    }
    
    const trimmedSubdomain = newSubdomain.trim().toLowerCase();
    
    if (!trimmedSubdomain) {
        alert('Subdomain cannot be empty');
        return;
    }
    
    if (!/^[a-z0-9-]+$/.test(trimmedSubdomain)) {
        alert('Subdomain can only contain lowercase letters, numbers, and hyphens');
        return;
    }
    
    // Show loading
    const subdomainDisplay = document.getElementById(`subdomain-display-${companyId}`);
    const subdomainDisplayDetail = document.getElementById(`subdomain-display-detail-${companyId}`);
    const subdomainSidebar = document.getElementById(`subdomain-sidebar-${companyId}`);
    if (subdomainDisplay) {
        subdomainDisplay.textContent = 'Updating...';
    }
    if (subdomainDisplayDetail) {
        subdomainDisplayDetail.textContent = 'Updating...';
    }
    if (subdomainSidebar) {
        subdomainSidebar.textContent = 'Updating...';
    }
    
    // Update subdomain via API
    fetch(`/api/v1/company/${companyId}/subdomain`, {
        method: 'PUT',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            subdomain: trimmedSubdomain
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.data) {
            const newSubdomainValue = data.data.subdomain;
            
            // Update the display in sidebar
            if (subdomainDisplay) {
                subdomainDisplay.textContent = newSubdomainValue + '.crm.metatech.ae';
            }
            
            // Update the display in details view
            if (subdomainDisplayDetail) {
                subdomainDisplayDetail.textContent = newSubdomainValue + '.crm.metatech.ae';
            }
            
            // Update the display in sidebar
            if (subdomainSidebar) {
                subdomainSidebar.textContent = newSubdomainValue + '.crm.metatech.ae';
            }
            
            // Reload companies list to reflect the change
            loadCompanies();
            
            // If this is the currently viewed company, reload its details
            const companyDetailsView = document.getElementById('companyDetailsView');
            if (!companyDetailsView.classList.contains('hidden')) {
                loadCompanyDetails(companyId);
            }
            
            alert('Subdomain updated successfully!');
        } else {
            alert(data.message || 'Failed to update subdomain');
            // Revert display
            if (subdomainDisplay && currentSubdomain) {
                subdomainDisplay.textContent = currentSubdomain + '.crm.metatech.ae';
            }
            if (subdomainDisplayDetail && currentSubdomain) {
                subdomainDisplayDetail.textContent = currentSubdomain + '.crm.metatech.ae';
            }
            if (subdomainSidebar && currentSubdomain) {
                subdomainSidebar.textContent = currentSubdomain + '.crm.metatech.ae';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating subdomain');
        // Revert display
        if (subdomainDisplay && currentSubdomain) {
            subdomainDisplay.textContent = currentSubdomain + '.crm.metatech.ae';
        }
        if (subdomainDisplayDetail && currentSubdomain) {
            subdomainDisplayDetail.textContent = currentSubdomain + '.crm.metatech.ae';
        }
        if (subdomainSidebar && currentSubdomain) {
            subdomainSidebar.textContent = currentSubdomain + '.crm.metatech.ae';
        }
    });
}

// Initial load
loadCompanies();
loadStats();
</script>
</div>
</body>
</html>
