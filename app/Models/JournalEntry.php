<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\CompanyScoped;

class JournalEntry extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'entry_number',
        'entry_date',
        'description',
        'reference',
        'entry_type',
        'project_id',
        'total_debit',
        'total_credit',
        'is_posted',
        'posted_at',
        'posted_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'is_posted' => 'boolean',
    ];

    /**
     * Get the journal entry items.
     */
    public function items()
    {
        return $this->hasMany(JournalEntryItem::class)->orderBy('line_number');
    }

    /**
     * Get the company.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the project.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who posted this entry.
     */
    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if debits equal credits.
     */
    public function isBalanced()
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }

    /**
     * Generate entry number.
     */
    public static function generateEntryNumber($companyId = null)
    {
        $companyId = $companyId ?? \App\Support\CompanyContext::getActiveCompanyId();
        $year = date('Y');
        $lastEntry = self::where('company_id', $companyId)
            ->where('entry_number', 'like', "JE-{$year}-%")
            ->orderBy('entry_number', 'desc')
            ->first();

        if ($lastEntry) {
            $lastNumber = (int) substr($lastEntry->entry_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('JE-%s-%04d', $year, $newNumber);
    }
}
