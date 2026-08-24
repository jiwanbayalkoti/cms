<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleDocumentRenewal extends Model
{
    protected $fillable = [
        'company_id',
        'vehicle_id',
        'vehicle_document_id',
        'document_type',
        'previous_expiry',
        'new_expiry',
        'service_date',
        'previous_policy_number',
        'new_policy_number',
        'cost',
        'notes',
        'renewed_by',
        'renewed_at',
    ];

    protected $casts = [
        'previous_expiry' => 'date',
        'new_expiry' => 'date',
        'service_date' => 'date',
        'cost' => 'decimal:2',
        'renewed_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function document()
    {
        return $this->belongsTo(VehicleDocument::class, 'vehicle_document_id');
    }

    public function renewer()
    {
        return $this->belongsTo(User::class, 'renewed_by');
    }

    public function getTypeLabel(): string
    {
        return Vehicle::documentTypeDefinitions()[$this->document_type]['label']
            ?? ucfirst(str_replace('_', ' ', $this->document_type));
    }
}
