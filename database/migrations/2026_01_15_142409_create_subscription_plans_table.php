<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    // Nếu bảng đã tồn tại thì xóa đi để tạo lại cho đúng chuẩn (Thêm dòng này)
    Schema::dropIfExists('subscription_plans'); 

    Schema::create('subscription_plans', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        
        // --- CỘT QUAN TRỌNG ---
        $table->decimal('price', 10, 2); // Phải là 'price', không phải 'price_monthly'
        $table->integer('monthly_credits'); // Phải là 'monthly_credits'
        // ----------------------
        
        $table->json('features')->nullable();
        $table->string('stripe_price_id')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};