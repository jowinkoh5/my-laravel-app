<?php

namespace App\Http\Requests;

use App\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the filter and pagination parameters of a product listing.
 *
 * Authorization is handled by the route's middleware and by the controller's
 * policy check, because the same parameters serve the admin dashboard, the
 * public catalogue and the JSON API.
 */
class IndexProductRequest extends FormRequest
{
    /**
     * The largest page size a client may request.
     */
    public const int MAX_PER_PAGE = 100;

    public const int DEFAULT_PER_PAGE = 15;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(ProductStatus::class)],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.self::MAX_PER_PAGE],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * The validated page size, falling back to the default.
     */
    public function perPage(): int
    {
        return $this->integer('per_page') ?: self::DEFAULT_PER_PAGE;
    }
}
