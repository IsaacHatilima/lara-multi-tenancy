<?php

declare(strict_types=1);

namespace App\Http\Requests\Plans;

use Illuminate\Foundation\Http\FormRequest;

final class SearchPlanFeaturesRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}
