<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenants;

use App\Concerns\TenantValidationRules;
use App\Rules\TenantHostname;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class CreateTenantRequest extends FormRequest
{
    use TenantValidationRules;

    /**
     * @return array<string, list<ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            ...$this->companyRules(),
            'domain' => ['bail', 'required', 'string', new TenantHostname(config('tenancy.identification.central_domains'))],
        ];
    }
}
