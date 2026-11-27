<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenants;

use App\Concerns\DomainValidationRules;
use App\Concerns\TenantValidationRules;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class CreateTenantRequest extends FormRequest
{
    use DomainValidationRules, TenantValidationRules;

    /**
     * @return array<string, list<Closure|ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            ...$this->companyRules(),
            'domain' => $this->domainRules(),
        ];
    }
}
