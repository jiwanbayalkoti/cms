<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;
use Illuminate\Support\Str;

class PermissionRole extends Model
{
    use HasFactory, CompanyScoped;

    public const TYPE_ADMIN_PACK = 'admin_pack';
    public const TYPE_STAFF = 'staff';

    public const LOGIN_ROLES = [
        'admin' => 'Company Admin',
        'user' => 'User',
        'site_engineer' => 'Site Engineer',
    ];

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'type',
        'description',
        'is_active',
        'created_by',
        'login_role',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (PermissionRole $role) {
            if (!$role->type) {
                $role->type = self::TYPE_STAFF;
            }
            if (!$role->slug && $role->name) {
                $role->slug = Str::slug($role->name);
            }
        });
    }

    public function modules()
    {
        return $this->hasMany(PermissionRoleModule::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'permission_role_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isAdminPack(): bool
    {
        return $this->type === self::TYPE_ADMIN_PACK;
    }

    public function isLoginRole(): bool
    {
        return !empty($this->login_role);
    }

    public function loginRoleValue(): string
    {
        if ($this->login_role) {
            return $this->login_role;
        }

        if ($this->isAdminPack() || $this->slug === 'company-admin') {
            return 'admin';
        }

        if (in_array($this->slug, ['site-engineer', 'site_engineer'], true)) {
            return 'site_engineer';
        }

        return 'user';
    }
}
