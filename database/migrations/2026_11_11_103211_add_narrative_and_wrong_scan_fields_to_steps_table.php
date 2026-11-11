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
        Schema::table('steps', function (Blueprint $table) {
            $table->text('story_rp')->nullable()->after('riddle_text');
            $table->text('heritage_info')->nullable()->after('story_rp');
            $table->text('wrong_scan_intro')->nullable()->after('heritage_info');
            $table->text('wrong_scan_mismatch')->nullable()->after('wrong_scan_intro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('steps', function (Blueprint $table) {
            $table->dropColumn([
                'story_rp',
                'heritage_info',
                'wrong_scan_intro',
                'wrong_scan_mismatch',
            ]);
        });
    }
};
