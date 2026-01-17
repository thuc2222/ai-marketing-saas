<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLanguage
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Kiểm tra session xem user đã chọn ngôn ngữ nào chưa
        $locale = Session::get('locale');

        // 2. Nếu chưa, lấy mặc định từ config (hoặc Settings DB nếu bạn muốn query)
        if (!$locale) {
            $locale = config('app.locale');
        }

        // 3. Set ngôn ngữ cho ứng dụng
        App::setLocale($locale);

        return $next($request);
    }
}