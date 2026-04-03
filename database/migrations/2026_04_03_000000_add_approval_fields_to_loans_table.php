<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            if (! Schema::hasColumn('loans', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (! Schema::hasColumn('loans', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        // Existing rows: treat as already approved so behaviour stays consistent.
        DB::table('loans')->whereNull('approved_at')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('loans')->where('id', $row->id)->update([
                    'approved_at' => $row->updated_at ?? $row->created_at ?? now(),
                    'approved_by' => $row->updated_by ?? $row->created_by,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            if (Schema::hasColumn('loans', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }
            if (Schema::hasColumn('loans', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
        });
    }
};
