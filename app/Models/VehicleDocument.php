<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VehicleDocument extends Model
{
    protected $fillable = [
        'company_id',
        'vehicle_id',
        'uploaded_by',
        'document_type',
        'label',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'expiry_date',
        'service_date',
        'policy_number',
        'cost',
        'notes',
        'is_current',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'service_date' => 'date',
        'cost' => 'decimal:2',
        'is_current' => 'boolean',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getTypeLabel(): string
    {
        return Vehicle::documentTypeDefinitions()[$this->document_type]['label']
            ?? ucfirst(str_replace('_', ' ', $this->document_type));
    }

    public function getUrl(): string
    {
        return Storage::url($this->file_path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function getFileSizeFormatted(): string
    {
        $bytes = (int) ($this->file_size ?? 0);
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return round($bytes / 1048576, 1) . ' MB';
    }
}
