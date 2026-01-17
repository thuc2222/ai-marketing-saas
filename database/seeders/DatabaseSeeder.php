<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Plans
        $freePlan = SubscriptionPlan::create([
            'name' => ['en' => 'Free Starter'],
            'description' => ['en' => 'Perfect for testing the waters.'],
            'slug' => 'free-starter',
            'price_monthly' => 0,
            'price_yearly' => 0,
            'currency_code' => 'USD',
            'ai_word_limit' => 2000,
            'ai_image_limit' => 5,
            'social_account_limit' => 1,
            'can_auto_post' => false,
            'is_active' => true,
        ]);

        $proPlan = SubscriptionPlan::create([
            'name' => ['en' => 'Pro Growth'],
            'description' => ['en' => 'Automate your entire workflow.'],
            'slug' => 'pro-growth',
            'price_monthly' => 2900, // $29.00
            'price_yearly' => 29000, // $290.00
            'currency_code' => 'USD',
            'ai_word_limit' => 50000,
            'ai_image_limit' => 100,
            'social_account_limit' => 5,
            'can_auto_post' => true,
            'is_active' => true,
            'is_featured' => true,
        ]);

        // 2. Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'subscription_plan_id' => $proPlan->id,
            'subscription_expires_at' => now()->addYear(),
        ]);
        
        // 3. Create Demo User
        User::create([
            'name' => 'Demo Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'subscription_plan_id' => $freePlan->id,
        ]);
    }
}