<?php

declare(strict_types=1);

namespace App\Http\Requests\Domains;

use App\Enums\DomainSlot;
use App\Rules\TenantHostname;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

final class SaveDomainRequest extends FormRequest
{
    /**
     * @return array<string, list<ValidationRule|string|Enum>>
     */
    public function rules(): array
    {
        return [
            'domain' => ['bail', 'required', 'string', new TenantHostname(config('tenancy.identification.central_domains'))],
            'slot' => ['nullable', Rule::enum(DomainSlot::class)],
        ];
    }
}
