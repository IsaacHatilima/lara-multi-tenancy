<?php

declare(strict_types=1);

namespace App\Http\Requests\Plans;

use Illuminate\Foundation\Http\FormRequest;

final class ListPlansRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'filter' => ['nullable', 'array:search'],
            'filter.search' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
