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
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('ranking_submissions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('ranking_exercises')->cascadeOnDelete();
            $table->foreignId('discipline_id')->constrained('disciplines')->restrictOnDelete();
            $table->foreignId('candidate_id')->constrained('candidates')->restrictOnDelete();
            $table->integer('ranking_number');
            $table->timestamps();

            // Constraint 1: A user cannot rank the same candidate twice in an exercise
            $table->unique(['user_id', 'exercise_id', 'discipline_id', 'candidate_id'], 'unique_user_exercise_discipline_candidate');
            // Constraint 2: A user cannot assign the same ranking number twice in an exercise
            $table->unique(['user_id', 'exercise_id', 'discipline_id', 'ranking_number'], 'unique_user_exercise_discipline_rank_num');

            $table->index(['exercise_id', 'discipline_id']);
            $table->index(['candidate_id', 'ranking_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rankings');
    }
};
