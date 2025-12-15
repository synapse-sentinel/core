# Synapse Sentinel Core

Event intake system for Synapse Sentinel. Receives certification results from gate workflows and stores them as events using Laravel Verbs.

## Architecture

```
Gate Workflow → POST /api/webhooks/gate → Core
                                           ↓ stores events
                                      Laravel Verbs
                                           ↓ projects to
                                      CertificationState
```

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## Webhook Endpoint

### POST /api/webhooks/gate

Receives certification results from gate workflows.

**Headers:**
- `Content-Type: application/json`
- `X-Gate-Signature: <hmac-sha256>` (optional, required if `GATE_WEBHOOK_SECRET` is set)

**Request Body:**
```json
{
  "repository": "synapse-sentinel/core",
  "sha": "abc123def456",
  "verdict": "approved",
  "reason": "All checks passed",
  "checks": {
    "tests": {"status": "pass", "coverage": 100},
    "security": {"status": "pass"},
    "syntax": {"status": "pass"}
  },
  "triggered_by": "pull_request",
  "pr_number": 42
}
```

**Response:**
```json
{"message": "ok"}
```

## Event Schema

### CertificationRequested

Fired when a certification process begins.

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `repository` | string | yes | Full repository name (e.g., `owner/repo`) |
| `sha` | string | yes | Git commit SHA |
| `triggeredBy` | string | yes | What triggered the certification (`push`, `pull_request`, `workflow_dispatch`) |
| `prNumber` | int | no | Pull request number if triggered by PR |
| `branch` | string | no | Branch name |

### CertificationCompleted

Fired when certification finishes with a verdict.

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `repository` | string | yes | Full repository name |
| `sha` | string | yes | Git commit SHA |
| `verdict` | string | yes | Result: `approved`, `rejected`, or `escalate` |
| `reason` | string | no | Human-readable explanation |
| `checks` | object | no | Detailed check results |
| `triggeredBy` | string | no | What triggered the certification |
| `prNumber` | int | no | Pull request number |

### CertificationFailed

Fired when certification encounters an error (distinct from rejection).

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `repository` | string | yes | Full repository name |
| `sha` | string | yes | Git commit SHA |
| `error` | string | yes | Error message |
| `stage` | string | no | Which stage failed (`tests`, `build`, `security`) |
| `context` | object | no | Additional error context |

## State Projection

Events are projected to `CertificationState` which tracks:

- `status`: `pending`, `completed`, or `failed`
- `verdict`: `approved`, `rejected`, or `escalate` (when completed)
- `repository`, `sha`, `branch`, `pr_number`
- Timestamps: `requested_at`, `completed_at`, `failed_at`

Query state:
```php
$state = CertificationState::load($certification_id);
$state->isApproved();  // bool
$state->isRejected();  // bool
$state->isPending();   // bool
$state->isFailed();    // bool
```

## Testing

```bash
php artisan test
php artisan test --coverage
```

## License

GPL-3.0-or-later
