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
        // Đổi avatar sang TEXT để chứa link dài
        $table->text('avatar')->nullable()->change();
        
        // Tiện thể đổi luôn token sang TEXT vì token FB cũng rất dài
        $table->text('token')->change(); 
    });
}

public function down(): void
{
    Schema::table('social_accounts', function (Blueprint $table) {
        $table->string('avatar')->nullable()->change();
        $table->string('token')->change();
    });
}
};
