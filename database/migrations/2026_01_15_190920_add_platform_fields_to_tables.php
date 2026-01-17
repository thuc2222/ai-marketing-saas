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
    // 1. Thêm cột 'platform' vào bảng Kế hoạch (để biết Plan này đánh trận nào)
    Schema::table('marketing_plans', function (Blueprint $table) {
        $table->string('platform')->default('facebook')->after('name'); 
        // Giá trị sẽ là: 'facebook' hoặc 'tiktok'
    });

    // 2. Thêm cột 'video_url' vào bảng Bài viết (để lưu file MP4)
    Schema::table('social_posts', function (Blueprint $table) {
        $table->string('video_url')->nullable()->after('image_url');
    });
}

public function down(): void
{
    Schema::table('marketing_plans', function (Blueprint $table) {
        $table->dropColumn('platform');
    });
    Schema::table('social_posts', function (Blueprint $table) {
        $table->dropColumn('video_url');
    });
}
};
