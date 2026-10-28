<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation rules for creating and updating a product.
 *
 * Both operations accept the same attributes, so one request serves both.
 * Authorization is handled by the `admin` middleware on the route group.
 */
class ProductRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'description' => ['nullable', 'string', 'max:5000'],
            // `decimal:0,2` keeps the value within the two decimal places that
            // the products.price column stores.
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'enabled' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.exists' => 'The selected category no longer exists.',
            'price.decimal' => 'The price may have at most two decimal places.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
        ];
    }
}
