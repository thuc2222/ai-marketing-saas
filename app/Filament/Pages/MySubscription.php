<?php

namespace App\Filament\Pages;

use App\Models\SubscriptionPlan;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class MySubscription extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'My Subscription';
    protected static ?string $title = 'Account Setup';
    protected static string $view = 'filament.pages.my-subscription';
    protected static ?string $slug = 'my-subscription';
    protected static ?int $navigationSort = 10;

    protected function getViewData(): array
    {
        return [
            'user' => Auth::user(),
            'currentPlan' => Auth::user()->subscriptionPlan,
            'plans' => SubscriptionPlan::where('is_active', true)->get(),
        ];
    }

    public function upgradeAction(): Action
    {
        return Action::make('upgrade')
            ->label(__('Pay Now'))
            ->modalHeading(__('Scan QR Code to Pay'))
            ->modalDescription(__('System will auto-activate your plan within 1-3 minutes after payment.'))
            ->modalSubmitActionLabel(__('I have completed the transfer'))
            ->modalContent(function (array $arguments) {
                $plan = SubscriptionPlan::find($arguments['plan_id']);
                $user = Auth::user();
                
                // Syntax: SAAS U{id} P{plan_id}
                $orderInfo = "SAAS U{$user->id} P{$plan->id}";
                
                $bankAcc = '0123456789'; // Your Bank Account
                $bankCode = 'MB';        // Bank Code
                $amount = intval($plan->price); 
                // If price is in USD, convert to VND here: $amount = intval($plan->price) * 25000;

                $qrUrl = "https://qr.sepay.vn/img?acc={$bankAcc}&bank={$bankCode}&amount={$amount}&des={$orderInfo}";

                return view('filament.pages.components.qr-payment', [
                    'qrUrl' => $qrUrl,
                    'amount' => number_format($amount),
                    'content' => $orderInfo
                ]);
            })
            ->action(function () {
                Notification::make()->title(__('Checking transaction status...'))->success()->send();
                return redirect(request()->header('Referer'));
            });
    }
}