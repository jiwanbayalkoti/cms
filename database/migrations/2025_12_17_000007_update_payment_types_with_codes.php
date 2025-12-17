<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update any payment types that don't have codes
        $paymentTypes = DB::table('payment_types')->whereNull('code')->orWhere('code', '')->get();
        
        foreach ($paymentTypes as $type) {
            $code = strtolower(str_replace(' ', '_', $type->name));
            DB::table('payment_types')
                ->where('id', $type->id)
                ->update(['code' => $code]);
        }
    }
    
    public function down(): void
    {
        // No rollback needed
    }
};

