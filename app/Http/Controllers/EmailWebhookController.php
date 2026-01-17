<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscriber;
use App\Models\Campaign;

class EmailWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Example logic for generic provider
        $event = $request->input('event'); // 'bounce', 'open', 'click'
        $email = $request->input('email');
        $campaignId = $request->input('campaign_id'); // Passed via custom headers when sending

        if ($event === 'hard_bounce' || $event === 'spam_complaint') {
            // AUTO DELETE / INVALIDATE LOGIC
            Subscriber::where('email', $email)->delete(); 
            // Or set status to 'bounced' to keep record
            // Subscriber::where('email', $email)->update(['status' => 'bounced']);
            
            if ($campaignId) {
                Campaign::where('id', $campaignId)->increment('bounce_count');
            }
        }
        
        if ($event === 'open' && $campaignId) {
            Campaign::where('id', $campaignId)->increment('open_count');
        }

        return response()->json(['status' => 'ok']);
    }
}