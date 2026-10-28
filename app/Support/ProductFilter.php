<?php

namespace App\Support;

use App\Enums\ProductStatus;
use App\Http\Requests\IndexProductRequest;
use Illuminate\Http\Request;

/**
 * The set of filters the product list accepts.
 *
 * The admin dashboard, the public catalogue, the JSON API and the Excel export
 * all narrow the same product list, so the filter travels as one object rather
 * than as a different handful of loose arguments at each call site.
 */
final readonly class ProductFilter
{
    public function __construct(
        public ?ProductStatus $status = null,
        public ?int $categoryId = null,
        public ?string $search = null,
    ) {}

    /**
     * Build the filter from an already-validated request.
     *
     * The caller is responsible for validating the input first — see
     * {@see IndexProductRequest}.
     */
    public static function fromRequest(Request $request): self
    {
        $search = trim((string) $request->string('search'));

        return new self(
            status: $request->enum('status', ProductStatus::class),
            categoryId: $request->integer('category_id') ?: null,
            search: $search === '' ? null : $search,
        );
    }

    /**
     * The filter as query-string parameters, for pagination and export links.
     *
     * @return array<string, string|int>
     */
    public function toQueryParameters(): array
    {
        return array_filter([
            'status' => $this->status?->value,
            'category_id' => $this->categoryId,
            'search' => $this->search,
        ], fn (mixed $value): bool => $value !== null);
    }
}
