<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentType;

class FixPaymentTypeCodes extends Command
{
    protected $signature = 'payment-types:fix-codes';
    protected $description = 'Ensure all payment types have codes';

    public function handle()
    {
        $types = PaymentType::all();
        
        foreach ($types as $type) {
            if (empty($type->code)) {
                $code = strtolower(str_replace(' ', '_', $type->name));
                $type->code = $code;
                $type->save();
                $this->info("Updated payment type '{$type->name}' with code '{$code}'");
            } else {
                $this->line("Payment type '{$type->name}' already has code '{$type->code}'");
            }
        }
        
        $this->info('Done!');
        return 0;
    }
}
