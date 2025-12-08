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
        Schema::table('suppliers', function (Blueprint $table) {
            // Remove SWIFT Code and IBAN fields
            if (Schema::hasColumn('suppliers', 'swift_code')) {
                $table->dropColumn('swift_code');
            }
            if (Schema::hasColumn('suppliers', 'iban')) {
                $table->dropColumn('iban');
            }
            // Add QR code image field
            if (!Schema::hasColumn('suppliers', 'qr_code_image')) {
                $table->string('qr_code_image')->nullable()->after('branch_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Restore SWIFT Code and IBAN
            if (!Schema::hasColumn('suppliers', 'swift_code')) {
                $table->string('swift_code')->nullable()->after('branch_address');
            }
            if (!Schema::hasColumn('suppliers', 'iban')) {
                $table->string('iban')->nullable()->after('swift_code');
            }
            // Remove QR code image field
            if (Schema::hasColumn('suppliers', 'qr_code_image')) {
                $table->dropColumn('qr_code_image');
            }
        });
    }
};
