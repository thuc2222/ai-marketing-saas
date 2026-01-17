<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Thêm cấu hình Ngôn ngữ mặc định
        $this->migrator->add('general.available_languages', [
            ['code' => 'en', 'flag_icon' => 'us.svg'],
            ['code' => 'vi', 'flag_icon' => 'vn.svg'],
        ]);

        // Thêm cấu hình Tiền tệ mặc định
        $this->migrator->add('general.supported_currencies', [
            ['code' => 'VND', 'symbol' => '₫', 'rate_per_credit' => 1000],
            ['code' => 'USD', 'symbol' => '$', 'rate_per_credit' => 0.05],
        ]);
    }

    public function down(): void
    {
        // Xóa đi nếu rollback
        $this->migrator->delete('general.available_languages');
        $this->migrator->delete('general.supported_currencies');
    }
};