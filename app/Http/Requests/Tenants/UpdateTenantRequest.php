<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenants;

use App\Concerns\TenantValidationRules;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateTenantRequest extends FormRequest
{
    use TenantValidationRules;

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return $this->companyRules();
    }
}
