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
    Schema::table('social_posts', function (Blueprint $table) {
        // 1. Thêm ID bài viết trên MXH (nếu chưa có)
        if (!Schema::hasColumn('social_posts', 'provider_post_id')) {
            $table->string('provider_post_id')->nullable()->after('status');
        }

        // 2. Thêm các cột chỉ số (Likes, Comments, Views)
        // Chúng ta kiểm tra từng cái, thiếu cái nào thêm cái đó
        if (!Schema::hasColumn('social_posts', 'likes_count')) {
            $table->unsignedInteger('likes_count')->default(0);
        }
        
        if (!Schema::hasColumn('social_posts', 'comments_count')) {
            $table->unsignedInteger('comments_count')->default(0);
        }

        if (!Schema::hasColumn('social_posts', 'views_count')) {
            $table->unsignedInteger('views_count')->default(0);
        }
    });
}

public function down(): void
{
    Schema::table('social_posts', function (Blueprint $table) {
        $table->dropColumn(['provider_post_id', 'likes_count', 'comments_count', 'views_count']);
    });
}
};
