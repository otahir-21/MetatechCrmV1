<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'first_name',
        'last_name',
        'company_name',
        'subdomain',
        'is_metatech_employee',
        'department',
        'designation',
        'joined_date',
        'status',
        'status_reason',
        'blocked_at',
        'blocked_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'blocked_at' => 'datetime',
            'is_metatech_employee' => 'boolean',
            'joined_date' => 'date',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'email' => $this->email,
            'subdomain' => $this->subdomain,
            'company_name' => $this->company_name,
            'is_metatech_employee' => $this->is_metatech_employee,
        ];
    }

    /**
     * Check if user is super admin.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is company super admin (has company_name and subdomain).
     *
     * @return bool
     */
    public function isCompanySuperAdmin(): bool
    {
        return $this->role === 'super_admin' 
            && $this->company_name !== null 
            && $this->subdomain !== null
            && !$this->is_metatech_employee;
    }

    /**
     * Check if user is product owner (System Super Admin without company).
     *
     * @return bool
     */
    public function isProductOwner(): bool
    {
        return $this->role === 'super_admin' 
            && $this->company_name === null 
            && $this->subdomain === null
            && !$this->is_metatech_employee;
    }

    /**
     * Check if user is internal super admin (Metatech employee with super_admin role).
     *
     * @return bool
     */
    public function isInternalSuperAdmin(): bool
    {
        return $this->role === 'super_admin' && (bool) $this->is_metatech_employee;
    }

    /**
     * Check if user is internal admin (Metatech employee with admin role).
     *
     * @return bool
     */
    public function isInternalAdmin(): bool
    {
        return $this->role === 'admin' && (bool) $this->is_metatech_employee;
    }

    /**
     * Check if user is an internal Metatech employee (any role).
     *
     * @return bool
     */
    public function isInternalEmployee(): bool
    {
        return $this->is_metatech_employee === true;
    }

    /**
     * Check if user can manage internal employees (Product Owner, Internal Super Admin, or Internal Admin).
     *
     * @return bool
     */
    public function canManageInternalEmployees(): bool
    {
        return $this->isProductOwner() || $this->isInternalSuperAdmin() || $this->isInternalAdmin();
    }

    /**
     * Check if user can view all projects (Super Admin or Admin).
     *
     * @return bool
     */
    public function canViewAllProjects(): bool
    {
        if (!$this->is_metatech_employee) {
            return false;
        }
        return $this->role === 'super_admin' || $this->role === 'admin';
    }

    /**
     * Check if user can create projects.
     *
     * @return bool
     */
    public function canCreateProjects(): bool
    {
        if (!$this->is_metatech_employee) {
            return false;
        }
        // All internal employees can create projects
        return true;
    }

    /**
     * Check if user can manage all users (Super Admin only).
     *
     * @return bool
     */
    public function canManageAllUsers(): bool
    {
        if (!$this->is_metatech_employee) {
            return false;
        }
        return $this->role === 'super_admin';
    }

    /**
     * Check if user can manage roles (Super Admin only).
     *
     * @return bool
     */
    public function canManageRoles(): bool
    {
        if (!$this->is_metatech_employee) {
            return false;
        }
        return $this->role === 'super_admin';
    }

    /**
     * Check if user can access system settings (Super Admin only).
     *
     * @return bool
     */
    public function canManageSettings(): bool
    {
        if (!$this->is_metatech_employee) {
            return false;
        }
        return $this->role === 'super_admin';
    }

    // ============================================
    // SPATIE ROLE HELPER METHODS
    // ============================================

    /**
     * Check if user has a Metatech role.
     *
     * @param string $role Role name without prefix (e.g., 'sales', 'admin')
     * @return bool
     */
    public function hasMetatechRole(string $role): bool
    {
        if (!$this->is_metatech_employee) {
            return false;
        }
        $fullRoleName = 'metatech.' . $role;
        return $this->hasRole($fullRoleName);
    }

    /**
     * Check if user has a Client role.
     *
     * @param string $role Role name without prefix (e.g., 'owner', 'admin', 'staff')
     * @return bool
     */
    public function hasClientRole(string $role): bool
    {
        if ($this->is_metatech_employee) {
            return false;
        }
        $fullRoleName = 'client.' . $role;
        return $this->hasRole($fullRoleName);
    }

    /**
     * Get user's role name without prefix.
     *
     * @return string|null
     */
    public function getRoleName(): ?string
    {
        $role = $this->roles->first();
        if (!$role) {
            return $this->role; // Fallback to enum role
        }
        
        return str_replace(['metatech.', 'client.'], '', $role->name);
    }

    /**
     * Get user's full role name (with prefix).
     *
     * @return string|null
     */
    public function getFullRoleName(): ?string
    {
        $role = $this->roles->first();
        if (!$role) {
            return $this->role; // Fallback to enum role
        }
        
        return $role->name;
    }

    /**
     * Check if user has any of the given Metatech roles.
     *
     * @param array|string $roles Role names without prefix
     * @return bool
     */
    public function hasAnyMetatechRole($roles): bool
    {
        if (!$this->is_metatech_employee) {
            return false;
        }
        
        $roles = is_array($roles) ? $roles : [$roles];
        $fullRoleNames = array_map(fn($role) => 'metatech.' . $role, $roles);
        
        return $this->hasAnyRole($fullRoleNames);
    }

    /**
     * Check if user has any of the given Client roles.
     *
     * @param array|string $roles Role names without prefix
     * @return bool
     */
    public function hasAnyClientRole($roles): bool
    {
        if ($this->is_metatech_employee) {
            return false;
        }
        
        $roles = is_array($roles) ? $roles : [$roles];
        $fullRoleNames = array_map(fn($role) => 'client.' . $role, $roles);
        
        return $this->hasAnyRole($fullRoleNames);
    }

    /**
     * Check if user is Metatech Super Admin (using Spatie role).
     *
     * @return bool
     */
    public function isMetatechSuperAdmin(): bool
    {
        return $this->hasMetatechRole('super_admin');
    }

    /**
     * Check if user is Metatech Admin (using Spatie role).
     *
     * @return bool
     */
    public function isMetatechAdmin(): bool
    {
        return $this->hasMetatechRole('admin');
    }

    /**
     * Check if user is Client Owner (using Spatie role).
     *
     * @return bool
     */
    public function isClientOwner(): bool
    {
        return $this->hasClientRole('owner');
    }

    /**
     * Check if user is Client Admin (using Spatie role).
     *
     * @return bool
     */
    public function isClientAdmin(): bool
    {
        return $this->hasClientRole('admin');
    }

    /**
     * Check if user is Client Staff (using Spatie role).
     *
     * @return bool
     */
    public function isClientStaff(): bool
    {
        return $this->hasClientRole('staff');
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function tasks()
    {
        return $this->hasMany(\App\Models\Task::class, 'assigned_to');
    }

    public function createdTasks()
    {
        return $this->hasMany(\App\Models\Task::class, 'created_by');
    }
}
