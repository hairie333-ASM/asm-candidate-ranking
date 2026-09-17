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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discipline_id')->constrained('disciplines')->restrictOnDelete();
            $table->string('candidate_name');
            $table->string('candidate_title')->nullable();
            $table->string('organisation')->nullable();
            $table->string('photo_url')->nullable();
            $table->text('basis_of_recommendation')->nullable();
            $table->text('area_of_expertise')->nullable();
            $table->text('qualifications')->nullable();
            $table->text('professional_memberships')->nullable();
            $table->text('short_description')->nullable();
            $table->string('nomination_form_url', 1000)->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('discipline_id');
            $table->index('candidate_name');
            $table->index('organisation');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
