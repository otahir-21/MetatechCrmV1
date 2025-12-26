<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Company Dashboard - Metatech CRM</title>
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-gray-50">
<div class="flex h-screen bg-gray-50">
    <!-- Left Sidebar -->
    <div class="w-64 bg-white border-r border-gray-200 flex flex-col h-full">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">{{ auth()->user()->company_name ?? 'Company Dashboard' }}</h2>
            <p class="text-xs text-gray-500 mt-1">{{ auth()->user()->email }}</p>
        </div>
        
        <nav class="mt-4 flex-1">
            <a href="#" onclick="showSection('projects', this); return false;" 
               class="nav-item block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-indigo-500">
                Projects
            </a>
            <a href="#" onclick="showSection('tasks', this); return false;" 
               class="nav-item block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-indigo-500">
                Tasks
            </a>
            <a href="#" onclick="showSection('staff-invitations', this); return false;" 
               class="nav-item block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-indigo-500">
                Staff Invitations
            </a>
            <a href="#" onclick="showSection('team', this); return false;" 
               class="nav-item block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-indigo-500">
                Team Members
            </a>
        </nav>
        
        <div class="p-4 border-t border-gray-200">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                    Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 overflow-y-auto">
        <!-- Projects Section -->
        <div id="projects-section" class="section p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Projects</h1>
                    <p class="mt-2 text-sm text-gray-600">Manage your company projects</p>
                </div>
                <button onclick="showCreateProjectModal()" 
                        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    Create Project
                </button>
            </div>

            <div id="projectsList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="text-center text-gray-500 py-8">Loading projects...</div>
            </div>
        </div>

        <!-- Staff Invitations Section -->
        <div id="staff-invitations-section" class="section hidden p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Staff Invitations</h1>
                    <p class="mt-2 text-sm text-gray-600">Invite team members to your company</p>
                </div>
                <button onclick="showInviteModal()" 
                        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    Invite Staff
                </button>
            </div>

            <div id="invitationsList" class="bg-white rounded-lg shadow">
                <div class="p-8 text-center text-gray-500">Loading invitations...</div>
            </div>
        </div>

        <!-- Tasks Section (Notion-like) -->
        <div id="tasks-section" class="section hidden p-8">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Tasks</h1>
                    <p class="mt-2 text-sm text-gray-600">Manage tasks across all projects</p>
                </div>
                <div class="flex gap-2">
                    <select id="taskProjectFilter" onchange="filterTasks()" 
                            class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <option value="">All Projects</option>
                    </select>
                    <select id="taskStatusFilter" onchange="filterTasks()" 
                            class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <option value="">All Status</option>
                        <option value="todo">To Do</option>
                        <option value="in_progress">In Progress</option>
                        <option value="review">Review</option>
                        <option value="done">Done</option>
                    </select>
                    <button onclick="showCreateTaskModal()" 
                            class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">
                        + New Task
                    </button>
                </div>
            </div>

            <!-- Notion-like Task Board -->
            <div id="tasksBoard" class="space-y-2">
                <div class="text-center text-gray-500 py-8">Loading tasks...</div>
            </div>
        </div>

        <!-- Team Members Section -->
        <div id="team-section" class="section hidden p-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Team Members</h1>
                <p class="mt-2 text-sm text-gray-600">View all team members in your company</p>
            </div>

            <div id="teamList" class="bg-white rounded-lg shadow">
                <div class="p-8 text-center text-gray-500">Loading team members...</div>
            </div>
        </div>
    </div>
</div>

<!-- Create Project Modal -->
<div id="createProjectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Project</h3>
            <form id="createProjectForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Project Name</label>
                    <input type="text" id="projectName" name="name" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="projectDescription" name="description" rows="3"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeCreateProjectModal()" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Invite Staff Modal -->
<div id="inviteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Invite Staff Member</h3>
            <form id="inviteForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" id="inviteEmail" name="email" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Role</label>
                    <select id="inviteRole" name="role" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                        <option value="project_manager">Project Manager</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeInviteModal()" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
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

