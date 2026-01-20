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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reporter_name');
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_name');
            $table->foreignId('question_id')->nullable()->constrained('questions')->nullOnDelete();
            $table->text('question_text_snapshot');
            $table->text('answer_text_snapshot');
            $table->text('reason');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
