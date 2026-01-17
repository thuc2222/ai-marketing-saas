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
    Schema::table('social_accounts', function (Blueprint $table) {
        // Thêm cột email (cho phép null vì không phải MXH nào cũng trả về email)
        $table->string('email')->nullable()->after('name');
        
        // Tiện thể kiểm tra xem có cột avatar và refresh_token chưa, nếu chưa thì thêm luôn
        if (!Schema::hasColumn('social_accounts', 'avatar')) {
            $table->string('avatar')->nullable()->after('email');
        }
        if (!Schema::hasColumn('social_accounts', 'refresh_token')) {
            $table->text('refresh_token')->nullable()->after('token');
        }
    });
}

public function down(): void
{
    Schema::table('social_accounts', function (Blueprint $table) {
        $table->dropColumn(['email', 'avatar', 'refresh_token']);
    });
}
};
