<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('companies', 'trial_days_override')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->unsignedSmallInteger('trial_days_override')->nullable()->after('trial_ends_at');
            });
        }

        DB::table('app_settings')->updateOrInsert(
            ['key' => 'trial_days'],
            [
                'value' => '7',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        if (Schema::hasColumn('companies', 'trial_days_override')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('trial_days_override');
            });
        }

        if (Schema::hasTable('app_settings')) {
            Schema::dropIfExists('app_settings');
        }
    }
};
