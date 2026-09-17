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
        Schema::create('due_diligence_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('due_diligence_categories')->restrictOnDelete();
            $table->text('comment');
            $table->foreignId('user_discipline_id')->nullable()->constrained('disciplines')->nullOnDelete();
            $table->timestamps();

            $table->index(['candidate_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('due_diligence_submissions');
    }
};
