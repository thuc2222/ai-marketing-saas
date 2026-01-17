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
    Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained();
        $table->foreignId('subscription_plan_id')->nullable()->constrained(); // Null nếu chỉ nạp credits lẻ
        $table->decimal('amount', 15, 0); // Số tiền (VND)
        $table->string('transaction_code')->nullable(); // Mã giao dịch ngân hàng
        $table->string('content')->nullable(); // Nội dung CK
        $table->string('status')->default('pending'); // pending | paid | failed
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
