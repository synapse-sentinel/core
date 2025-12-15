<?php

declare(strict_types=1);

namespace App\Events;

use App\States\CertificationState;
use Carbon\CarbonImmutable;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class CertificationRequested extends Event
{
    #[StateId(CertificationState::class)]
    public ?int $certification_id = null;

    public function __construct(
        public string $repository,
        public string $sha,
        public string $triggeredBy,
        public ?int $prNumber = null,
        public ?string $branch = null,
    ) {}

    public function apply(CertificationState $state): void
    {
        $state->repository = $this->repository;
        $state->sha = $this->sha;
        $state->triggered_by = $this->triggeredBy;
        $state->pr_number = $this->prNumber;
        $state->branch = $this->branch;
        $state->status = 'pending';
        $state->requested_at = CarbonImmutable::now();
    }
}
