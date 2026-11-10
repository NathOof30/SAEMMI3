<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->default('gali')->after('phase');
            $table->boolean('reward_picked')->default(false)->after('avatar');
            $table->string('reward_token', 64)->nullable()->unique()->after('reward_picked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'reward_picked', 'reward_token']);
        });
    }
};
