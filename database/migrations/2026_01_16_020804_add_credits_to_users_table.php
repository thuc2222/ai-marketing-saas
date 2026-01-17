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
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'credits')) {
            $table->unsignedInteger('credits')->default(10);
        }
        if (!Schema::hasColumn('users', 'subscription_plan_id')) {
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans'); // Lưu ý: 'subscription_plans' phải khớp tên bảng trên
        }
        if (!Schema::hasColumn('users', 'subscription_expires_at')) {
            $table->timestamp('subscription_expires_at')->nullable();
        }
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
