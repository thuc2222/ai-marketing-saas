<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoGeneration extends Model
{
    protected $fillable = [
        'user_id', 'social_post_id', 'video_type', 'credits_charged',
        'ai_script', 'status', 'provider', 'provider_request_id', 
        'result_url', 'error_message'
    ];

    public function socialPost(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}