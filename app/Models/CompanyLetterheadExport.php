<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CompanyLetterheadExport extends Model
{
    protected $fillable = [
        'company_id',
        'path',
        'file_name',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getPublicUrl(): ?string
    {
        if ($this->path === '' || $this->path === null) {
            return null;
        }
        if (!Storage::disk('public')->exists($this->path)) {
            return null;
        }

        return asset('storage/' . ltrim($this->path, '/'));
    }
}
