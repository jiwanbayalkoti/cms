<?php

namespace App\Models;

use App\Models\Traits\CompanyScoped;
use App\Models\Traits\ProjectScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillModule extends Model
{
    use HasFactory, CompanyScoped, ProjectScoped, SoftDeletes;

    protected $fillable = [
        'company_id',
        'project_id',
        'title',
        'version',
        'created_by',
        'approved_by',
        'status',
        'notes',
        'mb_number',
        'mb_date',
    ];

    protected $casts = [
        'mb_date' => 'date',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ARCHIVED = 'archived';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_SUBMITTED,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_ARCHIVED,
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(BillItem::class)->orderBy('sort_order');
    }

    public function aggregate()
    {
        return $this->hasOne(BillAggregate::class);
    }

    public function history()
    {
        return $this->hasMany(BillHistory::class)->orderByDesc('created_at');
    }

    public function canEdit(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    public function canApprove(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }
}
