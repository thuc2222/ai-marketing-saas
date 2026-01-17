<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Auth;

class SocialAccountController extends Controller
{
    // Hàm chuyển hướng: Nhận thêm tham số $provider
    public function redirectToProvider($provider)
    {
        // Kiểm tra hợp lệ để tránh hack
        if (!in_array($provider, ['facebook', 'tiktok'])) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    // Hàm nhận dữ liệu trả về
    public function handleProviderCallback($provider)
    {
        if (!in_array($provider, ['facebook', 'tiktok'])) {
            abort(404);
        }

        try {
            // Lấy thông tin user từ MXH
            $socialUser = Socialite::driver($provider)->user();
            
            // Tìm hoặc tạo mới trong Database
            $account = SocialAccount::updateOrCreate(
                [
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ],
                [
                    'user_id' => Auth::id(), // Gán cho user đang đăng nhập
                    'name' => $socialUser->getName() ?? $socialUser->getNickname(), // TikTok thường dùng nickname
                    'email' => $socialUser->getEmail(),
                    'avatar' => $socialUser->getAvatar(),
                    'token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken, // TikTok cần cái này để gia hạn token
                    'is_active' => true,
                ]
            );

            return redirect('/admin/social-accounts') // Quay về trang quản lý
                ->with('success', "Đã kết nối {$provider} thành công!");

        } catch (\Exception $e) {
            return redirect('/admin')->with('error', 'Lỗi kết nối: ' . $e->getMessage());
        }
    }
}