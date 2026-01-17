<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Email Lists
        Schema::create('email_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // 2. Subscribers
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_list_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->enum('status', ['active', 'bounced', 'unsubscribed'])->default('active');
            $table->timestamps();
            
            // Prevent duplicate emails in the same list
            $table->unique(['email_list_id', 'email']);
        });

        // 3. Campaigns
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('subject');
            $table->longText('content'); // HTML Content
            $table->enum('status', ['draft', 'processing', 'completed', 'failed'])->default('draft');
            
            // Analytics
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('open_count')->default(0);
            $table->integer('click_count')->default(0);
            $table->integer('bounce_count')->default(0);
            
            $table->timestamps();
        });
    }
};