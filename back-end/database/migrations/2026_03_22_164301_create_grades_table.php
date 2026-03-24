<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade'); // FIXED
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->enum('category', ['MIDTERM', 'FINAL', 'ASSIGNMENT', 'QUIZ', 'HOMEWORK', 'PROJECT', 'PARTICIPATION', 'OTHER']); // FIXED
            $table->decimal('score', 5, 2);
            $table->decimal('weight', 5, 2)->default(0);
            $table->foreignId('added_by')->constrained('users')->onDelete('cascade'); // teacher
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};