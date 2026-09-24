<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('occupations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('designation', 120)->nullable()->index();
            $table->string('company', 160)->nullable();
            $table->string('employment_type', 60)->nullable();
            $table->string('income_range', 60)->nullable()->index();
            $table->string('work_location', 120)->nullable();
            $table->boolean('is_current')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('occupations');
    }
};
