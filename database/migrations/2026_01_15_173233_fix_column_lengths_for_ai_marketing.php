<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Sửa bảng social_accounts (Lỗi Avatar & Token)
        Schema::table('social_accounts', function (Blueprint $table) {
            $table->text('avatar')->nullable()->change();
            $table->text('token')->change(); // Token Facebook cũng rất dài, sửa luôn cho chắc
        });

        // 2. Sửa bảng social_posts (Lỗi Image Prompt)
        Schema::table('social_posts', function (Blueprint $table) {
            $table->text('image_prompt')->nullable()->change();
            $table->text('content')->change(); // Nội dung bài viết chắc chắn sẽ dài > 255, sửa luôn
        });
    }

    public function down(): void
    {
        // Hoàn tác nếu cần (thường ít dùng)
        Schema::table('social_accounts', function (Blueprint $table) {
            $table->string('avatar')->nullable()->change();
            $table->string('token')->change();
        });

        Schema::table('social_posts', function (Blueprint $table) {
            $table->string('image_prompt')->nullable()->change();
            $table->string('content')->change();
        });
    }
};