<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('draft'); // draft, processing, completed
            
            // Context for AI
            $table->json('brand_voice')->nullable(); // { "tone": "professional", "keywords": [] }
            $table->json('target_audience')->nullable();
            
            $table->timestamps();
        });

        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_plan_id')->constrained()->cascadeOnDelete();
            $table->string('platform')->default('facebook'); // facebook, instagram, linkedin
            $table->text('content'); // The caption
            $table->string('image_prompt')->nullable(); // Prompt used to generate image
            $table->string('image_url')->nullable(); // Final image path
            $table->dateTime('scheduled_at')->nullable();
            $table->string('status')->default('draft'); // draft, scheduled, published, failed
            $table->json('social_api_response')->nullable(); // Log from FB/TikTok API
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_posts');
        Schema::dropIfExists('marketing_plans');
    }
};