<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('viewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'viewed_at']);
            $table->index(['viewer_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_views');
    }
};
