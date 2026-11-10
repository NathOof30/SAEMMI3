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
        Schema::table('users', function (Blueprint $table) {
            $table->string('team_name')->nullable()->after('name');
            $table->unsignedInteger('current_step')->default(1)->after('password');
            $table->string('phase')->default('GUESSING_LOCATION')->after('current_step');
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['team_name', 'current_step', 'phase']);
        });
    }
};
