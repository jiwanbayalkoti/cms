<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            if (!Schema::hasColumn('loans', 'is_closed')) {
                $table->boolean('is_closed')->default(false)->after('loan_date');
            }
            if (!Schema::hasColumn('loans', 'closed_at')) {
                $table->date('closed_at')->nullable()->after('is_closed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            if (Schema::hasColumn('loans', 'closed_at')) {
                $table->dropColumn('closed_at');
            }
            if (Schema::hasColumn('loans', 'is_closed')) {
                $table->dropColumn('is_closed');
            }
        });
    }
};

