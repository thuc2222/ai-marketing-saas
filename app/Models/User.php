<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar; // <--- Import Interface Avatar
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

// Thêm ", HasAvatar" vào dòng này
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'phone',
        'company_name',
        'credits',
        'role',
        'subscription_plan_id',
        'subscription_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'subscription_expires_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true; 
    }

    // --- LOGIC HIỂN THỊ AVATAR (MỚI THÊM) ---
    public function getFilamentAvatarUrl(): ?string
    {
        // 1. Nếu user đã upload ảnh -> Lấy link ảnh từ Storage
        if ($this->avatar_url) {
            return Storage::url($this->avatar_url);
        }
        
        // 2. Nếu chưa có ảnh -> Dùng dịch vụ tạo ảnh từ tên viết tắt (UI Avatars)
        // Ví dụ: Tên "Nguyen Van A" -> Ảnh chữ "NA"
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&color=7F9CF5&background=EBF4FF";
    }

    // --- CÁC MỐI QUAN HỆ ---

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function socialAccounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }
    
    public function marketingPlans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MarketingPlan::class);
    }
}