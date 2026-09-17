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
        Schema::create('ranking_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('ranking_exercises')->cascadeOnDelete();
            $table->foreignId('discipline_id')->constrained('disciplines')->restrictOnDelete();
            $table->string('status')->default('SUBMITTED'); // DRAFT, SUBMITTED
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'exercise_id', 'discipline_id'], 'unique_user_exercise_discipline_submission');
            $table->index(['exercise_id', 'discipline_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranking_submissions');
    }
};
