<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    protected $fillable = ['name', 'code'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($paymentType) {
            if (empty($paymentType->code)) {
                $paymentType->code = strtolower(str_replace(' ', '_', $paymentType->name));
            }
        });

        static::updating(function ($paymentType) {
            if (empty($paymentType->code)) {
                $paymentType->code = strtolower(str_replace(' ', '_', $paymentType->name));
            }
        });
    }

    /**
     * Get the code value for use in forms (always returns a string code)
     */
    public function getFormCodeAttribute()
    {
        return $this->code ?? strtolower(str_replace(' ', '_', $this->name));
    }
}

