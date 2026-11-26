<?php

declare(strict_types=1);

namespace App\Http\Requests\Plans;

use App\Enums\CentralPermission;
use App\Models\Plan;
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
     * @return array<string, list<ValidationRule|object|string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique(Plan::class, 'code')],
            'description' => ['nullable', 'string'],
        ];
    }
}
