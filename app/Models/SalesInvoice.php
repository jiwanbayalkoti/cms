<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;
use App\Models\Traits\ProjectScoped;

class SalesInvoice extends Model
{
    use HasFactory, CompanyScoped, ProjectScoped;

    protected $fillable = [
        'company_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'customer_id',
        'project_id',
        'reference_number',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'received_amount',
        'balance_amount',
        'status',
        'payment_status',
        'bank_account_id',
        'notes',
        'terms',
        'journal_entry_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function items()
    {
        return $this->hasMany(SalesInvoiceItem::class)->orderBy('line_number');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function generateInvoiceNumber($companyId = null)
    {
        $companyId = $companyId ?? \App\Support\CompanyContext::getActiveCompanyId();
        $year = date('Y');
        $lastInvoice = self::where('company_id', $companyId)
            ->where('invoice_number', 'like', "SI-{$year}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('SI-%s-%04d', $year, $newNumber);
    }
}
