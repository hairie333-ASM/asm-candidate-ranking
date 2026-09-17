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
        Schema::create('ranking_exercises', function (Blueprint $table) {
            $table->id();
            $table->string('exercise_name');
            $table->text('description')->nullable();
            $table->timestamp('start_datetime')->nullable();
            $table->timestamp('end_datetime')->nullable();
            $table->string('status')->default('Draft'); // Draft, Open, Closed
            $table->boolean('allow_resubmission')->default(false);
            $table->timestamps();

            $table->index('status');
            $table->index(['start_datetime', 'end_datetime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranking_exercises');
    }
};
