<?php

declare(strict_types=1);

use App\Webhooks\GateSignatureValidator;
use App\Webhooks\ProcessGateWebhookJob;
use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;

describe('GateSignatureValidator', function () {
    it('allows requests when no secret is configured', function () {
        $validator = new GateSignatureValidator;
        $request = Request::create('/webhook', 'POST', [], [], [], [], '{"test": "data"}');
        $config = new WebhookConfig([
            'name' => 'gate',
            'signing_secret' => '',
            'signature_header_name' => 'X-Gate-Signature',
            'signature_validator' => GateSignatureValidator::class,
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,
            'store_headers' => [],
            'process_webhook_job' => ProcessGateWebhookJob::class,
        ]);

        expect($validator->isValid($request, $config))->toBeTrue();
    });

    it('rejects requests with missing signature when secret is configured', function () {
        $validator = new GateSignatureValidator;
        $request = Request::create('/webhook', 'POST', [], [], [], [], '{"test": "data"}');
        $config = new WebhookConfig([
            'name' => 'gate',
            'signing_secret' => 'my-secret-key',
            'signature_header_name' => 'X-Gate-Signature',
            'signature_validator' => GateSignatureValidator::class,
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,
            'store_headers' => [],
            'process_webhook_job' => ProcessGateWebhookJob::class,
        ]);

        expect($validator->isValid($request, $config))->toBeFalse();
    });

    it('rejects requests with invalid signature', function () {
        $validator = new GateSignatureValidator;
        $request = Request::create('/webhook', 'POST', [], [], [], [
            'HTTP_X_GATE_SIGNATURE' => 'invalid-signature',
        ], '{"test": "data"}');
        $config = new WebhookConfig([
            'name' => 'gate',
            'signing_secret' => 'my-secret-key',
            'signature_header_name' => 'X-Gate-Signature',
            'signature_validator' => GateSignatureValidator::class,
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,
            'store_headers' => [],
            'process_webhook_job' => ProcessGateWebhookJob::class,
        ]);

        expect($validator->isValid($request, $config))->toBeFalse();
    });

    it('accepts requests with valid HMAC signature', function () {
        $validator = new GateSignatureValidator;
        $payload = '{"test": "data"}';
        $secret = 'my-secret-key';
        $validSignature = hash_hmac('sha256', $payload, $secret);

        $request = Request::create('/webhook', 'POST', [], [], [], [
            'HTTP_X_GATE_SIGNATURE' => $validSignature,
        ], $payload);
        $config = new WebhookConfig([
            'name' => 'gate',
            'signing_secret' => $secret,
            'signature_header_name' => 'X-Gate-Signature',
            'signature_validator' => GateSignatureValidator::class,
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,
            'store_headers' => [],
            'process_webhook_job' => ProcessGateWebhookJob::class,
        ]);

        expect($validator->isValid($request, $config))->toBeTrue();
    });
});
