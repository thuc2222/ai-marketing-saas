<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\SocialAccount;

class SocialPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'marketing_plan_id',
        'platform',
        'content',
        'image_prompt',
        'image_url',
        'video_url',
        'scheduled_at',
        'status',
        'social_api_response',
        'social_account_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'social_api_response' => 'array',
        'image_url' => 'array',
    ];

    public function socialAccount()
    {
        return $this->belongsTo(SocialAccount::class);
    }

    public function marketingPlan(): BelongsTo
    {
        return $this->belongsTo(MarketingPlan::class);
    }
}