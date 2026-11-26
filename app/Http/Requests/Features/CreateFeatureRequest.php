<?php

declare(strict_types=1);

namespace App\Http\Requests\Features;

use App\Enums\CentralPermission;
use App\Enums\LimitUnit;
use App\Models\Feature;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateFeatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->checkPermissionTo(CentralPermission::ManagePlans) ?? false;
    }

    /**
     * Code checks a feature by its key: lowercase letters, digits and underscores, starting with a letter.
     *
     * @return array<string, list<ValidationRule|object|string>>
     */
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:255', 'regex:/^[a-z][a-z0-9_]*$/', Rule::unique(Feature::class, 'key')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'limit_unit' => ['nullable', Rule::enum(LimitUnit::class)],
        ];
    }
}
