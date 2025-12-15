<?php

declare(strict_types=1);

use Spatie\WebhookClient\Models\WebhookCall;

describe('POST /api/webhooks/gate', function () {
    it('accepts valid gate certification payload', function () {
        $payload = [
            'repository' => 'conduit-ui/pr',
            'sha' => 'abc123def456',
            'verdict' => 'approved',
            'reason' => 'All checks passed',
            'checks' => [
                'tests' => ['status' => 'pass', 'coverage' => 100],
                'security' => ['status' => 'pass'],
                'syntax' => ['status' => 'pass'],
            ],
            'triggered_by' => 'pull_request',
            'pr_number' => 42,
        ];

        $response = $this->postJson('/api/webhooks/gate', $payload);

        $response->assertSuccessful();
        $response->assertJson(['message' => 'ok']);
    });

    it('does not process payload missing required repository field', function () {
        $payload = [
            'sha' => 'abc123',
            'verdict' => 'approved',
        ];

        $response = $this->postJson('/api/webhooks/gate', $payload);

        $response->assertSuccessful();

        $this->assertDatabaseMissing('webhook_calls', [
            'name' => 'gate',
        ]);

        $this->assertDatabaseMissing('verb_events', [
            'type' => 'App\Events\CertificationCompleted',
        ]);
    });

    it('does not process payload with invalid verdict value', function () {
        $payload = [
            'repository' => 'conduit-ui/pr',
            'sha' => 'abc123',
            'verdict' => 'invalid_verdict',
        ];

        $response = $this->postJson('/api/webhooks/gate', $payload);

        $response->assertSuccessful();

        $this->assertDatabaseMissing('webhook_calls', [
            'name' => 'gate',
        ]);

        $this->assertDatabaseMissing('verb_events', [
            'type' => 'App\Events\CertificationCompleted',
        ]);
    });

    it('stores webhook call in database', function () {
        $payload = [
            'repository' => 'conduit-ui/pr',
            'sha' => 'abc123def456',
            'verdict' => 'approved',
        ];

        $response = $this->postJson('/api/webhooks/gate', $payload);

        $response->assertSuccessful();

        $this->assertDatabaseHas('webhook_calls', [
            'name' => 'gate',
        ]);

        $webhookCall = WebhookCall::first();
        expect($webhookCall->payload)->toMatchArray([
            'repository' => 'conduit-ui/pr',
            'sha' => 'abc123def456',
            'verdict' => 'approved',
        ]);
    });

    it('stores certification event via Laravel Verbs', function () {
        $payload = [
            'repository' => 'conduit-ui/pr',
            'sha' => 'abc123def456',
            'verdict' => 'approved',
            'reason' => 'All checks passed',
            'checks' => [
                'tests' => ['status' => 'pass', 'coverage' => 100],
            ],
            'triggered_by' => 'push',
        ];

        $response = $this->postJson('/api/webhooks/gate', $payload);

        $response->assertSuccessful();

        $this->assertDatabaseHas('verb_events', [
            'type' => 'App\Events\CertificationCompleted',
        ]);
    });

    it('does not process payload missing required sha field', function () {
        $payload = [
            'repository' => 'conduit-ui/pr',
            'verdict' => 'approved',
        ];

        $response = $this->postJson('/api/webhooks/gate', $payload);

        $response->assertSuccessful();

        $this->assertDatabaseMissing('webhook_calls', [
            'name' => 'gate',
        ]);
    });

    it('does not process payload missing required verdict field', function () {
        $payload = [
            'repository' => 'conduit-ui/pr',
            'sha' => 'abc123',
        ];

        $response = $this->postJson('/api/webhooks/gate', $payload);

        $response->assertSuccessful();

        $this->assertDatabaseMissing('webhook_calls', [
            'name' => 'gate',
        ]);
    });
});
