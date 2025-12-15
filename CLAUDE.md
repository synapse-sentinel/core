# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**synapse-sentinel/core** is the event intake layer for the Synapse Sentinel ecosystem. It receives certification results from gate workflows and stores them as events using Laravel Verbs event sourcing.

Events are ground truth - all actions are stored immutably for the knowledge layer to synthesize.

## Development Commands

```bash
# Run tests
vendor/bin/pest

# Run tests with coverage
vendor/bin/pest --coverage --min=100

# Run specific test file
vendor/bin/pest tests/Feature/WebhookTest.php

# Code formatting
vendor/bin/pint
```

## Architecture

### Event Flow
```
Gate Workflow (GitHub Action)
    → POST /webhooks/gate
        → Store event (Laravel Verbs)
            → Project state
```

### Core Components

```
app/
├── Http/
│   └── Controllers/
│       └── WebhookController.php    # Receives gate results
├── Events/                          # Laravel Verbs events
│   ├── CertificationRequested.php
│   ├── CertificationCompleted.php
│   ├── CheckPassed.php
│   └── CheckFailed.php
├── States/                          # Verbs state projections
│   └── Repository.php               # Current certification status
└── Models/
    └── ...
```

### Webhook Payload (from gate)

```json
{
  "repository": "owner/repo",
  "sha": "abc123",
  "verdict": "approved|rejected|escalate",
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

## Testing Conventions

- All tests MUST use `describe()/it()` blocks (no `test()` functions)
- TDD: write failing tests first
- 100% coverage required (dogfooding our own gate)
- Spec as test: test descriptions are the specification

## License

GPL-3.0
