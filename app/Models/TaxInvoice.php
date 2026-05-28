<?php

namespace App\Models;

use App\Models\Traits\CompanyScoped;
use App\Models\Traits\ProjectScoped;
use App\Services\AmountToWordsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxInvoice extends Model
{
    use HasFactory, CompanyScoped, ProjectScoped;

    protected $fillable = [
        'company_id',
        'invoice_number',
        'invoice_date',
        'transaction_date_bs',
        'customer_id',
        'project_id',
        'buyer_name',
        'buyer_address',
        'buyer_pan',
        'buyer_phone',
        'payment_method',
        'subtotal',
        'discount_percent',
        'discount_amount',
        'taxable_amount',
        'vat_percent',
        'vat_amount',
        'grand_total',
        'amount_in_words',
        'template',
        'status',
        'notes',
        'reference_number',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'vat_percent' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public static function templateOptions(): array
    {
        return [
            'nepali_annex5' => 'कर विजक (Nepali Annex-5)',
            'english_standard' => 'Tax Invoice (Modern — Energetic style)',
        ];
    }

    public static function paymentMethodOptions(): array
    {
        return [
            'cash' => 'Cash / नगद',
            'cheque' => 'Cheque / चेक',
            'credit' => 'Credit / उधारो',
            'other' => 'Other / अन्य',
        ];
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    public function isPrintable(): bool
    {
        return $this->status === 'issued';
    }

    public static function calculateTotals(array $items, float $discountPercent, float $discountAmount, float $vatPercent): array
    {
        $subtotal = 0.0;
        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $subtotal += round($qty * $price, 2);
        }

        $discountFromPercent = $discountPercent > 0 ? round($subtotal * $discountPercent / 100, 2) : 0;
        $discount = $discountAmount > 0 ? $discountAmount : $discountFromPercent;
        $discount = min($discount, $subtotal);

        $taxable = round($subtotal - $discount, 2);
        $vat = round($taxable * $vatPercent / 100, 2);
        $grand = round($taxable + $vat, 2);

        return compact('subtotal', 'discount', 'taxable', 'vat', 'grand');
    }

    public static function suggestInvoiceNumber(int $companyId): string
    {
        $last = self::where('company_id', $companyId)
            ->orderByDesc('id')
            ->value('invoice_number');

        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            return str_pad((string) ((int) $m[1] + 1), strlen($m[1]), '0', STR_PAD_LEFT);
        }

        return '001';
    }

    public function items()
    {
        return $this->hasMany(TaxInvoiceItem::class)->orderBy('line_number');
    }

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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function refreshAmountInWords(): void
    {
        $this->amount_in_words = app(AmountToWordsService::class)->rupeesPaisaToWords((float) $this->grand_total);
    }

    public function splitAmount(float $amount): array
    {
        $rupees = (int) floor($amount);
        $paisa = (int) round(($amount - $rupees) * 100);

        return ['rupees' => $rupees, 'paisa' => $paisa];
    }
}
