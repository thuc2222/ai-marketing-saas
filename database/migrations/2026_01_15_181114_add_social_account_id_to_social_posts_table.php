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
        // Thêm khóa ngoại trỏ tới bảng social_accounts
        $table->foreignId('social_account_id')
              ->nullable() // Cho phép null (để không lỗi dữ liệu cũ)
              ->constrained('social_accounts')
              ->nullOnDelete(); // Nếu xóa Fanpage thì bài viết vẫn còn (nhưng mất liên kết)
    });
}

public function down(): void
{
    Schema::table('social_posts', function (Blueprint $table) {
        $table->dropForeign(['social_account_id']);
        $table->dropColumn('social_account_id');
    });
}
};
