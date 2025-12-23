<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'company_id',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-sync is_admin with role when role changes
        static::saving(function ($user) {
            if ($user->isDirty('role')) {
                $user->is_admin = in_array($user->role, ['admin', 'super_admin']);
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is an admin (admin or super_admin)
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']) || $this->is_admin;
    }

    /**
     * Check if user is a regular user
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Get user's role with fallback to is_admin
     */
    public function getRole(): string
    {
        if ($this->role) {
            return $this->role;
        }
        
        // Fallback to legacy is_admin
        return $this->is_admin ? 'admin' : 'user';
    }

    /**
     * Get accessible project IDs for this user
     * Returns null if user has access to all projects (super admin only)
     * Returns array of project IDs if user has specific project assignments
     * Returns empty array if user has no project assignments (no access to any records)
     */
    public function getAccessibleProjectIds(): ?array
    {
        // Super admins have access to all projects
        if ($this->isSuperAdmin()) {
            return null;
        }

        // Get assigned projects
        $assignedProjectIds = $this->projects()->pluck('projects.id')->all();

        // If user has no project assignments, return empty array (no access to any records)
        if (empty($assignedProjectIds)) {
            return [];
        }

        // User has specific project assignments
        return $assignedProjectIds;
    }

    /**
     * Check if user has access to a specific project
     */
    public function hasProjectAccess(int $projectId): bool
    {
        // Super admins have access to all projects
        if ($this->isSuperAdmin()) {
            return true;
        }

        $accessibleProjectIds = $this->getAccessibleProjectIds();

        // If null, user has access to all projects in their company
        if ($accessibleProjectIds === null) {
            return true;
        }

        // Check if project is in accessible list
        return in_array($projectId, $accessibleProjectIds);
    }

    /**
     * Get query scope for filtering projects by user access
     */
    public function scopeAccessibleProjects($query)
    {
        $accessibleProjectIds = $this->getAccessibleProjectIds();

        // If null, no restriction (user can see all projects in their company)
        if ($accessibleProjectIds === null) {
            return $query;
        }

        // Filter to only accessible projects
        return $query->whereIn('id', $accessibleProjectIds);
    }
}
