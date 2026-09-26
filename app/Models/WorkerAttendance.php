<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;

class WorkerAttendance extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'worker_id',
        'project_id',
        'date',
        'status',
        'overtime_hours',
        'notes',
        'marked_by',
    ];

    protected $casts = [
        'date' => 'date',
        'overtime_hours' => 'decimal:2',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function marker()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'present' => 'Present',
            'absent' => 'Absent',
            'half_day' => 'Half Day',
            'leave' => 'Leave',
            default => ucfirst($this->status),
        };
    }

    public function getDayCreditAttribute(): float
    {
        return match ($this->status) {
            'present' => 1.0,
            'half_day' => 0.5,
            default => 0.0,
        };
    }
}
