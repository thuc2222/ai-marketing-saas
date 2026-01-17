<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_plans', function (Blueprint $table) {
            // Chỉ thêm nếu cột chưa tồn tại
            if (!Schema::hasColumn('marketing_plans', 'campaign_goal')) {
                $table->string('campaign_goal')->nullable();
            }
            
            if (!Schema::hasColumn('marketing_plans', 'target_audience')) {
                $table->json('target_audience')->nullable();
            }

            if (!Schema::hasColumn('marketing_plans', 'brand_voice')) {
                $table->string('brand_voice')->nullable();
            }

            if (!Schema::hasColumn('marketing_plans', 'competitors')) {
                $table->json('competitors')->nullable();
            }

            if (!Schema::hasColumn('marketing_plans', 'content_pillars')) {
                $table->json('content_pillars')->nullable();
            }

            if (!Schema::hasColumn('marketing_plans', 'budget')) {
                $table->decimal('budget', 15, 2)->default(0);
            }

            if (!Schema::hasColumn('marketing_plans', 'expected_revenue')) {
                $table->decimal('expected_revenue', 15, 2)->default(0);
            }

            if (!Schema::hasColumn('marketing_plans', 'estimated_roi')) {
                $table->float('estimated_roi')->default(0);
            }

            if (!Schema::hasColumn('marketing_plans', 'stage')) {
                $table->string('stage')->default('planning'); 
            }
        });
    }

    public function down(): void
    {
        Schema::table('marketing_plans', function (Blueprint $table) {
            $table->dropColumn([
                'campaign_goal', 
                'target_audience', 
                'brand_voice', 
                'competitors',
                'content_pillars',
                'budget',
                'expected_revenue',
                'estimated_roi',
                'stage'
            ]);
        });
    }
};