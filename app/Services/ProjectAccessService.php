<?php

namespace App\Services;

use App\Models\User;
use App\Models\Project;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class ProjectAccessService
{
    /**
     * Grant project access to a user.
     *
     * @param int $projectId
     * @param int $userId
     * @param string $accessLevel
     * @param User $grantedBy
     * @return void
     * @throws \Exception
     */
    public function grantProjectAccess(int $projectId, int $userId, string $accessLevel, User $grantedBy): void
    {
        // Verify grantor is Company Super Admin or Project Admin
        $project = Project::findOrFail($projectId);

        // Verify user belongs to same company
        $user = User::findOrFail($userId);
        if ($user->subdomain !== $grantedBy->subdomain || $user->company_name !== $grantedBy->company_name) {
            throw new \Exception('User must belong to the same company', 403);
        }

        // Get company
        $company = Company::where('subdomain', $grantedBy->subdomain)->firstOrFail();

        // Verify project belongs to company
        if ($project->company_id !== $company->id) {
            throw new \Exception('Project does not belong to your company', 403);
        }

        // Verify grantor has permission
        if (!$grantedBy->isCompanySuperAdmin() && !$this->isProjectAdmin($grantedBy, $projectId)) {
            throw new \Exception('You do not have permission to grant access to this project', 403);
        }

        // Grant access
        DB::table('project_user')->updateOrInsert(
            ['project_id' => $projectId, 'user_id' => $userId],
            [
                'access_level' => $accessLevel,
                'granted_by' => $grantedBy->id,
                'granted_at' => now(),
            ]
        );
    }

    /**
     * Revoke project access from a user.
     *
     * @param int $projectId
     * @param int $userId
     * @param User $revokedBy
     * @return void
     * @throws \Exception
     */
    public function revokeProjectAccess(int $projectId, int $userId, User $revokedBy): void
    {
        $project = Project::findOrFail($projectId);
        $company = Company::where('subdomain', $revokedBy->subdomain)->firstOrFail();

        if ($project->company_id !== $company->id) {
            throw new \Exception('Project does not belong to your company', 403);
        }

        if (!$revokedBy->isCompanySuperAdmin() && !$this->isProjectAdmin($revokedBy, $projectId)) {
            throw new \Exception('You do not have permission to revoke access from this project', 403);
        }

        DB::table('project_user')
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Get user's accessible projects.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserProjects(User $user)
    {
        // Company Super Admin can access all projects in their company
        if ($user->isCompanySuperAdmin()) {
            $company = Company::where('subdomain', $user->subdomain)->first();
            if ($company) {
                return Project::where('company_id', $company->id)->get();
            }
            return collect([]);
        }

        // Regular users can only access projects they have explicit access to
        $company = Company::where('subdomain', $user->subdomain)->first();
        if (!$company) {
            return collect([]);
        }

        return Project::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('company_id', $company->id)
        ->get();
    }

    /**
     * Check if user can access a project.
     *
     * @param User $user
     * @param int $projectId
     * @return bool
     */
    public function canUserAccessProject(User $user, int $projectId): bool
    {
        $project = Project::find($projectId);
        if (!$project) {
            return false;
        }

        $company = Company::where('subdomain', $user->subdomain)->first();
        if (!$company || $project->company_id !== $company->id) {
            return false;
        }

        // Company Super Admin can access all projects
        if ($user->isCompanySuperAdmin()) {
            return true;
        }

        // Check if user has explicit access
        return DB::table('project_user')
            ->where('project_id', $projectId)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Check if user is project admin.
     *
     * @param User $user
     * @param int $projectId
     * @return bool
     */
    public function isProjectAdmin(User $user, int $projectId): bool
    {
        if ($user->isCompanySuperAdmin()) {
            return true;
        }

        $access = DB::table('project_user')
            ->where('project_id', $projectId)
            ->where('user_id', $user->id)
            ->value('access_level');

        return $access === 'admin';
    }

    /**
     * Get project access level for user.
     *
     * @param User $user
     * @param int $projectId
     * @return string|null
     */
    public function getUserProjectAccessLevel(User $user, int $projectId): ?string
    {
        if ($user->isCompanySuperAdmin()) {
            return 'admin';
        }

        return DB::table('project_user')
            ->where('project_id', $projectId)
            ->where('user_id', $user->id)
            ->value('access_level');
    }
}

