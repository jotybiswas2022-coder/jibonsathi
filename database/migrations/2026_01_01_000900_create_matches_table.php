<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matched_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('match_percentage')->default(0)->index();
            $table->json('reasons')->nullable();
            $table->string('type', 30)->default('recommended')->index();
            $table->boolean('is_mutual')->default(false);
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'matched_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
