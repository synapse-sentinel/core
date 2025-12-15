<?php

declare(strict_types=1);

namespace App\Webhooks;

use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class GateSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $signature = $request->header($config->signatureHeaderName);
        $secret = $config->signingSecret;

        if (empty($secret)) {
            return true;
        }

        if (empty($signature)) {
            return false;
        }

        $computedSignature = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($computedSignature, $signature);
    }
}
