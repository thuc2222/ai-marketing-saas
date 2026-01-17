<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VideoGeneration;
use App\Models\User;
use App\Services\VideoBrandingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class VideoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('AI Webhook Data Received:', $request->all());

        $payload = $request->all();
        $providerRequestId = $payload['id'] ?? $payload['data']['task_id'] ?? null;

        if (!$providerRequestId) {
            return response()->json(['error' => 'No ID found'], 400);
        }

        $videoGen = VideoGeneration::where('provider_request_id', $providerRequestId)->first();

        if (!$videoGen) {
            return response()->json(['error' => 'Video record not found'], 404);
        }

        // Lấy URL từ nhà cung cấp (Kling hoặc Replicate)
        $videoUrl = $payload['output'][0] ?? $payload['data']['video_url'] ?? null;

        if ($videoUrl) {
            try {
                // 1. Tải video gốc từ AI về thư mục tạm
                $tempFilename = 'temp/' . Str::uuid() . '.mp4';
                Storage::disk('public')->put($tempFilename, file_get_contents($videoUrl));

                // 2. Kiểm tra Logo và xử lý Branding
                $user = User::find($videoGen->user_id);
                $userLogo = $user->brand_logo ?? null;
                $finalFilename = 'videos/' . Str::uuid() . '.mp4';

                if ($userLogo && Storage::disk('public')->exists($userLogo)) {
                    // Nếu có logo -> Chèn logo bằng FFMPEG
                    app(VideoBrandingService::class)->applyLogo(
                        $tempFilename,
                        $userLogo,
                        $finalFilename
                    );
                    Storage::disk('public')->delete($tempFilename); // Xóa file tạm sau khi chèn
                } else {
                    // Nếu không có logo -> Đổi tên file tạm thành file chính thức
                    Storage::disk('public')->move($tempFilename, $finalFilename);
                }

                // 3. Cập nhật Database một lần duy nhất
                $videoGen->update([
                    'status' => 'completed',
                    'result_url' => Storage::url($finalFilename)
                ]);

                $videoGen->socialPost->update(['video_status' => 'ready']);

                // 4. Gửi thông báo cho User
                if ($user) {
                    Notification::make()
                        ->title('🎬 Video của bạn đã sẵn sàng!')
                        ->body("Video cho chủ đề '{$videoGen->socialPost->topic}' đã được xử lý xong.")
                        ->success()
                        ->sendToDatabase($user);
                }

            } catch (\Exception $e) {
                Log::error('Webhook Processing Error: ' . $e->getMessage());
                $videoGen->update(['status' => 'failed']);
            }
        }

        return response()->json(['status' => 'ok']);

        $payload = $request->all();
        $providerRequestId = $payload['id'] ?? $payload['data']['task_id'] ?? null;
        $videoUrl = $payload['output'][0] ?? $payload['data']['video_url'] ?? null;

        if ($providerRequestId && $videoUrl) {
            $videoGen = VideoGeneration::where('provider_request_id', $providerRequestId)->first();
            
            if ($videoGen) {
                // ĐẨY VÀO HÀNG CHỜ VÀ KẾT THÚC WEBHOOK NGAY
                \App\Jobs\ProcessVideoBranding::dispatch($videoGen, $videoUrl);
                return response()->json(['status' => 'queued']);
            }
        }

        return response()->json(['status' => 'error'], 400);
    }
}