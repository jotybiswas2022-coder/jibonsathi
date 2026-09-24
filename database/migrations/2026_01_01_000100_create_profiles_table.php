<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Identity
            $table->string('gender', 10)->index();
            $table->date('date_of_birth')->nullable();
            $table->unsignedSmallInteger('height_cm')->nullable()->index();
            $table->string('marital_status', 30)->nullable()->index();
            $table->string('religion', 60)->nullable()->index();
            $table->string('mother_tongue', 60)->nullable();

            // Location
            $table->string('country', 80)->nullable()->index();
            $table->string('division', 80)->nullable()->index();
            $table->string('district', 80)->nullable()->index();
            $table->string('city', 120)->nullable();

            // Narrative
            $table->text('about_me')->nullable();
            $table->string('headline', 160)->nullable();

            // Moderation / lifecycle
            $table->unsignedTinyInteger('profile_completion')->default(0);
            $table->string('profile_status', 20)->default('pending')->index();
            $table->string('verification_status', 20)->default('unverified')->index();
            $table->timestamp('approved_at')->nullable();

            // Privacy
            $table->string('profile_visibility', 20)->default('members');
            $table->boolean('show_phone')->default(false);
            $table->boolean('show_email')->default(false);
            $table->boolean('allow_messages')->default(true);
            $table->boolean('allow_profile_views')->default(true);
            $table->boolean('show_online_status')->default(true);

            // Notification preferences
            $table->boolean('notify_interests')->default(true);
            $table->boolean('notify_messages')->default(true);
            $table->boolean('notify_profile_views')->default(true);
            $table->boolean('notify_shortlists')->default(true);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
