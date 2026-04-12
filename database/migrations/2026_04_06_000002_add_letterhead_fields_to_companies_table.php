<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('letterhead_template')->nullable()->after('favicon');
            $table->string('letterhead_primary_color', 20)->nullable()->after('letterhead_template');
            $table->string('letterhead_secondary_color', 20)->nullable()->after('letterhead_primary_color');
            $table->string('letterhead_font_family', 100)->nullable()->after('letterhead_secondary_color');
            $table->string('letterhead_header_alignment', 20)->nullable()->after('letterhead_font_family');
            $table->string('letterhead_tagline')->nullable()->after('letterhead_header_alignment');
            $table->text('letterhead_footer_text')->nullable()->after('letterhead_tagline');
            $table->boolean('letterhead_show_watermark')->default(false)->after('letterhead_footer_text');
            $table->unsignedTinyInteger('letterhead_watermark_opacity')->default(10)->after('letterhead_show_watermark');
            $table->boolean('letterhead_show_border')->default(true)->after('letterhead_watermark_opacity');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'letterhead_template',
                'letterhead_primary_color',
                'letterhead_secondary_color',
                'letterhead_font_family',
                'letterhead_header_alignment',
                'letterhead_tagline',
                'letterhead_footer_text',
                'letterhead_show_watermark',
                'letterhead_watermark_opacity',
                'letterhead_show_border',
            ]);
        });
    }
};

