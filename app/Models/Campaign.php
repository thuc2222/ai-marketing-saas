<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'subject',
        'content',
        'status',
        'total_recipients',
        'sent_count',
        'open_count',
        'click_count',
        'bounce_count',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}