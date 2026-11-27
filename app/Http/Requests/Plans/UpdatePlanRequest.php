<?php

declare(strict_types=1);

namespace App\Http\Requests\Plans;

use App\Enums\BillingInterval;
use App\Enums\CentralPermission;
use App\Enums\Currency;
use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePlanRequest extends FormRequest
{
    /**
     * The highest price, in major units, a plan can have.
     */
    private const int MAX_PRICE = 1000000;

    /**
     * The longest trial, in days.
     */
    private const int MAX_TRIAL_DAYS = 365;

    /**
     * The largest limit the database column holds.
     */
    private const int MAX_LIMIT = 2147483647;

    public function authorize(): bool
    {
        return $this->user()?->checkPermissionTo(CentralPermission::ManagePlans) ?? false;
    }

    /**
     * `features` lists every feature the plan includes, so one left out is removed. A feature can be listed only once.
     *
     * @return array<string, list<ValidationRule|object|string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique(Plan::class, 'code')->ignore($this->route('plan'))],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'decimal:0,2', 'min:0', 'max:'.self::MAX_PRICE],
            'currency' => ['required', Rule::enum(Currency::class)],
            'interval' => ['required', Rule::enum(BillingInterval::class)],
            'trial_days' => ['required', 'integer', 'min:0', 'max:'.self::MAX_TRIAL_DAYS],
            'features' => ['array'],
            'features.*.id' => ['required', 'integer', 'distinct', Rule::exists(Feature::class, 'id')],
            'features.*.limit' => ['nullable', 'integer', 'min:0', 'max:'.self::MAX_LIMIT],
        ];
    }
}
