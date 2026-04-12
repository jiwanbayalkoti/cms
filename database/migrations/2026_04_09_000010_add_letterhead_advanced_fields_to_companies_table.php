<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addIfMissing = function (string $column, callable $add): void {
            if (!Schema::hasColumn('companies', $column)) {
                Schema::table('companies', function (Blueprint $table) use ($add) {
                    $add($table);
                });
            }
        };

        $addIfMissing('letterhead_name_en_size', fn (Blueprint $table) => $table->decimal('letterhead_name_en_size', 5, 2)->nullable()->after('letterhead_tagline'));
        $addIfMissing('letterhead_name_np_size', fn (Blueprint $table) => $table->decimal('letterhead_name_np_size', 5, 2)->nullable()->after('letterhead_name_en_size'));
        $addIfMissing('letterhead_name_letter_spacing', fn (Blueprint $table) => $table->decimal('letterhead_name_letter_spacing', 5, 2)->nullable()->after('letterhead_name_np_size'));
        $addIfMissing('letterhead_name_line_height', fn (Blueprint $table) => $table->decimal('letterhead_name_line_height', 5, 2)->nullable()->after('letterhead_name_letter_spacing'));
        $addIfMissing('letterhead_name_en_color', fn (Blueprint $table) => $table->string('letterhead_name_en_color', 20)->nullable()->after('letterhead_name_line_height'));
        $addIfMissing('letterhead_name_np_color', fn (Blueprint $table) => $table->string('letterhead_name_np_color', 20)->nullable()->after('letterhead_name_en_color'));
        $addIfMissing('letterhead_address_color', fn (Blueprint $table) => $table->string('letterhead_address_color', 20)->nullable()->after('letterhead_name_np_color'));
        $addIfMissing('letterhead_name_font_style', fn (Blueprint $table) => $table->string('letterhead_name_font_style', 20)->nullable()->after('letterhead_address_color'));
        $addIfMissing('letterhead_name_en_align', fn (Blueprint $table) => $table->string('letterhead_name_en_align', 10)->nullable()->after('letterhead_name_font_style'));
        $addIfMissing('letterhead_name_np_align', fn (Blueprint $table) => $table->string('letterhead_name_np_align', 10)->nullable()->after('letterhead_name_en_align'));
        $addIfMissing('letterhead_address_align', fn (Blueprint $table) => $table->string('letterhead_address_align', 10)->nullable()->after('letterhead_name_np_align'));
        $addIfMissing('letterhead_meta_chs_align', fn (Blueprint $table) => $table->string('letterhead_meta_chs_align', 10)->nullable()->after('letterhead_address_align'));
        $addIfMissing('letterhead_meta_ps_align', fn (Blueprint $table) => $table->string('letterhead_meta_ps_align', 10)->nullable()->after('letterhead_meta_chs_align'));
        $addIfMissing('letterhead_meta_date_align', fn (Blueprint $table) => $table->string('letterhead_meta_date_align', 10)->nullable()->after('letterhead_meta_ps_align'));
        $addIfMissing('letterhead_layout_json', fn (Blueprint $table) => $table->longText('letterhead_layout_json')->nullable()->after('letterhead_meta_date_align'));
    }

    public function down(): void
    {
        $dropIfExists = function (string $column): void {
            if (Schema::hasColumn('companies', $column)) {
                Schema::table('companies', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        };
        $dropIfExists('letterhead_name_en_size');
        $dropIfExists('letterhead_name_np_size');
        $dropIfExists('letterhead_name_letter_spacing');
        $dropIfExists('letterhead_name_line_height');
        $dropIfExists('letterhead_name_en_color');
        $dropIfExists('letterhead_name_np_color');
        $dropIfExists('letterhead_address_color');
        $dropIfExists('letterhead_name_font_style');
        $dropIfExists('letterhead_name_en_align');
        $dropIfExists('letterhead_name_np_align');
        $dropIfExists('letterhead_address_align');
        $dropIfExists('letterhead_meta_chs_align');
        $dropIfExists('letterhead_meta_ps_align');
        $dropIfExists('letterhead_meta_date_align');
        $dropIfExists('letterhead_layout_json');
    }
};

