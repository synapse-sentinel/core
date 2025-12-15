<?php

declare(strict_types=1);

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/gate', [WebhookController::class, 'gate'])
    ->name('webhooks.gate');
