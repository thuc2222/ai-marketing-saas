<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'monthly_credits',
        'features',
        'stripe_price_id',
        'is_active',
    ];

    protected $casts = [
        // Quan trọng: Ép kiểu features từ JSON trong DB thành Array để code dùng được
        'features' => 'array', 
        
        // Đảm bảo các trường khác đúng kiểu
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'monthly_credits' => 'integer',
    ];
}