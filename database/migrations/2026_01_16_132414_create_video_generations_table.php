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
    Schema::create('video_generations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('social_post_id')->constrained()->cascadeOnDelete();
        
        // Cấu hình loại video và chi phí
        $table->string('video_type'); // social_short, pro_hd, avatar
        $table->integer('credits_charged');
        
        // Lưu kịch bản AI đã tối ưu (từ DeepSeek/GPT-4o)
        $table->text('ai_script')->nullable(); 
        
        // Trạng thái và thông tin nhà cung cấp
        $table->string('status')->default('pending'); // scripting, rendering, completed, failed
        $table->string('provider')->nullable(); // kling, luma, heygen, runway
        $table->string('provider_request_id')->nullable(); // ID để check webhook
        $table->string('result_url')->nullable(); // Link video cuối cùng
        $table->text('error_message')->nullable();
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_generations');
    }
};
