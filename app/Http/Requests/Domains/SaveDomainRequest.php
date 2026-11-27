<?php

declare(strict_types=1);

namespace App\Http\Requests\Domains;

use App\Concerns\DomainValidationRules;
use App\Enums\DomainSlot;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

final class SaveDomainRequest extends FormRequest
{
    use DomainValidationRules;

    /**
     * @return array<string, list<Closure|ValidationRule|string|Enum>>
     */
    public function rules(): array
    {
        return [
            'domain' => $this->domainRules(),
            'slot' => ['nullable', Rule::enum(DomainSlot::class)],
        ];
    }
}
