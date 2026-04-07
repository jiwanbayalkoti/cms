<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 150);
            $table->string('method', 10)->nullable();
            $table->string('route_name')->nullable();
            $table->text('url')->nullable();
            $table->string('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('route_name');
            $table->index('method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};

