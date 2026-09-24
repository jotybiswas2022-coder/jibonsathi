<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('family_type', 40)->nullable()->index();
            $table->string('family_status', 40)->nullable();
            $table->string('father_occupation', 120)->nullable();
            $table->string('mother_occupation', 120)->nullable();
            $table->unsignedTinyInteger('brothers')->nullable();
            $table->unsignedTinyInteger('sisters')->nullable();
            $table->string('family_income_range', 60)->nullable();
            $table->text('about_family')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_details');
    }
};
