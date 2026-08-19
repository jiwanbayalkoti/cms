<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionRoleModule extends Model
{
    protected $fillable = [
        'permission_role_id',
        'module_key',
        'can_view',
        'can_create',
        'can_update',
        'can_delete',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_create' => 'boolean',
        'can_update' => 'boolean',
        'can_delete' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(PermissionRole::class, 'permission_role_id');
    }

    public function allows(string $action): bool
    {
        return match ($action) {
            'view' => (bool) $this->can_view,
            'create' => (bool) $this->can_create,
            'update' => (bool) $this->can_update,
            'delete' => (bool) $this->can_delete,
            default => false,
        };
    }
}
