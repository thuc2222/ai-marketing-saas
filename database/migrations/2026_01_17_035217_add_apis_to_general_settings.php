<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\Exceptions\SettingAlreadyExists;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Danh sách các key cần bổ sung
        $keysToAdd = [
            'general.replicate_api_token',
            'general.kling_access_key',
            'general.kling_secret_key',
            'general.facebook_app_id',
            'general.facebook_app_secret',
            'general.stripe_public_key',
            'general.stripe_secret_key',
        ];

        foreach ($keysToAdd as $key) {
            try {
                // Thử thêm key với giá trị rỗng
                $this->migrator->add($key, '');
            } catch (SettingAlreadyExists $e) {
                // Nếu key đã tồn tại -> Bỏ qua, không báo lỗi
                continue;
            }
        }
    }

    public function down(): void
    {
        // Xóa lần lượt khi rollback
        $this->migrator->delete('general.replicate_api_token');
        $this->migrator->delete('general.kling_access_key');
        $this->migrator->delete('general.kling_secret_key');
        $this->migrator->delete('general.facebook_app_id');
        $this->migrator->delete('general.facebook_app_secret');
        $this->migrator->delete('general.stripe_public_key');
        $this->migrator->delete('general.stripe_secret_key');
    }
};