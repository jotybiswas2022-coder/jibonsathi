<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('preferred_gender', 10)->nullable();
            $table->unsignedTinyInteger('age_min')->nullable();
            $table->unsignedTinyInteger('age_max')->nullable();
            $table->unsignedSmallInteger('height_min_cm')->nullable();
            $table->unsignedSmallInteger('height_max_cm')->nullable();
            $table->string('preferred_country', 80)->nullable();
            $table->string('preferred_division', 80)->nullable();
            $table->string('preferred_district', 80)->nullable();
            $table->json('religions')->nullable();
            $table->json('marital_statuses')->nullable();
            $table->string('education_level', 80)->nullable();
            $table->string('profession', 120)->nullable();
            $table->string('diet', 40)->nullable();
            $table->string('smoking', 40)->nullable();
            $table->string('drinking', 40)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_preferences');
    }
};
