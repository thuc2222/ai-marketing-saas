<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_plans', function (Blueprint $table) {
            // Kiểm tra: Nếu chưa có cột 'channels' thì mới thêm
            if (!Schema::hasColumn('marketing_plans', 'channels')) {
                $table->json('channels')->nullable();
            }
            
            // Kiểm tra: Nếu chưa có cột 'kpi_targets' thì mới thêm
            if (!Schema::hasColumn('marketing_plans', 'kpi_targets')) {
                $table->json('kpi_targets')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('marketing_plans', function (Blueprint $table) {
            // Xóa cột nếu tồn tại
            if (Schema::hasColumn('marketing_plans', 'channels')) {
                $table->dropColumn('channels');
            }
            if (Schema::hasColumn('marketing_plans', 'kpi_targets')) {
                $table->dropColumn('kpi_targets');
            }
        });
    }
};