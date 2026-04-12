<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('companies', 'letterhead_watermark_mode')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('letterhead_watermark_mode', 20)
                    ->nullable()
                    ->after('letterhead_watermark_text');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('companies', 'letterhead_watermark_mode')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('letterhead_watermark_mode');
            });
        }
    }
};

