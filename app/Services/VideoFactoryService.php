<?php

namespace App\Services;

use App\Models\SocialPost;
use App\Models\VideoGeneration;
use App\Settings\VideoSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class VideoFactoryService
{
    /**
     * Sản xuất video bằng cách kết hợp OpenAI và Video AI Providers
     */
    public function produceVideo(SocialPost $post, string $type, VideoSettings $settings)
    {
        $user = auth()->user();
        $credits = (int) $settings->{"price_{$type}"};

        try {
            // 1. Soạn Prompt hình ảnh chi tiết bằng OpenAI
            $aiScript = $this->generateVideoPrompt($post, $settings->scripting_model ?? 'gpt-4o');

            // 2. Render Video với cơ chế Failover
            $primary = $settings->video_provider; // 'replicate' hoặc 'kling'
            $backup = ($primary === 'replicate') ? 'kling' : 'replicate';

            // Thử lần 1
            $result = $this->tryRender($aiScript, $primary, $post);

            // Nếu hỏng, thử lần 2 (Backup)
            if (!$result) {
                Log::warning("Video Provider [{$primary}] failed. Switching to [{$backup}].");
                $result = $this->tryRender($aiScript, $backup, $post);
            }

            // 3. Xử lý bản ghi cuối cùng
            if (!$result) {
                $user->increment('credits', $credits); // Hoàn tiền nếu cả 2 sập
                throw new \Exception("Tất cả dịch vụ Video AI hiện đang quá tải. Đã hoàn lại Credits.");
            }

            return VideoGeneration::create([
                'user_id' => $user->id,
                'social_post_id' => $post->id,
                'video_type' => $type,
                'credits_charged' => $credits,
                'ai_script' => $aiScript,
                'provider' => $result['provider'],
                'provider_request_id' => $result['id'],
                'status' => 'rendering',
            ]);

        } catch (\Exception $e) {
            Log::error("VideoFactory Critical Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Dùng OpenAI biến nội dung Post thành Prompt Cinematic
     */
    private function generateVideoPrompt(SocialPost $post, string $model): string
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system', 
                        'content' => 'You are a professional AI Video Director. Convert the user input into a detailed visual prompt for AI video generation. Output ONLY the visual prompt in English.'
                    ],
                    ['role' => 'user', 'content' => $post->content],
                ],
            ]);

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            Log::error("OpenAI Scripting Error: " . $e->getMessage());
            return $post->content; // Trả về nội dung gốc nếu AI lỗi
        }
    }

    /**
     * Gửi yêu cầu Render tới Replicate hoặc Kling
     */
    private function tryRender(string $prompt, string $provider, SocialPost $post): ?array
    {
        try {
            if ($provider === 'replicate') {
                $response = Http::withToken(env('REPLICATE_API_TOKEN'))
                    ->post('https://api.replicate.com/v1/predictions', [
                        'version' => "4f63125664d56155502a3d70284240763337489803a658762d0004f2d77b3174", // Luma Dream Machine
                        'input' => [
                            'prompt' => $prompt,
                            'aspect_ratio' => $post->platform === 'tiktok' ? '9:16' : '16:9',
                        ],
                        'webhook' => route('api.webhooks.video-ai'),
                        'webhook_events_filter' => ['completed']
                    ]);

                if ($response->successful()) {
                    return ['id' => $response->json('id'), 'provider' => 'replicate'];
                }
            }

            if ($provider === 'kling') {
                $response = Http::withHeaders([
                    'X-Kling-AccessKey' => env('KLING_ACCESS_KEY'),
                    'Content-Type' => 'application/json',
                ])->post('https://api.klingai.com/v1/videos', [
                    'model' => 'kling-v1',
                    'prompt' => $prompt,
                    'callback_url' => route('api.webhooks.video-ai'),
                    'config' => [
                        'aspect_ratio' => $post->platform === 'tiktok' ? '9:16' : '16:9',
                    ]
                ]);

                if ($response->successful()) {
                    return ['id' => $response->json('data.task_id'), 'provider' => 'kling'];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Render Provider [{$provider}] Error: " . $e->getMessage());
            return null;
        }
    }
}