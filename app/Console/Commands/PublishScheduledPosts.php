<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SocialPost;
use App\Services\SocialPublisher;
use Illuminate\Support\Facades\Log;

class PublishScheduledPosts extends Command
{
    // Tên lệnh để gọi trong Terminal
    protected $signature = 'app:publish-scheduled-posts';

    // Mô tả lệnh
    protected $description = 'Quét và đăng các bài viết đã đến giờ hẹn';

    public function handle(SocialPublisher $publisher)
    {
        $this->info('🚀 Bắt đầu quét bài viết...');

        // 1. Tìm các bài có trạng thái 'scheduled' VÀ thời gian <= hiện tại
        $posts = SocialPost::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($posts->isEmpty()) {
            $this->info('✅ Không có bài nào cần đăng lúc này.');
            return;
        }

        $this->info("Found {$posts->count()} posts to publish.");

        foreach ($posts as $post) {
            $this->info("Processing Post ID: {$post->id} ({$post->platform})...");

            try {
                // Gọi Service SocialPublisher để đăng (TikTok hoặc Facebook)
                $publisher->publish($post);
                
                $this->info("✅ Đăng thành công: {$post->id}");

            } catch (\Exception $e) {
                // Nếu lỗi, chuyển trạng thái thành 'failed' để không lặp lại
                $post->update(['status' => 'failed']);
                
                $this->error("❌ Lỗi Post {$post->id}: " . $e->getMessage());
                Log::error("Auto-Publish Failed ID {$post->id}: " . $e->getMessage());
            }
        }

        $this->info('🎉 Hoàn tất quy trình.');
    }
}