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
        // Chuyển cột image_url sang kiểu TEXT (chứa được khoảng 65.000 ký tự)
        // Hoặc dùng longText() nếu bạn định up cả trăm ảnh 1 bài
        $table->text('image_url')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('social_posts', function (Blueprint $table) {
        // Quay về kiểu cũ (nếu cần rollback)
        $table->string('image_url')->change();
    });
}
};
