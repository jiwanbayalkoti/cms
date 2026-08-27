<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_documents', function (Blueprint $table) {
            // Period the document belongs to; stored as the first day of the month.
            $table->date('document_date')->nullable()->after('label');
            $table->index(['company_id', 'document_date']);
        });
    }

    public function down(): void
    {
        Schema::table('company_documents', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'document_date']);
            $table->dropColumn('document_date');
        });
    }
};
