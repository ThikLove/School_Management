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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('student_code', 20)->unique();
            $table->string('name', 150)->index();
            $table->enum('gender', ['male', 'female', 'other'])->index();
            $table->date('date_of_birth');
            $table->string('email')->nullable()->unique();
            $table->string('phone', 30)->nullable();
            $table->string('address')->nullable();
            $table->date('enrollment_date');
            $table->enum('status', ['active', 'inactive', 'graduated'])->default('active')->index();
            $table->foreignId('class_id')->constrained('school_classes')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
