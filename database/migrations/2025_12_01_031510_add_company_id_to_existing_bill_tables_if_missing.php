<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Removed bill_categories and bill_subcategories tables - no longer used
    }

    public function down(): void
    {
        // This migration fixes existing tables - no rollback needed
    }
};
