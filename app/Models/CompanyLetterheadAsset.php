<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyLetterheadAsset extends Model
{
    protected $fillable = [
        'company_id',
        'kind',
        'label',
        'path',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getUrl(): ?string
    {
        if (empty($this->path)) {
            return null;
        }

        $rel = str_replace('\\', '/', ltrim((string) $this->path, '/'));

        return asset('storage/'.$rel);
    }

    public function getAbsolutePath(): ?string
    {
        if (empty($this->path)) {
            return null;
        }
        $p = storage_path('app/public/' . ltrim($this->path, '/'));

        return is_file($p) ? $p : null;
    }
}
