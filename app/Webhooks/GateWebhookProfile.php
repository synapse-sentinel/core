<?php

declare(strict_types=1);

namespace App\Webhooks;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookProfile\WebhookProfile;

class GateWebhookProfile implements WebhookProfile
{
    public function shouldProcess(Request $request): bool
    {
        $payload = $request->all();

        if (empty($payload['repository']) || ! is_string($payload['repository'])) {
            return false;
        }

        if (empty($payload['sha']) || ! is_string($payload['sha'])) {
            return false;
        }

        if (empty($payload['verdict']) || ! in_array($payload['verdict'], ['approved', 'rejected', 'escalate'], true)) {
            return false;
        }

        return true;
    }
}
