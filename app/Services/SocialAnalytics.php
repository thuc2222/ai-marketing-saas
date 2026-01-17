<?php

namespace App\Services;

use App\Models\SocialPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SocialAnalytics
{
    public function updateStats(SocialPost $post)
    {
        if (!$post->socialAccount) return;

        try {
            if ($post->platform === 'facebook') {
                $this->updateFacebookStats($post);
            } elseif ($post->platform === 'tiktok') {
                $this->updateTikTokStats($post);
            }
        } catch (\Exception $e) {
            Log::error("Analytics Error Post {$post->id}: " . $e->getMessage());
        }
    }

    private function updateFacebookStats($post)
    {
        // Facebook API để lấy like, comment
        // Cần ID bài viết dạng: PageID_PostID
        // Lưu ý: Lúc đăng bài thành công (ở bước trước), bạn cần lưu lại Post ID trả về từ FB vào database
        // Giả sử cột 'provider_post_id' trong bảng social_posts đã lưu ID này.
        
        // API: /{post-id}?fields=likes.summary(true),comments.summary(true)
        $token = $post->socialAccount->token;
        // Logic gọi API Facebook ở đây...
        // (Tạm thời để placeholder vì FB API cần quyền 'read_insights' khá phức tạp)
    }

    private function updateTikTokStats($post)
    {
        // TikTok API lấy view/like video
        // Endpoint: /v2/video/query/?fields=like_count,comment_count,share_count,view_count
        
        $token = $post->socialAccount->token;
        $fileId = $post->provider_post_id; // ID video TikTok trả về khi upload

        if (!$fileId) return;

        $response = Http::withToken($token)
            ->post('https://open.tiktokapis.com/v2/video/query/', [
                'filters' => [
                    'video_ids' => [$fileId]
                ],
                'fields' => ['like_count', 'comment_count', 'share_count', 'view_count']
            ]);

        if ($response->successful()) {
            $data = $response->json('data.videos.0');
            if ($data) {
                $post->update([
                    'likes_count' => $data['like_count'] ?? 0,
                    'comments_count' => $data['comment_count'] ?? 0,
                    // Bạn có thể thêm cột views_count vào bảng social_posts nếu muốn
                ]);
                Log::info("Updated stats for TikTok Post {$post->id}");
            }
        }
    }
}