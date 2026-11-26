<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

final class ListTenantsRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
