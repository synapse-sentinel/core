<?php

declare(strict_types=1);

namespace App\Webhooks;

use App\Events\CertificationCompleted;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class ProcessGateWebhookJob extends ProcessWebhookJob
{
    public function handle(): void
    {
        $payload = $this->webhookCall->payload;

        CertificationCompleted::fire(
            repository: $payload['repository'],
            sha: $payload['sha'],
            verdict: $payload['verdict'],
            reason: $payload['reason'] ?? null,
            checks: $payload['checks'] ?? null,
            triggeredBy: $payload['triggered_by'] ?? null,
            prNumber: isset($payload['pr_number']) ? (int) $payload['pr_number'] : null,
        );
    }
}
