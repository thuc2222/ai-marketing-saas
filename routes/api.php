<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SePayWebhookController;
use App\Http\Controllers\Api\VideoWebhookController;
use App\Http\Controllers\EmailWebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/sepay/webhook', [SePayWebhookController::class, 'handle']);
Route::post('/webhooks/video-ai', [App\Http\Controllers\Api\VideoWebhookController::class, 'handle']);
Route::post('/webhooks/video-ai', [VideoWebhookController::class, 'handle'])
    ->name('api.webhooks.video-ai');
Route::post('/email-webhook', [EmailWebhookController::class, 'handle']);