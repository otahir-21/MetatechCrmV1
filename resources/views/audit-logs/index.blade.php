<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Audit Logs - Metatech CRM</title>
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-gray-50">
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
                    <p class="mt-1 text-sm text-gray-600">Track all system activities and security events</p>
                </div>
                <a href="/dashboard" class="text-indigo-600 hover:text-indigo-800">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Filters</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Event Type</label>
                    <select id="eventTypeFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All</option>
                        <option value="login">Login</option>
                        <option value="invitation">Invitation</option>
                        <option value="role_change">Role Change</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                    <select id="actionFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" id="dateFromFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" id="dateToFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="mt-4">
                <button onclick="applyFilters()" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Apply Filters
                </button>
                <button onclick="clearFilters()" class="ml-2 px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Clear
                </button>
            </div>
        </div>

        <!-- Audit Logs Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Audit Logs</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                        </tr>
                    </thead>
                    <tbody id="auditLogsTableBody" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">Loading audit logs...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div id="pagination" class="px-6 py-4 border-t border-gray-200">
                <!-- Pagination will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
    const apiBase = '/api/v1';
    const token = @if(isset($api_token) && $api_token) '{{ $api_token }}' @else '{{ session("api_token") }}' @endif;

    let currentPage = 1;
    let filters = {};

    // Load audit logs
    async function loadAuditLogs(page = 1) {
        currentPage = page;
        const params = new URLSearchParams({
            page: page,
            per_page: 20,
            ...filters
        });

        try {
            const response = await fetch(`${apiBase}/audit-logs?${params}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Unknown error' }));
                console.error('API Error:', errorData);
                throw new Error(errorData.message || 'Failed to load audit logs');
            }

            const data = await response.json();
            renderAuditLogs(data.data);
            renderPagination(data.meta);
        } catch (error) {
            console.error('Error loading audit logs:', error);
            let errorMessage = 'Error loading audit logs. Please try again.';
            if (error.message) {
                errorMessage = error.message;
            }
            document.getElementById('auditLogsTableBody').innerHTML = 
                `<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">${errorMessage}</td></tr>`;
        }
    }

    // Render audit logs table
    function renderAuditLogs(logs) {
        const tbody = document.getElementById('auditLogsTableBody');
        
        if (logs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No audit logs found.</td></tr>';
            return;
        }

        tbody.innerHTML = logs.map(log => {
            const timestamp = new Date(log.created_at).toLocaleString();
            const eventType = log.event_type.replace('_', ' ');
            const action = log.action.replace('_', ' ');
            const userName = log.user ? (log.user.name || log.user.email) : 'N/A';
            const targetUserName = log.target_user ? (log.target_user.name || log.target_user.email) : 'N/A';
            const details = formatDetails(log.details);

            return `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${timestamp}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getEventTypeColor(log.event_type)}">
                            ${eventType}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${action}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${userName}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${targetUserName}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${log.ip_address || 'N/A'}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">${details}</td>
                </tr>
            `;
        }).join('');
    }

    // Format details JSON
    function formatDetails(details) {
        if (!details) return 'N/A';
        if (typeof details === 'string') {
            try {
                details = JSON.parse(details);
            } catch (e) {
                return details;
            }
        }
        
        const items = [];
        if (details.email) items.push(`Email: ${details.email}`);
        if (details.old_role && details.new_role) items.push(`${details.old_role} → ${details.new_role}`);
        if (details.invitee_email) items.push(`Invitee: ${details.invitee_email}`);
        if (details.reason) items.push(`Reason: ${details.reason}`);
        if (details.role) items.push(`Role: ${details.role}`);
        if (details.department) items.push(`Dept: ${details.department}`);
        
        return items.length > 0 ? items.join(', ') : JSON.stringify(details);
    }

    // Get event type badge color
    function getEventTypeColor(eventType) {
        const colors = {
            'login': 'bg-blue-100 text-blue-800',
            'invitation': 'bg-green-100 text-green-800',
            'role_change': 'bg-purple-100 text-purple-800'
        };
        return colors[eventType] || 'bg-gray-100 text-gray-800';
    }

    // Render pagination
    function renderPagination(meta) {
        const paginationDiv = document.getElementById('pagination');
        
        if (meta.last_page <= 1) {
            paginationDiv.innerHTML = '';
            return;
        }

        let html = '<div class="flex items-center justify-between">';
        html += `<div class="text-sm text-gray-700">Showing ${meta.from} to ${meta.to} of ${meta.total} results</div>`;
        html += '<div class="flex space-x-2">';
        
        // Previous button
        if (meta.current_page > 1) {
            html += `<button onclick="loadAuditLogs(${meta.current_page - 1})" class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Previous</button>`;
        }
        
        // Page numbers
        for (let i = 1; i <= meta.last_page; i++) {
            if (i === meta.current_page) {
                html += `<span class="px-3 py-1 bg-indigo-600 text-white rounded-md text-sm">${i}</span>`;
            } else if (i === 1 || i === meta.last_page || (i >= meta.current_page - 2 && i <= meta.current_page + 2)) {
                html += `<button onclick="loadAuditLogs(${i})" class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">${i}</button>`;
            } else if (i === meta.current_page - 3 || i === meta.current_page + 3) {
                html += '<span class="px-3 py-1 text-gray-500">...</span>';
            }
        }
        
        // Next button
        if (meta.current_page < meta.last_page) {
            html += `<button onclick="loadAuditLogs(${meta.current_page + 1})" class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Next</button>`;
        }
        
        html += '</div></div>';
        paginationDiv.innerHTML = html;
    }

    // Apply filters
    function applyFilters() {
        filters = {};
        
        const eventType = document.getElementById('eventTypeFilter').value;
        if (eventType) filters.event_type = eventType;
        
        const action = document.getElementById('actionFilter').value;
        if (action) filters.action = action;
        
        const dateFrom = document.getElementById('dateFromFilter').value;
        if (dateFrom) filters.date_from = dateFrom;
        
        const dateTo = document.getElementById('dateToFilter').value;
        if (dateTo) filters.date_to = dateTo;
        
        loadAuditLogs(1);
    }

    // Clear filters
    function clearFilters() {
        document.getElementById('eventTypeFilter').value = '';
        document.getElementById('actionFilter').value = '';
        document.getElementById('dateFromFilter').value = '';
        document.getElementById('dateToFilter').value = '';
        filters = {};
        loadAuditLogs(1);
    }

    // Update action filter based on event type
    document.getElementById('eventTypeFilter').addEventListener('change', function() {
        const eventType = this.value;
        const actionFilter = document.getElementById('actionFilter');
        actionFilter.innerHTML = '<option value="">All</option>';
        
        const actions = {
            'login': ['login_success', 'login_failed'],
            'invitation': ['invitation_sent', 'invitation_accepted', 'invitation_cancelled'],
            'role_change': ['role_updated']
        };
        
        if (actions[eventType]) {
            actions[eventType].forEach(action => {
                const option = document.createElement('option');
                option.value = action;
                option.textContent = action.replace('_', ' ');
                actionFilter.appendChild(option);
            });
        }
    });

    // Load audit logs on page load
    loadAuditLogs();
</script>
</body>
</html>

