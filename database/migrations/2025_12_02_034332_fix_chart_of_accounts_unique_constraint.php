<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            // Drop the old unique constraint on account_code
            $table->dropUnique(['account_code']);
            
            // Add composite unique constraint on company_id and account_code
            $table->unique(['company_id', 'account_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique(['company_id', 'account_code']);
            
            // Restore the old unique constraint on account_code
            $table->unique('account_code');
        });
    }
};
