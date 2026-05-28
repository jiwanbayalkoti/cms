<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_invoice_id',
        'line_number',
        'hs_code',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'line_amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'line_amount' => 'decimal:2',
    ];

    public function taxInvoice()
    {
        return $this->belongsTo(TaxInvoice::class);
    }
}
