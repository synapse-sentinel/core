<?php

declare(strict_types=1);

namespace App\States;

use Carbon\CarbonImmutable;
use Thunk\Verbs\State;

class CertificationState extends State
{
    public string $repository = '';

    public string $sha = '';

    public string $status = 'pending';

    public ?string $verdict = null;

    public ?string $reason = null;

    public ?string $error = null;

    public ?string $triggered_by = null;

    public ?int $pr_number = null;

    public ?string $branch = null;

    public ?array $checks = null;

    public ?CarbonImmutable $requested_at = null;

    public ?CarbonImmutable $completed_at = null;

    public ?CarbonImmutable $failed_at = null;

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isApproved(): bool
    {
        return $this->isCompleted() && $this->verdict === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->isCompleted() && $this->verdict === 'rejected';
    }
}
