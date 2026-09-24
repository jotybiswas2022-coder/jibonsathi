<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lifestyle_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('diet', 40)->nullable()->index();
            $table->string('smoking', 40)->nullable()->index();
            $table->string('drinking', 40)->nullable()->index();
            $table->json('hobbies')->nullable();
            $table->json('interests')->nullable();
            $table->text('about_lifestyle')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lifestyle_details');
    }
};
