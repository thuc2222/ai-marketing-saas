<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class SePayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Log dữ liệu để debug (Xem SePay trả về cái gì)
        Log::info('SePay Webhook:', $request->all());

        // 2. Lấy thông tin từ Webhook SePay
        // SePay thường trả về: transactionDate, accountNumber, subAccount, amount, content, transferType...
        $amount = $request->input('transferAmount'); // Hoặc 'amount' tùy cấu hình SePay
        $content = $request->input('content'); 
        $transactionCode = $request->input('id'); // Mã tham chiếu ngân hàng

        // Kiểm tra trùng lặp (tránh cộng tiền 2 lần)
        if (Transaction::where('transaction_code', $transactionCode)->exists()) {
            return response()->json(['success' => true, 'message' => 'Transaction already processed']);
        }

        // 3. Phân tích nội dung chuyển khoản (Regex)
        // Cú pháp: SAAS U{id} P{plan_id} -> VD: "SAAS U1 P2"
        if (preg_match('/SAAS U(\d+) P(\d+)/i', $content, $matches)) {
            $userId = $matches[1];
            $planId = $matches[2];

            $user = User::find($userId);
            $plan = SubscriptionPlan::find($planId);

            if ($user && $plan) {
                // 4. CỘNG TIỀN / CẬP NHẬT GÓI CHO USER
                $user->update([
                    'subscription_plan_id' => $plan->id,
                    'credits' => $user->credits + $plan->monthly_credits, // Cộng dồn credits
                    // 'subscription_expires_at' => now()->addMonth(), // Nếu muốn tính ngày hết hạn
                ]);

                // 5. Lưu lịch sử giao dịch
                Transaction::create([
                    'user_id' => $user->id,
                    'subscription_plan_id' => $plan->id,
                    'amount' => $amount,
                    'transaction_code' => $transactionCode,
                    'content' => $content,
                    'status' => 'paid',
                ]);

                return response()->json(['success' => true, 'message' => 'Plan upgraded successfully']);
            }
        }

        return response()->json(['success' => false, 'message' => 'Invalid syntax']);
    }
}