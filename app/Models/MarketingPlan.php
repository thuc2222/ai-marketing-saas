<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'campaign_goal',
        'target_audience', 
        'brand_voice',
        'competitors',
        'content_pillars',
        'budget',
        'expected_revenue',
        'estimated_roi',
        'stage',
        'start_date',
        'end_date',
        'ai_strategy_advice',
        'channels',     // <--- Mới
        'kpi_targets',  // <--- Mới
    ];

    protected $casts = [
        'content_pillars' => 'array',
        'competitors' => 'array',
        'channels' => 'array',      // <--- Bắt buộc ép kiểu mảng
        'kpi_targets' => 'array',   // <--- Bắt buộc ép kiểu mảng
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'expected_revenue' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(SocialPost::class);
    }
}