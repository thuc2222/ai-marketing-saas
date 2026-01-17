<?php

namespace App\Policies;

use App\Models\MarketingPlan;
use App\Models\User;

class MarketingPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MarketingPlan $marketingPlan): bool
    {
        return $user->id === $marketingPlan->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, MarketingPlan $marketingPlan): bool
    {
        return $user->id === $marketingPlan->user_id;
    }

    public function delete(User $user, MarketingPlan $marketingPlan): bool
    {
        return $user->id === $marketingPlan->user_id;
    }
}