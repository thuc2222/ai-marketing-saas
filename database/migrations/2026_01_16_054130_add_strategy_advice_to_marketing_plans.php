<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('marketing_plans', function (Blueprint $table) {
        $table->longText('ai_strategy_advice')->nullable(); // Lưu bài tư vấn dài
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_plans', function (Blueprint $table) {
            //
        });
    }
};
