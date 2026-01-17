<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['user_id', 'subscription_plan_id', 'amount', 'transaction_code', 'content', 'status'];
}
