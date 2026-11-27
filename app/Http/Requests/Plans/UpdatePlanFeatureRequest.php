<?php

declare(strict_types=1);

namespace App\Http\Requests\Plans;

use App\Enums\CentralPermission;
use Illuminate\Foundation\Http\FormRequest;

final class UpdatePlanFeatureRequest extends FormRequest
{
    /**
     * The largest limit the database column holds.
     */
    private const int MAX_LIMIT = 2147483647;

    public function authorize(): bool
    {
        return $this->user()?->checkPermissionTo(CentralPermission::ManagePlans) ?? false;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'limit' => ['nullable', 'integer', 'min:0', 'max:'.self::MAX_LIMIT],
        ];
    }
}
