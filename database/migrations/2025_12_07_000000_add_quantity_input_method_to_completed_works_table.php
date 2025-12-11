<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('completed_works', function (Blueprint $table) {
            if (!Schema::hasColumn('completed_works', 'quantity_input_method')) {
                $table->enum('quantity_input_method', ['dimensions', 'direct'])->default('dimensions')->after('height');
            }
        });
    }

    public function down(): void
    {
        Schema::table('completed_works', function (Blueprint $table) {
            if (Schema::hasColumn('completed_works', 'quantity_input_method')) {
                $table->dropColumn('quantity_input_method');
            }
        });
    }
};

