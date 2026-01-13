<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Audit Logs - Metatech CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
                            <p class="text-sm text-gray-600">System activity and security logs</p>
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
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Filters</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="filter-event-type" class="block text-sm font-medium text-gray-700 mb-1">Event Type</label>
                        <select id="filter-event-type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Events</option>
                            <option value="login">Login</option>
                            <option value="invitation">Invitation</option>
                            <option value="role_change">Role Change</option>
                            <option value="user_created">User Created</option>
                            <option value="user_updated">User Updated</option>
                            <option value="company_created">Company Created</option>
                        </select>
                    </div>
                    <div>
                        <label for="filter-action" class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                        <select id="filter-action" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Actions</option>
                            <option value="login_success">Login Success</option>
                            <option value="login_failed">Login Failed</option>
                            <option value="invitation_sent">Invitation Sent</option>
                            <option value="invitation_accepted">Invitation Accepted</option>
                            <option value="invitation_cancelled">Invitation Cancelled</option>
                            <option value="role_updated">Role Updated</option>
                        </select>
                    </div>
                    <div>
                        <label for="filter-date-from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input type="date" id="filter-date-from" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="filter-date-to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input type="date" id="filter-date-to" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button onclick="applyFilters()" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Apply Filters
                    </button>
                    <button onclick="clearFilters()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        Clear
                    </button>
                    <button onclick="exportLogs()" class="ml-auto px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Export CSV
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-sm text-gray-600">Total Logs</p>
                    <p class="text-2xl font-bold text-gray-900" id="stat-total">Loading...</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-sm text-gray-600">Login Attempts</p>
                    <p class="text-2xl font-bold text-blue-600" id="stat-logins">Loading...</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-sm text-gray-600">Failed Logins</p>
                    <p class="text-2xl font-bold text-red-600" id="stat-failed">Loading...</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-sm text-gray-600">Invitations</p>
                    <p class="text-2xl font-bold text-green-600" id="stat-invitations">Loading...</p>
                </div>
            </div>

            <!-- Audit Logs Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Activity Log</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                </tr>
                            </thead>
                            <tbody id="logs-tbody" class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        Loading audit logs...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing <span id="showing-from">0</span> to <span id="showing-to">0</span> of <span id="showing-total">0</span> results
                        </div>
                        <div class="flex gap-2" id="pagination-buttons">
                            <!-- Pagination buttons will be inserted here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const apiToken = '{{ $api_token }}';
        let currentPage = 1;
        let currentFilters = {};

        // Load audit logs on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadAuditLogs();
            loadStats();
        });

        async function loadAuditLogs(page = 1) {
            try {
                const params = new URLSearchParams({
                    page: page,
                    per_page: 20,
                    ...currentFilters
                });

                const response = await fetch(`/api/v1/audit-logs?${params}`, {
                    headers: {
                        'Authorization': `Bearer ${apiToken}`,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    renderLogs(data.data);
                    renderPagination(data.meta);
                } else {
                    console.error('Error loading logs:', data);
                    document.getElementById('logs-tbody').innerHTML = `
                        <tr><td colspan="6" class="px-6 py-4 text-center text-red-600">
                            Error loading logs: ${data.message || 'Unknown error'}
                        </td></tr>
                    `;
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('logs-tbody').innerHTML = `
                    <tr><td colspan="6" class="px-6 py-4 text-center text-red-600">
                        Failed to load audit logs. Please check your connection.
                    </td></tr>
                `;
            }
        }

        function renderLogs(logs) {
            const tbody = document.getElementById('logs-tbody');
            
            if (!logs || logs.length === 0) {
                tbody.innerHTML = `
                    <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No audit logs found.
                    </td></tr>
                `;
                return;
            }

            tbody.innerHTML = logs.map(log => {
                const eventTypeColor = getEventTypeColor(log.event_type);
                const actionColor = getActionColor(log.action);
                const timestamp = new Date(log.created_at).toLocaleString();
                const userName = log.user ? `${log.user.name || log.user.email}` : 'System';
                
                return `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${timestamp}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full ${eventTypeColor}">
                                ${formatEventType(log.event_type)}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full ${actionColor}">
                                ${formatAction(log.action)}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${userName}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${log.ip_address || 'N/A'}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            ${formatDetails(log.details)}
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function renderPagination(meta) {
            if (!meta) return;
            
            document.getElementById('showing-from').textContent = meta.from || 0;
            document.getElementById('showing-to').textContent = meta.to || 0;
            document.getElementById('showing-total').textContent = meta.total || 0;

            const paginationButtons = document.getElementById('pagination-buttons');
            let buttons = '';

            if (meta.current_page > 1) {
                buttons += `<button onclick="loadAuditLogs(${meta.current_page - 1})" class="px-3 py-1 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Previous</button>`;
            }

            if (meta.current_page < meta.last_page) {
                buttons += `<button onclick="loadAuditLogs(${meta.current_page + 1})" class="px-3 py-1 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Next</button>`;
            }

            paginationButtons.innerHTML = buttons;
        }

        async function loadStats() {
            // For now, show basic stats from the current page
            // You can enhance this by creating a dedicated stats API endpoint
            document.getElementById('stat-total').textContent = '-';
            document.getElementById('stat-logins').textContent = '-';
            document.getElementById('stat-failed').textContent = '-';
            document.getElementById('stat-invitations').textContent = '-';
        }

        function applyFilters() {
            currentFilters = {
                event_type: document.getElementById('filter-event-type').value,
                action: document.getElementById('filter-action').value,
                date_from: document.getElementById('filter-date-from').value,
                date_to: document.getElementById('filter-date-to').value
            };

            // Remove empty filters
            Object.keys(currentFilters).forEach(key => {
                if (!currentFilters[key]) delete currentFilters[key];
            });

            loadAuditLogs(1);
        }

        function clearFilters() {
            document.getElementById('filter-event-type').value = '';
            document.getElementById('filter-action').value = '';
            document.getElementById('filter-date-from').value = '';
            document.getElementById('filter-date-to').value = '';
            currentFilters = {};
            loadAuditLogs(1);
        }

        function exportLogs() {
            const params = new URLSearchParams(currentFilters);
            window.location.href = `/api/v1/audit-logs/export?${params}&token=${apiToken}`;
        }

        function getEventTypeColor(eventType) {
            const colors = {
                'login': 'bg-blue-100 text-blue-800',
                'invitation': 'bg-green-100 text-green-800',
                'role_change': 'bg-purple-100 text-purple-800',
                'user_created': 'bg-indigo-100 text-indigo-800',
                'user_updated': 'bg-yellow-100 text-yellow-800',
                'company_created': 'bg-pink-100 text-pink-800'
            };
            return colors[eventType] || 'bg-gray-100 text-gray-800';
        }

        function getActionColor(action) {
            if (action.includes('success')) return 'bg-green-100 text-green-800';
            if (action.includes('failed') || action.includes('cancelled')) return 'bg-red-100 text-red-800';
            return 'bg-blue-100 text-blue-800';
        }

        function formatEventType(eventType) {
            return eventType.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        }

        function formatAction(action) {
            return action.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        }

        function formatDetails(details) {
            if (!details || Object.keys(details).length === 0) return '-';
            
            return Object.entries(details)
                .filter(([key, value]) => value !== null && value !== undefined)
                .map(([key, value]) => `<strong>${key}:</strong> ${value}`)
                .slice(0, 3)
                .join(', ');
        }
    </script>
</body>
</html>
