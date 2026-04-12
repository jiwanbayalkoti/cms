<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('companies', 'letterhead_address_size')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->decimal('letterhead_address_size', 5, 2)
                    ->nullable()
                    ->after('letterhead_name_np_size');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('companies', 'letterhead_address_size')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('letterhead_address_size');
            });
        }
    }
};

