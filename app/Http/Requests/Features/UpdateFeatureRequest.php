<?php

declare(strict_types=1);

namespace App\Http\Requests\Features;

use App\Enums\CentralPermission;
use App\Enums\LimitUnit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateFeatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->checkPermissionTo(CentralPermission::ManagePlans) ?? false;
    }

    /**
     * The key isn't accepted: code checks the feature by it, so it never changes.
     *
     * @return array<string, list<ValidationRule|object|string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'limit_unit' => ['nullable', Rule::enum(LimitUnit::class)],
        ];
    }
}
