<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GateWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'repository' => ['required', 'string'],
            'sha' => ['required', 'string'],
            'verdict' => ['required', Rule::in(['approved', 'rejected', 'escalate'])],
            'reason' => ['nullable', 'string'],
            'checks' => ['nullable', 'array'],
            'triggered_by' => ['nullable', 'string'],
            'pr_number' => ['nullable', 'integer'],
        ];
    }
}
