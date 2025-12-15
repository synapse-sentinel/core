<?php

declare(strict_types=1);

namespace App\Events;

use Thunk\Verbs\Event;

class CertificationCompleted extends Event
{
    public function __construct(
        public string $repository,
        public string $sha,
        public string $verdict,
        public ?string $reason = null,
        public ?array $checks = null,
        public ?string $triggeredBy = null,
        public ?int $prNumber = null,
    ) {}
}
