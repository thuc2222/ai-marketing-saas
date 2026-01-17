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
        // Chỉ thêm nếu cột 'credits' chưa tồn tại
        if (!Schema::hasColumn('users', 'credits')) {
            $table->integer('credits')->default(0)->after('email');
        }
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        if (Schema::hasColumn('users', 'credits')) {
            $table->dropColumn('credits');
        }
    });
}
};
