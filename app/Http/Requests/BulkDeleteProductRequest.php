<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkDeleteProductRequest extends FormRequest
{
    /**
     * The largest number of products one request may delete, which bounds the
     * size of the `WHERE id IN (...)` statement.
     */
    public const int MAX_IDS = 500;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1', 'max:'.self::MAX_IDS],
            'ids.*' => [
                'integer',
                // Soft-deleted rows remain in the table, so they are excluded
                // here to keep an already-deleted id from reporting success.
                Rule::exists('products', 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ids.required' => 'Select at least one product to delete.',
            'ids.*.exists' => 'One of the selected products no longer exists.',
        ];
    }

    /**
     * @return array<int, int>
     */
    public function productIds(): array
    {
        return array_map('intval', $this->validated('ids'));
    }
}
