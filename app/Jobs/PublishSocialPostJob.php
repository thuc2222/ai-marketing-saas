<?php

namespace App\Jobs;

use App\Models\SocialPost;
use App\Services\SocialPublisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class PublishSocialPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $post;
    public $userId;

    /**
     * Nhận đầu vào là bài viết cần đăng
     */
    public function __construct(SocialPost $post, $userId)
    {
        $this->post = $post;
        $this->userId = $userId;
    }

    /**
     * Logic xử lý chính (Sẽ chạy ngầm)
     */
    public function handle(SocialPublisher $publisher): void
    {
        try {
            // Gọi lại service đăng bài cũ của chúng ta
            $publisher->publish($this->post);

            // Gửi thông báo thành công cho User (qua Filament Notification)
            Notification::make()
                ->title('Đăng bài thành công!')
                ->body("Bài viết '{$this->post->topic}' đã được đăng lên {$this->post->platform}.")
                ->success()
                ->sendToDatabase(\App\Models\User::find($this->userId));

        } catch (\Exception $e) {
            Log::error("Queue Job Failed: " . $e->getMessage());
            
            // Cập nhật trạng thái bài viết là lỗi
            $this->post->update(['status' => 'failed']);

            // Báo lỗi cho user
            Notification::make()
                ->title('Đăng bài thất bại')
                ->body("Lỗi: " . $e->getMessage())
                ->danger()
                ->sendToDatabase(\App\Models\User::find($this->userId));
        }
    }
}