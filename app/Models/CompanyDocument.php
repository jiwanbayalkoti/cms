<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CompanyDocument extends Model
{
    protected $fillable = [
        'company_id',
        'uploaded_by',
        'document_type',
        'label',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'notes',
    ];

    public static array $types = [
        'registration_certificate' => 'Company Registration Certificate',
        'pan'                      => 'PAN Certificate',
        'ward_certificate'         => 'Ward Certificate',
        'tax_clearance'            => 'Tax Clearance',
        'vat_certificate'          => 'VAT Certificate',
        'bank_document'            => 'Bank Document',
        'audit_report'             => 'Audit Report',
        'other'                    => 'Other',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getTypeLabel(): string
    {
        return self::$types[$this->document_type] ?? ucfirst(str_replace('_', ' ', $this->document_type));
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
        $bytes = $this->file_size ?? 0;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}
