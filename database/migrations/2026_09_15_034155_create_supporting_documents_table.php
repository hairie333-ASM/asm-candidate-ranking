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
        Schema::create('supporting_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('due_diligence_submission_id')->constrained('due_diligence_submissions')->cascadeOnDelete();
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('storage_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->timestamps();

            $table->index('due_diligence_submission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supporting_documents');
    }
};
