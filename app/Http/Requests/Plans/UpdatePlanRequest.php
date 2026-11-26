<?php

declare(strict_types=1);

namespace App\Http\Requests\Plans;

use App\Enums\CentralPermission;
use App\Models\Feature;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->checkPermissionTo(CentralPermission::ManagePlans) ?? false;
    }

    /**
     * `features` lists every feature the plan includes, so one left out is removed. A key can be listed only once.
     *
     * @return array<string, list<ValidationRule|object|string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'features' => ['array'],
            'features.*.key' => ['required', 'string', 'distinct', Rule::exists(Feature::class, 'key')],
            'features.*.limit' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
