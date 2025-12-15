<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\CertificationCompleted;
use App\Http\Requests\GateWebhookRequest;
use Illuminate\Http\JsonResponse;

class WebhookController extends Controller
{
    public function gate(GateWebhookRequest $request): JsonResponse
    {
        CertificationCompleted::fire(
            repository: $request->validated('repository'),
            sha: $request->validated('sha'),
            verdict: $request->validated('verdict'),
            reason: $request->validated('reason'),
            checks: $request->validated('checks'),
            triggeredBy: $request->validated('triggered_by'),
            prNumber: $request->validated('pr_number'),
        );

        return response()->json(['status' => 'accepted']);
    }
}
