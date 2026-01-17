<?php

namespace App\Jobs;

use App\Models\SocialPost;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishScheduledPosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Find posts that are 'scheduled' and past due
        $posts = SocialPost::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->with(['marketingPlan.user.socialAccounts']) // Eager load for API tokens
            ->get();

        foreach ($posts as $post) {
            $this->publishPost($post);
        }
    }

    protected function publishPost(SocialPost $post): void
{
    try {
        $account = \App\Models\SocialAccount::where('user_id', $post->marketingPlan->user_id)
            ->where('provider', $post->platform) // 'facebook'
            ->where('is_active', true)
            ->first();

        if (!$account) {
            throw new \Exception("Chưa kết nối tài khoản {$post->platform}");
        }

        // Logic đăng bài Facebook Graph API
        if ($post->platform === 'facebook') {
            
            $payload = [
                'message' => $post->content,
                'access_token' => $account->token, // Page Access Token
            ];

            // Nếu có ảnh (URL ảnh phải public trên internet, không được là localhost)
            // Trong môi trường dev, bạn phải dùng ngrok hoặc upload ảnh lên S3/Cloudinary trước
            if ($post->image_url) {
                $endpoint = "https://graph.facebook.com/{$account->provider_id}/photos";
                $payload['url'] = asset('storage/' . $post->image_url); // Cần domain thật
            } else {
                $endpoint = "https://graph.facebook.com/{$account->provider_id}/feed";
            }

            $response = Http::post($endpoint, $payload);

            if ($response->failed()) {
                throw new \Exception("FB API Error: " . $response->body());
            }

            $fbId = $response->json('id') ?? $response->json('post_id');
            
            $post->update([
                'status' => 'published',
                'social_api_response' => $response->json()
            ]);
        }

    } catch (\Exception $e) {
        $post->update([
            'status' => 'failed',
            'social_api_response' => ['error' => $e->getMessage()]
        ]);
        \Illuminate\Support\Facades\Log::error("Publish Failed: " . $e->getMessage());
    }
    }
}