<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Auth;

class SocialAccountController extends Controller
{
    /**
     * Chuyển hướng người dùng sang trang đăng nhập của MXH (Facebook/TikTok)
     */
    public function redirectToProvider($provider)
    {
        if ($provider === 'tiktok') {
            return Socialite::driver('tiktok')
                ->scopes([
                    'user.info.basic', // Quyền lấy tên, avatar
                    'video.upload',    // <--- QUYỀN QUAN TRỌNG ĐỂ ĐĂNG BÀI
                ])
                ->redirect();
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Nhận dữ liệu trả về từ MXH
     */
    public function handleProviderCallback($provider)
    {
        $validProviders = ['facebook', 'tiktok'];
        
        if (!in_array($provider, $validProviders)) {
            return abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
            $userId = Auth::id();

            if (!$userId) {
                return redirect('/admin/login')->with('error', 'Vui lòng đăng nhập Admin trước.');
            }

            // --- XỬ LÝ EMAIL (QUAN TRỌNG) ---
            // Nếu TikTok không trả về email, ta tự chế ra một email ảo dựa trên ID
            // Định dạng: tiktok_IDCuaUser@no-email.com
            $email = $socialUser->getEmail();
            if (empty($email)) {
                $email = $provider . '_' . $socialUser->getId() . '@no-email.com';
            }

            // Lưu vào Database
            $account = SocialAccount::updateOrCreate(
                [
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ],
                [
                    'user_id' => $userId,
                    // Ưu tiên lấy Name, nếu không có thì lấy Nickname, không có nữa thì đặt mặc định
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'TikTok User',
                    'email' => $email, // Dùng biến $email đã xử lý ở trên
                    'avatar' => $socialUser->getAvatar(),
                    'token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken, 
                    'is_active' => true,
                ]
            );

            return redirect('/admin/social-accounts')
                ->with('success', "Đã kết nối tài khoản {$account->name} thành công!");

        } catch (\Exception $e) {
            // Log lỗi ra file laravel.log để kiểm tra sau này nếu cần
            \Illuminate\Support\Facades\Log::error("Social Login Error: " . $e->getMessage());

            return redirect('/admin/social-accounts')
                ->with('error', "Lỗi kết nối: " . $e->getMessage());
        }
    }
}