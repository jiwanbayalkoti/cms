<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('measurement_books')
            ->where('dimension_unit', 'ft_in')
            ->update(['dimension_unit' => 'ft']);
        DB::table('measurement_books')
            ->where('dimension_unit', 'm_cm')
            ->update(['dimension_unit' => 'm']);
    }

    public function down(): void
    {
        // Optional: reverse not critical
    }
};
