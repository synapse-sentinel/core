<?php

declare(strict_types=1);

namespace App\Events;

use App\States\CertificationState;
use Carbon\CarbonImmutable;
use Thunk\Verbs\Attributes\Autodiscovery\StateId;
use Thunk\Verbs\Event;

class CertificationFailed extends Event
{
    #[StateId(CertificationState::class)]
    public ?int $certification_id = null;

    public function __construct(
        public string $repository,
        public string $sha,
        public string $error,
        public ?string $stage = null,
        public ?array $context = null,
    ) {}

    public function apply(CertificationState $state): void
    {
        $state->status = 'failed';
        $state->error = $this->error;
        $state->failed_at = CarbonImmutable::now();
    }
}
