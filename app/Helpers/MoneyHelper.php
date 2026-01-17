<?php
use App\Settings\GeneralSettings;

if (!function_exists('format_currency_display')) {
    function format_currency_display($credits) {
        // 1. Lấy currency người dùng đang chọn (từ Session)
        $selectedCurrencyCode = session('currency', 'VND');
        
        // 2. Lấy cấu hình từ Settings
        $settings = app(GeneralSettings::class);
        $currencies = collect($settings->supported_currencies);

        // 3. Tìm cấu hình của loại tiền đang chọn
        $currencyConfig = $currencies->firstWhere('code', $selectedCurrencyCode);

        // Nếu không tìm thấy, fallback về VND mặc định
        if (!$currencyConfig) {
            return number_format($credits * 1000) . ' ₫';
        }

        // 4. Tính toán: Số Credits * Tỷ giá
        $rate = (float) $currencyConfig['rate_per_credit'];
        $value = $credits * $rate;

        // 5. Định dạng hiển thị
        // Nếu là USD thì để 2 số thập phân, VND thì 0 số thập phân
        $decimals = ($selectedCurrencyCode === 'USD') ? 2 : 0;
        
        return $currencyConfig['symbol'] . number_format($value, $decimals);
    }
}