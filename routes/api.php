<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeadApiController;
use App\Http\Controllers\Api\WebhookApiController;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/whatsapp/webhook', [WebhookApiController::class, 'whatsapp']);
    Route::get('/whatsapp/webhook', [WebhookApiController::class, 'verifyWhatsapp']);
    Route::post('/lead-capture', [LeadApiController::class, 'capture']);
    Route::post('/webhooks/incoming/{token}', [WebhookApiController::class, 'incoming']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/user', [AuthController::class, 'user']);
        Route::apiResource('leads', LeadApiController::class);
        Route::post('/leads/import', [LeadApiController::class, 'import']);
        Route::get('/leads/export', [LeadApiController::class, 'export']);
        Route::post('/leads/{lead}/assign', [LeadApiController::class, 'assign']);
        Route::post('/leads/{lead}/convert', [LeadApiController::class, 'convert']);
    });
});
