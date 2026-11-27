<?php

declare(strict_types=1);

namespace App\Http\Requests\Plans;

use App\Enums\CentralPermission;
use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AddPlanFeatureRequest extends FormRequest
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
     * @return array<string, list<ValidationRule|object|string>>
     */
    public function rules(): array
    {
        /** @var Plan $plan */
        $plan = $this->route('plan');

        return [
            'feature_id' => [
                'required',
                'integer',
                Rule::exists(Feature::class, 'id'),
                Rule::unique('plan_feature', 'feature_id')->where('plan_id', $plan->id),
            ],
            'limit' => ['nullable', 'integer', 'min:0', 'max:'.self::MAX_LIMIT],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'feature_id.unique' => __('The plan already includes this feature.'),
        ];
    }
}
