<?php

namespace App\Services;

use App\Models\MarketingPlan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class AiContentGenerator
{
    const TEXT_MODEL = 'gpt-4o-mini';
    const IMAGE_MODEL = 'dall-e-3';
    const TIMEOUT = 120;

    public function generate(MarketingPlan $plan): void
    {   
        $user = Auth::user();
        $days = $plan->start_date->diffInDays($plan->end_date) + 1;
        $systemPrompt = $this->buildSystemPrompt($plan);

        if ($user->credits < $days) {
            Notification::make()
                ->title('Credits not enough!')
                ->body("This plan needs {$days} credits, you have {$user->credits} left. Please upgrade.")
                ->danger()
                ->send();
            
            // Dừng luôn, không chạy nữa
            throw new \Exception("Not enough credits"); 
            // Hoặc return; tùy cách bạn handle lỗi ở Controller
        }

        for ($i = 0; $i < $days; $i++) {
            $currentDate = $plan->start_date->copy()->addDays($i);
            
            try {
                // 1. Generate Text
                $userPrompt = $this->buildUserPrompt($plan, $i + 1, $days);
                
                $response = Http::withToken(env('OPENAI_API_KEY'))
                    ->timeout(self::TIMEOUT)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => self::TEXT_MODEL,
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $userPrompt],
                        ],
                        'temperature' => 0.7,
                    ]);

                if ($response->failed()) {
                    Log::error("AI Error Day {$i}: " . $response->body());
                    continue;
                }

                // --- XỬ LÝ LÀM SẠCH NỘI DUNG ---
                $rawContent = $response->json('choices.0.message.content');
                $content = $this->cleanContent($rawContent);

                if (!$content) continue;

                // 2. Generate Image Prompt
                $imagePrompt = '';
                $imageUrl = null;

                // CHỈ TẠO ẢNH NẾU LÀ FACEBOOK
                if ($plan->platform === 'facebook') {
                    $imagePrompt = $this->generateImagePrompt($content, 'facebook');
                    
                    // Chỉ tạo ảnh DALL-E cho bài đầu tiên (để tiết kiệm tiền)
                    if ($i === 0) { 
                        $imageUrl = $this->generateDalleImage($imagePrompt);
                    }
                } 
                // NẾU LÀ TIKTOK -> KHÔNG LÀM GÌ CẢ (Để null)

                $user->decrement('credits', $days);
    
                // Update lại Plan
                $plan->update(['status' => 'generated']);

                // Save to DB
                $plan->posts()->create([
                    'platform' => $plan->platform,
                    'social_account_id' => $plan->social_account_id,
                    'content' => $content,
                    'image_prompt' => $imagePrompt,
                    // Lưu ảnh nếu có (chỉ Facebook), TikTok sẽ là null
                    'image_url' => $imageUrl ? [$imageUrl] : null, 
                    'scheduled_at' => $currentDate->setTime($plan->platform === 'tiktok' ? 19 : 9, 0),
                    'status' => 'draft',
                ]);

            } catch (\Exception $e) {
                Log::error("Exception Day {$i}: " . $e->getMessage());
            }
        }

        $plan->update(['status' => 'generated']);
    }

    private function buildSystemPrompt(MarketingPlan $plan): string
    {
        $voice = "Professional";
        if (!empty($plan->brand_voice) && is_array($plan->brand_voice)) {
            $voiceParts = [];
            foreach ($plan->brand_voice as $key => $value) $voiceParts[] = "{$key}: {$value}";
            $voice = implode(', ', $voiceParts);
        }
        
        $audience = is_array($plan->target_audience) 
            ? implode(', ', $plan->target_audience) 
            : ($plan->target_audience ?? 'General Public');

        return "You are an expert Social Media Manager. " .
               "Tone of Voice: {$voice}. Target Audience: {$audience}. " .
               "Language: Vietnamese. " .
               "STRICT RULE: Only return the post content. Do NOT include metadata labels like 'Tiêu đề:', 'Thân bài:', 'Hashtag:'. " .
               "Do NOT format labels with bolding (e.g., **Tiêu đề:**). Just write the content naturally.";
    }

    private function buildUserPrompt(MarketingPlan $plan, int $day, int $total): string
    {
        return "Write a Facebook post for Day {$day}/{$total} of campaign '{$plan->name}'. " .
               "Goal: {$plan->description}. " .
               "Format:\n" .
               "- Headline (Eye-catching, Uppercase or Bold, with Emoji)\n" .
               "- Empty line\n" .
               "- Body (Engaging paragraphs)\n" .
               "- Footer (3-5 Hashtags)\n\n" .
               "IMPORTANT: Do NOT output labels like 'Tiêu đề:', 'Title:', 'Subject:', 'Body:', 'Nội dung:'. Just start writing.";
    }

    private function generateImagePrompt(string $postContent): string
    {
        try {
            $response = Http::withToken(env('OPENAI_API_KEY'))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => self::TEXT_MODEL,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an Art Director. Create a detailed DALL-E 3 image prompt (English) based on this post.'],
                        ['role' => 'user', 'content' => Str::limit($postContent, 500)],
                    ],
                ]);
            return $response->json('choices.0.message.content') ?? '';
        } catch (\Exception $e) {
            return '';
        }
    }

    private function generateDalleImage(string $prompt): ?string
    {
        try {
            $response = Http::withToken(env('OPENAI_API_KEY'))
                ->timeout(60)
                ->post('https://api.openai.com/v1/images/generations', [
                    'model' => self::IMAGE_MODEL,
                    'prompt' => Str::limit($prompt, 1000),
                    'n' => 1,
                    'size' => '1024x1024',
                    'quality' => 'standard',
                ]);

            if ($response->failed()) return null;
            $tempUrl = $response->json('data.0.url');
            if (!$tempUrl) return null;

            $imageContent = Http::get($tempUrl)->body();
            $filename = 'post-images/' . Str::random(40) . '.png';
            Storage::disk('public')->put($filename, $imageContent);

            return $filename;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * NÂNG CẤP: Hàm làm sạch nội dung "Diệt tận gốc"
     */
    private function cleanContent(?string $text): string
    {
        if (empty($text)) return '';

        // Danh sách các từ khóa cần loại bỏ (Tiếng Việt + Tiếng Anh)
        $keywords = [
            'Tiêu đề', 'Title', 'Subject', 'Headline',
            'Thân bài', 'Body', 'Content', 'Nội dung', 'Mô tả',
            'Hashtags?', 'Hashtag', 'Từ khóa',
            'Kết bài', 'Kết luận', 'Conclusion', 'Footer'
        ];

        foreach ($keywords as $word) {
            // Case 1: Dấu hai chấm nằm TRONG phần bôi đậm (VD: **Tiêu đề:** hoặc **Title:**)
            // Regex: Tìm "**" + khoảng trắng + từ khóa + dấu hai chấm + "**"
            $text = preg_replace("/\*\*\s*{$word}\s*:\s*\*\*/iu", '', $text);
            
            // Case 2: Dấu hai chấm nằm NGOÀI hoặc không bôi đậm (VD: **Tiêu đề**: hoặc Tiêu đề:)
            // Regex: Tìm (bôi đậm tùy chọn) + từ khóa + (bôi đậm tùy chọn) + dấu hai chấm
            $text = preg_replace("/(?:\*\*|__)?\s*{$word}(?:\*\*|__)?\s*[:\-]\s*/iu", '', $text);
        }

        // Xóa các dấu ngoặc kép hoặc khoảng trắng thừa ở đầu cuối
        $text = trim($text, " \t\n\r\0\x0B\"'");

        return $text;
    }
}