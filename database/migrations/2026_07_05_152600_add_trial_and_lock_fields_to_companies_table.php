<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->timestamp('trial_started_at')->nullable()->after('favicon');
            $table->timestamp('trial_ends_at')->nullable()->after('trial_started_at');
            $table->string('subscription_status', 30)->default('active')->after('trial_ends_at');
            $table->timestamp('locked_at')->nullable()->after('subscription_status');
            $table->string('lock_reason')->nullable()->after('locked_at');
            $table->timestamp('payment_required_email_sent_at')->nullable()->after('lock_reason');

            $table->index(['subscription_status', 'trial_ends_at']);
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['subscription_status', 'trial_ends_at']);
            $table->dropColumn([
                'trial_started_at',
                'trial_ends_at',
                'subscription_status',
                'locked_at',
                'lock_reason',
                'payment_required_email_sent_at',
            ]);
        });
    }
};
