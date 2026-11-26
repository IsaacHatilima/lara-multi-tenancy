<?php

declare(strict_types=1);

namespace App\Http\Requests\Plans;

use App\Enums\BillingInterval;
use App\Enums\CentralPermission;
use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Rules\CurrencyCode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->checkPermissionTo(CentralPermission::ManagePlans) ?? false;
    }

    /**
     * A code can have only one active plan. The plan to start from is checked as a UUID first, which the exists query needs.
     *
     * @return array<string, list<ValidationRule|object|string>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', Rule::unique(Plan::class, 'code')->where('status', PlanStatus::Active->value)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'currency' => ['required', 'string', new CurrencyCode],
            'interval' => ['required', Rule::enum(BillingInterval::class)],
            'trial_days' => ['required', 'integer', 'min:0'],
            'is_public' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'start_from' => ['bail', 'nullable', 'uuid', Rule::exists(Plan::class, 'id')],
        ];
    }
}
