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
        Schema::table('due_diligence_submissions', function (Blueprint $table) {
            $table->string('reference_1_name')->nullable()->after('comment');
            $table->string('reference_1_designation')->nullable()->after('reference_1_name');
            $table->string('reference_1_organisation')->nullable()->after('reference_1_designation');
            $table->string('reference_1_contact_number')->nullable()->after('reference_1_organisation');
            $table->string('reference_1_email')->nullable()->after('reference_1_contact_number');

            $table->string('reference_2_name')->nullable()->after('reference_1_email');
            $table->string('reference_2_designation')->nullable()->after('reference_2_name');
            $table->string('reference_2_organisation')->nullable()->after('reference_2_designation');
            $table->string('reference_2_contact_number')->nullable()->after('reference_2_organisation');
            $table->string('reference_2_email')->nullable()->after('reference_2_contact_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('due_diligence_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'reference_1_name',
                'reference_1_designation',
                'reference_1_organisation',
                'reference_1_contact_number',
                'reference_1_email',
                'reference_2_name',
                'reference_2_designation',
                'reference_2_organisation',
                'reference_2_contact_number',
                'reference_2_email',
            ]);
        });
    }
};
