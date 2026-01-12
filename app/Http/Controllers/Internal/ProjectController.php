<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects (filtered by role).
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // Verify user is a Metatech employee
        if (!$user || !$user->is_metatech_employee) {
            abort(403, 'Access denied. This page is only for Metatech employees.');
        }

        // Filter projects based on role
        $query = Project::with(['creator', 'users', 'tasks']);

        if ($user->canViewAllProjects()) {
            // Super Admin and Admin see all projects
            $projects = $query->orderBy('created_at', 'desc')->get();
        } else {
            // Users see only projects they're assigned to
            $projects = $query->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->orWhere('created_by', $user->id)
              ->orderBy('created_at', 'desc')
              ->get();
        }

        // Generate JWT token for API calls
        $token = JWTAuth::fromUser($user);

        return view('internal.projects.index', [
            'projects' => $projects,
            'api_token' => $token,
            'canCreate' => $user->canCreateProjects(),
            'canViewAll' => $user->canViewAllProjects(),
        ]);
    }

    /**
     * Show the form for creating a new project.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = Auth::user();
        
        if (!$user || !$user->is_metatech_employee) {
            abort(403, 'Access denied.');
        }

        if (!$user->canCreateProjects()) {
            abort(403, 'You do not have permission to create projects.');
        }

        $token = JWTAuth::fromUser($user);

        return view('internal.projects.create', [
            'api_token' => $token,
        ]);
    }

    /**
     * Store a newly created project.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !$user->is_metatech_employee || !$user->canCreateProjects()) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // For internal CRM, we might not need company_id
        // But keeping it optional for now (can be null for internal projects)
        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => 'active',
            'company_id' => null, // Internal projects don't belong to a company
            'created_by' => $user->id,
        ]);

        // Auto-assign creator to project
        $project->users()->attach($user->id, [
            'access_level' => 'admin',
            'granted_at' => now(),
            'granted_by' => $user->id,
        ]);

        return redirect()->route('internal.projects.index')
            ->with('success', 'Project created successfully!');
    }

    /**
     * Display the specified project.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $user = Auth::user();
        
        if (!$user || !$user->is_metatech_employee) {
            abort(403, 'Access denied.');
        }

        $project = Project::with(['creator', 'users', 'tasks.assignedTo'])->findOrFail($id);

        // Check access
        $hasAccess = false;
        if ($user->canViewAllProjects()) {
            $hasAccess = true;
        } elseif ($project->users->contains($user->id) || $project->created_by === $user->id) {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            abort(403, 'You do not have access to this project.');
        }

        $token = JWTAuth::fromUser($user);

        return view('internal.projects.show', [
            'project' => $project,
            'api_token' => $token,
            'canEdit' => $user->canViewAllProjects() || $project->created_by === $user->id,
        ]);
    }

    /**
     * Show the form for editing the specified project.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        if (!$user || !$user->is_metatech_employee) {
            abort(403, 'Access denied.');
        }

        $project = Project::findOrFail($id);

        // Only Super Admin/Admin or project creator can edit
        if (!$user->canViewAllProjects() && $project->created_by !== $user->id) {
            abort(403, 'You do not have permission to edit this project.');
        }

        $token = JWTAuth::fromUser($user);

        return view('internal.projects.edit', [
            'project' => $project,
            'api_token' => $token,
        ]);
    }

    /**
     * Update the specified project.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user || !$user->is_metatech_employee) {
            abort(403, 'Access denied.');
        }

        $project = Project::findOrFail($id);

        // Only Super Admin/Admin or project creator can update
        if (!$user->canViewAllProjects() && $project->created_by !== $user->id) {
            abort(403, 'You do not have permission to update this project.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,archived,completed',
        ]);

        $project->update($request->only(['name', 'description', 'status']));

        return redirect()->route('internal.projects.show', $project->id)
            ->with('success', 'Project updated successfully!');
    }
}
