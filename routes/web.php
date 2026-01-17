<?php

use Illuminate\Support\Facades\Route;
use App\Models\SubscriptionPlan;
use App\Http\Controllers\SocialAccountController;
use Illuminate\Support\Facades\Session;

Route::get('/', function () {
    // Lấy danh sách các gói cước đang mở bán để hiện ra bảng giá
    $plans = SubscriptionPlan::where('is_active', true)->get();
    
    return view('welcome', compact('plans'));
})->name('home');

Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

// --- ROUTE ĐĂNG NHẬP MXH (Dynamic Provider) ---
// {provider} sẽ tự động nhận diện là 'facebook' hoặc 'tiktok'
// Middleware 'web' là mặc định, không cần bọc 'auth' ở đây vì người dùng chưa đăng nhập cũng cần vào route này để login
Route::get('/auth/{provider}/redirect', [SocialAccountController::class, 'redirectToProvider'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAccountController::class, 'handleProviderCallback']);

Route::view('/privacy-policy', 'privacy');

Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'vi'])) { // Chỉ chấp nhận en hoặc vi
        Session::put('locale', $locale);
    }
    return back(); // Quay lại trang trước đó
})->name('switch-language');