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
            $table->string('role')->default('voting_user')->after('password');
            $table->foreignId('discipline_id')->nullable()->after('role')->constrained('disciplines')->nullOnDelete();
            $table->boolean('active')->default(true)->after('discipline_id');
            $table->timestamp('last_login')->nullable()->after('active');

            $table->index('role');
            $table->index('discipline_id');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['discipline_id']);
            $table->dropColumn(['role', 'discipline_id', 'active', 'last_login']);
        });
    }
};
