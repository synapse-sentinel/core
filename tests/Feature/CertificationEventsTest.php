<?php

declare(strict_types=1);

use App\Events\CertificationCompleted;
use App\Events\CertificationFailed;
use App\Events\CertificationRequested;
use App\States\CertificationState;
use Thunk\Verbs\Facades\Verbs;

describe('CertificationRequested', function () {
    it('creates a pending certification state', function () {
        $event = CertificationRequested::fire(
            repository: 'synapse-sentinel/core',
            sha: 'abc123',
            triggeredBy: 'pull_request',
            prNumber: 42,
            branch: 'feature/test',
        );

        $state = CertificationState::load($event->certification_id);

        expect($state->repository)->toBe('synapse-sentinel/core');
        expect($state->sha)->toBe('abc123');
        expect($state->triggered_by)->toBe('pull_request');
        expect($state->pr_number)->toBe(42);
        expect($state->branch)->toBe('feature/test');
        expect($state->status)->toBe('pending');
        expect($state->isPending())->toBeTrue();
        expect($state->requested_at)->not->toBeNull();
    });

    it('stores event in verb_events table', function () {
        CertificationRequested::fire(
            repository: 'synapse-sentinel/core',
            sha: 'def456',
            triggeredBy: 'push',
        );

        Verbs::commit();

        $this->assertDatabaseHas('verb_events', [
            'type' => CertificationRequested::class,
        ]);
    });
});

describe('CertificationCompleted', function () {
    it('updates state to completed with verdict', function () {
        $requested = CertificationRequested::fire(
            repository: 'synapse-sentinel/core',
            sha: 'abc123',
            triggeredBy: 'pull_request',
        );

        CertificationCompleted::fire(
            certification_id: $requested->certification_id,
            repository: 'synapse-sentinel/core',
            sha: 'abc123',
            verdict: 'approved',
            reason: 'All checks passed',
            checks: ['tests' => 'pass', 'coverage' => 100],
        );

        $state = CertificationState::load($requested->certification_id);

        expect($state->status)->toBe('completed');
        expect($state->verdict)->toBe('approved');
        expect($state->reason)->toBe('All checks passed');
        expect($state->checks)->toBe(['tests' => 'pass', 'coverage' => 100]);
        expect($state->isCompleted())->toBeTrue();
        expect($state->isApproved())->toBeTrue();
        expect($state->completed_at)->not->toBeNull();
    });

    it('can mark certification as rejected', function () {
        $requested = CertificationRequested::fire(
            repository: 'synapse-sentinel/core',
            sha: 'abc123',
            triggeredBy: 'pull_request',
        );

        CertificationCompleted::fire(
            certification_id: $requested->certification_id,
            repository: 'synapse-sentinel/core',
            sha: 'abc123',
            verdict: 'rejected',
            reason: 'Coverage below threshold',
        );

        $state = CertificationState::load($requested->certification_id);

        expect($state->isRejected())->toBeTrue();
        expect($state->isApproved())->toBeFalse();
    });

    it('can be fired without prior request for webhook intake', function () {
        CertificationCompleted::fire(
            repository: 'external/repo',
            sha: 'xyz789',
            verdict: 'approved',
        );

        Verbs::commit();

        $this->assertDatabaseHas('verb_events', [
            'type' => CertificationCompleted::class,
        ]);
    });
});

describe('CertificationFailed', function () {
    it('updates state to failed with error', function () {
        $requested = CertificationRequested::fire(
            repository: 'synapse-sentinel/core',
            sha: 'abc123',
            triggeredBy: 'pull_request',
        );

        CertificationFailed::fire(
            certification_id: $requested->certification_id,
            repository: 'synapse-sentinel/core',
            sha: 'abc123',
            error: 'Runner crashed unexpectedly',
            stage: 'tests',
            context: ['exit_code' => 137],
        );

        $state = CertificationState::load($requested->certification_id);

        expect($state->status)->toBe('failed');
        expect($state->error)->toBe('Runner crashed unexpectedly');
        expect($state->isFailed())->toBeTrue();
        expect($state->isCompleted())->toBeFalse();
        expect($state->failed_at)->not->toBeNull();
    });

    it('stores event with context', function () {
        CertificationFailed::fire(
            repository: 'synapse-sentinel/core',
            sha: 'abc123',
            error: 'Timeout',
            stage: 'build',
            context: ['timeout_seconds' => 300],
        );

        Verbs::commit();

        $this->assertDatabaseHas('verb_events', [
            'type' => CertificationFailed::class,
        ]);
    });
});
