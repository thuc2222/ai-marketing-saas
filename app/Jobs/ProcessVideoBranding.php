<?php

namespace App\Jobs;

use App\Models\VideoGeneration;
use App\Models\User;
use App\Services\VideoBrandingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class ProcessVideoBranding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Thử lại 3 lần nếu lỗi (ví dụ file logo chưa tải kịp)
    public $timeout = 300; // Giới hạn 5 phút cho mỗi video

    public function __construct(
        protected VideoGeneration $videoGen,
        protected string $videoUrl
    ) {}

    public function handle()
    {
        // 1. Tải video gốc từ AI
        $tempFilename = 'temp/' . Str::uuid() . '.mp4';
        Storage::disk('public')->put($tempFilename, file_get_contents($this->videoUrl));

        // 2. Xác định Logo và đường dẫn lưu chính thức
        $user = User::find($this->videoGen->user_id);
        $userLogo = $user->brand_logo ?? null;
        $finalFilename = 'videos/' . Str::uuid() . '.mp4';

        // 3. Thực hiện Branding (FFMPEG)
        if ($userLogo && Storage::disk('public')->exists($userLogo)) {
            app(VideoBrandingService::class)->applyLogo(
                $tempFilename,
                $userLogo,
                $finalFilename
            );
            Storage::disk('public')->delete($tempFilename);
        } else {
            Storage::disk('public')->move($tempFilename, $finalFilename);
        }

        // 4. Cập nhật trạng thái xong
        $this->videoGen->update([
            'status' => 'completed',
            'result_url' => Storage::url($finalFilename)
        ]);

        $this->videoGen->socialPost->update(['video_status' => 'ready']);

        // 5. Thông báo cho User
        Notification::make()
            ->title('🎬 Video của bạn đã sẵn sàng!')
            ->success()
            ->sendToDatabase($user);
    }
}