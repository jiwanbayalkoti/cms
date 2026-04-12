<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('companies', 'letterhead_address_text')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('letterhead_address_text', 255)
                    ->nullable()
                    ->after('letterhead_tagline');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('companies', 'letterhead_address_text')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('letterhead_address_text');
            });
        }
    }
};
