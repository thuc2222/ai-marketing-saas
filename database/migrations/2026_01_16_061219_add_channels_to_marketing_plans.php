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
        // Lưu danh sách kênh: ['facebook', 'tiktok', 'email', 'offline_posm']
        $table->json('channels')->nullable();

        // Lưu KPI cụ thể: {"reach": 100000, "leads": 500}
        $table->json('kpi_targets')->nullable(); 
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