// Show/Hide Sections
function showSection(section, element) {
    document.querySelectorAll('.section').forEach(el => el.classList.add('hidden'));
    document.getElementById(section + '-section').classList.remove('hidden');
    
    // Update nav active state
    document.querySelectorAll('.nav-item').forEach(el => {
        el.classList.remove('border-indigo-500', 'bg-indigo-50');
    });
    if (element) {
        element.classList.add('border-indigo-500', 'bg-indigo-50');
    }
    
    // Load section data
    if (section === 'projects') loadProjects();
    if (section === 'tasks') loadTasks();
    if (section === 'staff-invitations') loadInvitations();
    if (section === 'team') loadTeam();
}

// Load Projects
async function loadProjects() {
    try {
        const response = await fetch(`${apiBase}/projects`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        const projectsList = document.getElementById('projectsList');
        
        if (data.data && data.data.length > 0) {
            projectsList.innerHTML = data.data.map(project => `
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">${project.name}</h3>
                    <p class="text-sm text-gray-600 mt-2">${project.description || 'No description'}</p>
                    <div class="mt-4 flex justify-between items-center">
                        <span class="text-xs text-gray-500">Access: ${project.access_level || 'admin'}</span>
                        <button onclick="viewProject(${project.id})" 
                                class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                            View Details
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            projectsList.innerHTML = '<div class="col-span-full text-center text-gray-500 py-8">No projects found. Create your first project!</div>';
        }
    } catch (error) {
        console.error('Error loading projects:', error);
        document.getElementById('projectsList').innerHTML = '<div class="col-span-full text-center text-red-500 py-8">Error loading projects</div>';
    }
}

// Load Invitations
async function loadInvitations() {
    try {
        const response = await fetch(`${apiBase}/staff/invitations`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        const invitationsList = document.getElementById('invitationsList');
        
        if (data.data && data.data.length > 0) {
            invitationsList.innerHTML = `
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expires</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${data.data.map(inv => `
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${inv.email}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${inv.role}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full ${inv.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : inv.status === 'accepted' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                        ${inv.status}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(inv.expires_at).toLocaleDateString()}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    ${inv.status === 'pending' ? `
                                        <button onclick="cancelInvitation(${inv.id})" 
                                                class="text-red-600 hover:text-red-800">Cancel</button>
                                    ` : '-'}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        } else {
            invitationsList.innerHTML = '<div class="p-8 text-center text-gray-500">No invitations found. Invite your first team member!</div>';
        }
    } catch (error) {
        console.error('Error loading invitations:', error);
        document.getElementById('invitationsList').innerHTML = '<div class="p-8 text-center text-red-500">Error loading invitations</div>';
    }
}

// Load Team Members
async function loadTeam() {
    // This would require a new API endpoint to list company users
    document.getElementById('teamList').innerHTML = '<div class="p-8 text-center text-gray-500">Team members list - To be implemented</div>';
}

// Create Project
async function createProject() {
    const form = document.getElementById('createProjectForm');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const data = {
            name: document.getElementById('projectName').value,
            description: document.getElementById('projectDescription').value
        };
        
        try {
            const response = await fetch(`${apiBase}/projects`, {
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
                alert('Project created successfully!');
                closeCreateProjectModal();
                loadProjects();
            } else {
                alert(result.message || 'Error creating project');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error creating project');
        }
    });
}

// Invite Staff
async function inviteStaff() {
    const form = document.getElementById('inviteForm');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const data = {
            email: document.getElementById('inviteEmail').value,
            role: document.getElementById('inviteRole').value
        };
        
        try {
            const response = await fetch(`${apiBase}/staff/invite`, {
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
                alert('Invitation sent successfully!');
                closeInviteModal();
                loadInvitations();
            } else {
                alert(result.message || 'Error sending invitation');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error sending invitation');
        }
    });
}

// Cancel Invitation
async function cancelInvitation(id) {
    if (!confirm('Are you sure you want to cancel this invitation?')) return;
    
    try {
        const response = await fetch(`${apiBase}/staff/invitations/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            alert('Invitation cancelled');
            loadInvitations();
        } else {
            const result = await response.json();
            alert(result.message || 'Error cancelling invitation');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error cancelling invitation');
    }
}

// Modal Functions
function showCreateProjectModal() {
    document.getElementById('createProjectModal').classList.remove('hidden');
    createProject();
}

function closeCreateProjectModal() {
    document.getElementById('createProjectModal').classList.add('hidden');
    document.getElementById('createProjectForm').reset();
}

function showInviteModal() {
    document.getElementById('inviteModal').classList.remove('hidden');
    inviteStaff();
}

function closeInviteModal() {
    document.getElementById('inviteModal').classList.add('hidden');
    document.getElementById('inviteForm').reset();
}

function viewProject(id) {
    // Show project detail with tasks
    loadProjectTasks(id);
}

// Load Tasks (Notion-like view)
let allProjects = [];
let allTasks = [];
let currentProjectFilter = '';
let currentStatusFilter = '';

async function loadTasks() {
    try {
        // Load projects for filter dropdown
        const projectsResponse = await fetch(`${apiBase}/projects`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (projectsResponse.ok) {
            const projectsData = await projectsResponse.json();
            allProjects = projectsData.data || [];
            
            const projectFilter = document.getElementById('taskProjectFilter');
            projectFilter.innerHTML = '<option value="">All Projects</option>' + 
                allProjects.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
        }
        
        // Load all user tasks
        const tasksResponse = await fetch(`${apiBase}/tasks/my-tasks`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (tasksResponse.ok) {
            const tasksData = await tasksResponse.json();
            allTasks = tasksData.data || [];
            displayTasks();
        }
    } catch (error) {
        console.error('Error loading tasks:', error);
        document.getElementById('tasksBoard').innerHTML = '<div class="text-center text-red-500 py-8">Error loading tasks</div>';
    }
}

function filterTasks() {
    currentProjectFilter = document.getElementById('taskProjectFilter').value;
    currentStatusFilter = document.getElementById('taskStatusFilter').value;
    displayTasks();
}

function displayTasks() {
    let filteredTasks = [...allTasks];
    
    if (currentProjectFilter) {
        filteredTasks = filteredTasks.filter(t => t.project_id == currentProjectFilter);
    }
    
    if (currentStatusFilter) {
        filteredTasks = filteredTasks.filter(t => t.status === currentStatusFilter);
    }
    
    const tasksBoard = document.getElementById('tasksBoard');
    
    if (filteredTasks.length === 0) {
        tasksBoard.innerHTML = '<div class="text-center text-gray-500 py-8">No tasks found. Create your first task!</div>';
        return;
    }
    
    // Notion-like task list
    tasksBoard.innerHTML = filteredTasks.map(task => {
        const priorityColors = {
            'low': 'bg-gray-100 text-gray-700',
            'medium': 'bg-blue-100 text-blue-700',
            'high': 'bg-orange-100 text-orange-700',
            'urgent': 'bg-red-100 text-red-700'
        };
        
        const statusColors = {
            'todo': 'bg-gray-100 text-gray-700',
            'in_progress': 'bg-blue-100 text-blue-700',
            'review': 'bg-yellow-100 text-yellow-700',
            'done': 'bg-green-100 text-green-700',
            'archived': 'bg-gray-100 text-gray-500'
        };
        
        const project = allProjects.find(p => p.id === task.project_id);
        const isOverdue = task.is_overdue;
        const dueDate = task.due_date ? new Date(task.due_date).toLocaleDateString() : null;
        
        return `
            <div class="bg-white rounded-lg border border-gray-200 hover:border-indigo-300 hover:shadow-md transition-all p-4 cursor-pointer task-item" 
                 onclick="viewTaskDetail(${task.id})"
                 draggable="true"
                 data-task-id="${task.id}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            ${task.is_pinned ? '<span class="text-yellow-500">📌</span>' : ''}
                            <h4 class="font-medium text-gray-900">${task.title}</h4>
                        </div>
                        ${task.description ? `<p class="text-sm text-gray-600 mb-2 line-clamp-2">${task.description}</p>` : ''}
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2 py-1 text-xs rounded-full ${statusColors[task.status] || 'bg-gray-100'}">
                                ${task.status.replace('_', ' ')}
                            </span>
                            <span class="px-2 py-1 text-xs rounded-full ${priorityColors[task.priority] || 'bg-gray-100'}">
                                ${task.priority}
                            </span>
                            ${project ? `<span class="px-2 py-1 text-xs rounded bg-indigo-50 text-indigo-700">${project.name}</span>` : ''}
                            ${task.tags && task.tags.length > 0 ? task.tags.map(tag => 
                                `<span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">${tag}</span>`
                            ).join('') : ''}
                        </div>
                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                            ${task.assigned_to ? `<span>👤 ${task.assigned_to.name || task.assigned_to.email}</span>` : '<span>Unassigned</span>'}
                            ${dueDate ? `<span class="${isOverdue ? 'text-red-600 font-medium' : ''}">📅 ${dueDate}</span>` : ''}
                            ${task.progress_percentage > 0 ? `<span>${task.progress_percentage}%</span>` : ''}
                        </div>
                    </div>
                    <div class="flex gap-2 ml-4">
                        <button onclick="event.stopPropagation(); editTask(${task.id})" 
                                class="text-indigo-600 hover:text-indigo-800 text-sm">Edit</button>
                        <button onclick="event.stopPropagation(); deleteTask(${task.id})" 
                                class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    // Add drag and drop handlers
    setupDragAndDrop();
}

// Create Task
async function createTask() {
    const form = document.getElementById('createTaskForm');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const projectId = document.getElementById('taskProjectId').value;
        const data = {
            title: document.getElementById('taskTitle').value,
            description: document.getElementById('taskDescription').value,
            status: document.getElementById('taskStatus').value,
            priority: document.getElementById('taskPriority').value,
            assigned_to: document.getElementById('taskAssignedTo').value || null,
            due_date: document.getElementById('taskDueDate').value || null,
            tags: document.getElementById('taskTags').value.split(',').map(t => t.trim()).filter(t => t),
            is_pinned: document.getElementById('taskIsPinned').checked
        };
        
        try {
            const response = await fetch(`${apiBase}/projects/${projectId}/tasks`, {
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
                alert('Task created successfully!');
                closeCreateTaskModal();
                loadTasks();
            } else {
                alert(result.message || 'Error creating task');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error creating task');
        }
    });
}

// Edit Task
async function editTask(taskId) {
    try {
        const response = await fetch(`${apiBase}/tasks/${taskId}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const result = await response.json();
            const task = result.data;
            
            // Populate edit form
            document.getElementById('editTaskId').value = task.id;
            document.getElementById('editTaskTitle').value = task.title;
            document.getElementById('editTaskDescription').value = task.description || '';
            document.getElementById('editTaskStatus').value = task.status;
            document.getElementById('editTaskPriority').value = task.priority;
            document.getElementById('editTaskAssignedTo').value = task.assigned_to?.id || '';
            document.getElementById('editTaskDueDate').value = task.due_date ? task.due_date.split('T')[0] : '';
            document.getElementById('editTaskTags').value = (task.tags || []).join(', ');
            document.getElementById('editTaskIsPinned').checked = task.is_pinned;
            
            document.getElementById('editTaskModal').classList.remove('hidden');
            setupEditTaskForm();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error loading task');
    }
}

// Update Task
async function updateTask() {
    const taskId = document.getElementById('editTaskId').value;
    const data = {
        title: document.getElementById('editTaskTitle').value,
        description: document.getElementById('editTaskDescription').value,
        status: document.getElementById('editTaskStatus').value,
        priority: document.getElementById('editTaskPriority').value,
        assigned_to: document.getElementById('editTaskAssignedTo').value || null,
        due_date: document.getElementById('editTaskDueDate').value || null,
        tags: document.getElementById('editTaskTags').value.split(',').map(t => t.trim()).filter(t => t),
        is_pinned: document.getElementById('editTaskIsPinned').checked
    };
    
    try {
        const response = await fetch(`${apiBase}/tasks/${taskId}`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            alert('Task updated successfully!');
            closeEditTaskModal();
            loadTasks();
        } else {
            alert(result.message || 'Error updating task');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error updating task');
    }
}

// Delete Task
async function deleteTask(taskId) {
    if (!confirm('Are you sure you want to delete this task?')) return;
    
    try {
        const response = await fetch(`${apiBase}/tasks/${taskId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            alert('Task deleted successfully!');
            loadTasks();
        } else {
            const result = await response.json();
            alert(result.message || 'Error deleting task');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error deleting task');
    }
}

// View Task Detail
function viewTaskDetail(taskId) {
    // Open task detail modal (Notion-like)
    window.location.href = `#task-${taskId}`;
    // TODO: Implement full task detail view
}

// Setup drag and drop
function setupDragAndDrop() {
    const taskItems = document.querySelectorAll('.task-item');
    taskItems.forEach(item => {
        item.addEventListener('dragstart', handleDragStart);
        item.addEventListener('dragover', handleDragOver);
        item.addEventListener('drop', handleDrop);
        item.addEventListener('dragend', handleDragEnd);
    });
}

let draggedElement = null;

function handleDragStart(e) {
    draggedElement = this;
    this.style.opacity = '0.5';
}

function handleDragOver(e) {
    e.preventDefault();
    if (this !== draggedElement) {
        this.style.borderTop = '2px solid #4F46E5';
    }
}

function handleDrop(e) {
    e.preventDefault();
    if (this !== draggedElement) {
        // Update positions via API
        // TODO: Implement position update
    }
    this.style.borderTop = '';
}

function handleDragEnd(e) {
    this.style.opacity = '1';
    document.querySelectorAll('.task-item').forEach(item => {
        item.style.borderTop = '';
    });
}

// Modal Functions
function showCreateTaskModal() {
    // Load projects for dropdown
    const projectSelect = document.getElementById('taskProjectId');
    projectSelect.innerHTML = '<option value="">Select Project</option>' + 
        allProjects.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
    
    document.getElementById('createTaskModal').classList.remove('hidden');
    createTask();
}

function closeCreateTaskModal() {
    document.getElementById('createTaskModal').classList.add('hidden');
    document.getElementById('createTaskForm').reset();
}

function setupEditTaskForm() {
    const form = document.getElementById('editTaskForm');
    form.onsubmit = (e) => {
        e.preventDefault();
        updateTask();
    };
}

function closeEditTaskModal() {
    document.getElementById('editTaskModal').classList.add('hidden');
    document.getElementById('editTaskForm').reset();
}

// Load project tasks
async function loadProjectTasks(projectId) {
    try {
        const response = await fetch(`${apiBase}/projects/${projectId}/tasks`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            // Show project detail with tasks
            // TODO: Implement project detail view
            console.log('Project tasks:', data);
        }
    } catch (error) {
        console.error('Error loading project tasks:', error);
    }
}

// Initialize - Show projects by default
document.addEventListener('DOMContentLoaded', function() {
    const projectsNav = document.querySelector('.nav-item');
    showSection('projects', projectsNav);
});
</script>

<!-- Create Task Modal -->
<div id="createTaskModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Task</h3>
            <form id="createTaskForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Project *</label>
                    <select id="taskProjectId" name="project_id" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title *</label>
                    <input type="text" id="taskTitle" name="title" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="taskDescription" name="description" rows="4"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="taskStatus" name="status"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="todo">To Do</option>
                            <option value="in_progress">In Progress</option>
                            <option value="review">Review</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Priority</label>
                        <select id="taskPriority" name="priority"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Assign To</label>
                        <select id="taskAssignedTo" name="assigned_to"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Unassigned</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Due Date</label>
                        <input type="date" id="taskDueDate" name="due_date"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tags (comma-separated)</label>
                    <input type="text" id="taskTags" name="tags" placeholder="urgent, frontend, bug"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="taskIsPinned" name="is_pinned" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="taskIsPinned" class="ml-2 text-sm text-gray-700">Pin this task</label>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeCreateTaskModal()" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div id="editTaskModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Task</h3>
            <form id="editTaskForm" class="space-y-4">
                <input type="hidden" id="editTaskId">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title *</label>
                    <input type="text" id="editTaskTitle" name="title" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="editTaskDescription" name="description" rows="4"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="editTaskStatus" name="status"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="todo">To Do</option>
                            <option value="in_progress">In Progress</option>
                            <option value="review">Review</option>
                            <option value="done">Done</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Priority</label>
                        <select id="editTaskPriority" name="priority"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Assign To</label>
                        <select id="editTaskAssignedTo" name="assigned_to"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Unassigned</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Due Date</label>
                        <input type="date" id="editTaskDueDate" name="due_date"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tags (comma-separated)</label>
                    <input type="text" id="editTaskTags" name="tags" placeholder="urgent, frontend, bug"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="editTaskIsPinned" name="is_pinned" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="editTaskIsPinned" class="ml-2 text-sm text-gray-700">Pin this task</label>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeEditTaskModal()" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Update Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>

