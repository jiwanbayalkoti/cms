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
        Schema::table('construction_materials', function (Blueprint $table) {
            $table->dropForeign(['stakeholder_id']);
            $table->dropIndex(['stakeholder_id']);
            $table->dropColumn('stakeholder_id');
        });
        
        Schema::dropIfExists('stakeholders');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('stakeholders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('name');
            $table->string('contact')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->index('company_id');
        });
        
        Schema::table('construction_materials', function (Blueprint $table) {
            $table->unsignedBigInteger('stakeholder_id')->nullable()->after('purchased_by_id');
            $table->foreign('stakeholder_id')->references('id')->on('stakeholders')->onDelete('set null');
            $table->index('stakeholder_id');
        });
    }
};
