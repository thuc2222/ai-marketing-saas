<?php

namespace App\Services;

use App\Models\SocialPost;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SocialPublisher
{
    /**
     * Hàm chính để điều phối việc đăng bài
     */
    public function publish(SocialPost $post, ?int $socialAccountId = null): void
    {
        // 1. Xác định tài khoản đích
        $targetId = $socialAccountId ?? $post->social_account_id;
        $account = $targetId ? SocialAccount::find($targetId) : null;

        // Fallback: Nếu không tìm thấy, thử tìm tài khoản default theo platform
        if (!$account) {
            $account = SocialAccount::where('user_id', auth()->id() ?? $post->marketingPlan->user_id ?? 1)
                ->where('provider', $post->platform)
                ->where('is_active', true)
                ->first();
        }

        if (!$account) {
            throw new \Exception("Không tìm thấy tài khoản {$post->platform} để đăng bài.");
        }

        // 2. Điều hướng xử lý theo Platform
        $providerPostId = null;

        if ($post->platform === 'tiktok') {
            $providerPostId = $this->publishToTikTok($post, $account);
        } else {
            $providerPostId = $this->publishToFacebook($post, $account);
        }

        // 3. Cập nhật trạng thái thành công & Lưu ID
        $post->update([
            'status' => 'published',
            'published_at' => now(),
            'provider_post_id' => $providerPostId, // Lưu ID để sau này tracking view/like
        ]);

        Log::info("Đã đăng thành công Post ID: {$post->id} lên {$post->platform}. Provider ID: {$providerPostId}");
    }

    // --- LOGIC FACEBOOK ---
    private function publishToFacebook($post, $account)
    {
        $pageId = $account->provider_id;
        $accessToken = $account->token;
        $attachedMedia = [];

        // Xử lý upload ảnh (nếu có)
        // Hỗ trợ cả trường hợp 1 ảnh (string) hoặc nhiều ảnh (array)
        $images = $post->image_url;
        if (is_string($images)) {
            $images = [$images]; // Ép về mảng
        }

        if (!empty($images) && is_array($images)) {
            foreach ($images as $imagePath) {
                // Upload từng ảnh lên Facebook trước để lấy ID
                $photoId = $this->uploadFacebookPhoto($pageId, $accessToken, $imagePath);
                if ($photoId) {
                    $attachedMedia[] = ['media_fbid' => $photoId];
                }
            }
        }

        // Gửi bài viết (Text + Các ảnh đã upload)
        $endpoint = "https://graph.facebook.com/v19.0/{$pageId}/feed";
        $payload = [
            'message' => $post->content,
            'access_token' => $accessToken,
        ];

        // Nếu có ảnh thì đính kèm vào
        if (!empty($attachedMedia)) {
            $payload['attached_media'] = $attachedMedia;
        }

        $response = Http::post($endpoint, $payload);

        if ($response->failed()) {
            throw new \Exception('FB Publish Error: ' . $response->json('error.message'));
        }

        // Trả về ID bài viết (dạng PageID_PostID)
        return $response->json('id');
    }

    private function uploadFacebookPhoto($pageId, $accessToken, $imagePath)
    {
        $endpoint = "https://graph.facebook.com/v19.0/{$pageId}/photos";
        
        // Đường dẫn file thực tế trên server
        $physicalPath = Storage::disk('public')->path($imagePath);

        if (!file_exists($physicalPath)) {
            Log::warning("Không tìm thấy ảnh: " . $physicalPath);
            return null;
        }

        $response = Http::withToken($accessToken)
            ->asMultipart()
            ->attach('source', file_get_contents($physicalPath), basename($physicalPath))
            ->post($endpoint, [
                'published' => 'false' // Quan trọng: Chỉ upload ngầm, không đăng ngay
            ]);

        if ($response->successful()) {
            return $response->json('id');
        }

        Log::error("FB Photo Upload Fail: " . $response->body());
        return null;
    }

    // --- LOGIC TIKTOK ---
    private function publishToTikTok($post, $account)
    {
        $accessToken = $account->token;
        
        // Kiểm tra file video
        if (empty($post->video_url)) {
            throw new \Exception("Bài viết TikTok bắt buộc phải có Video.");
        }

        $physicalPath = Storage::disk('public')->path($post->video_url);

        if (!file_exists($physicalPath)) {
            throw new \Exception("Không tìm thấy file video: " . $post->video_url);
        }

        $fileSize = filesize($physicalPath);

        // BƯỚC 1: INIT - Khởi tạo phiên upload
        $initResponse = Http::withToken($accessToken)
            ->post('https://open.tiktokapis.com/v2/post/publish/video/init/', [
                'post_info' => [
                    'title' => substr($post->content, 0, 150), // TikTok caption giới hạn
                    'privacy_level' => 'PUBLIC_TO_EVERYONE',
                    'disable_duet' => false,
                    'disable_comment' => false,
                    'video_cover_timestamp_ms' => 1000
                ],
                'source_info' => [
                    'source' => 'FILE_UPLOAD',
                    'video_size' => $fileSize,
                    'chunk_size' => $fileSize,
                    'total_chunk_count' => 1
                ]
            ]);

        if ($initResponse->failed()) {
            throw new \Exception('TikTok Init Failed: ' . $initResponse->body());
        }

        $uploadUrl = $initResponse->json('data.upload_url');
        $publishId = $initResponse->json('data.publish_id'); // Lấy ID phiên đăng

        // BƯỚC 2: UPLOAD - Đẩy file binary lên
        $fileStream = fopen($physicalPath, 'r');

        $uploadResponse = Http::withHeaders([
            'Content-Type' => 'video/mp4',
            'Content-Length' => $fileSize,
        ])->send('PUT', $uploadUrl, [
            'body' => $fileStream
        ]);

        if ($uploadResponse->failed()) {
            throw new \Exception('TikTok Upload Failed: ' . $uploadResponse->body());
        }

        // Lưu ý: TikTok không trả về VideoID ngay lập tức.
        // Nó chỉ trả về publish_id (mã phiên). VideoID thật sẽ có sau khi TikTok duyệt xong.
        // Tạm thời chúng ta lưu publish_id để tracking.
        return $publishId;
    }
}