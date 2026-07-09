<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('companies', 'paid_until')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->timestamp('paid_until')->nullable()->after('payment_required_email_sent_at');
                $table->index(['subscription_status', 'paid_until']);
            });
        }

        Schema::create('company_payment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 12, 2)->nullable();
            $table->unsignedSmallInteger('paid_for_days')->default(30);
            $table->string('payment_method')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('proof_path')->nullable();
            $table->text('note')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_payment_requests');

        if (Schema::hasColumn('companies', 'paid_until')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropIndex(['subscription_status', 'paid_until']);
                $table->dropColumn('paid_until');
            });
        }
    }
};
