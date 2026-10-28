<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Support\ProductFilter;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'category_id', 'description', 'price', 'stock', 'enabled'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'enabled' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Apply every filter the caller supplied, skipping those left null.
     */
    #[Scope]
    protected function filtered(Builder $query, ProductFilter $filter): Builder
    {
        return $query
            ->when($filter->status, fn (Builder $query, ProductStatus $status) => $query->status($status))
            ->when($filter->categoryId, fn (Builder $query, int $categoryId) => $query->inCategory($categoryId))
            ->when($filter->search, fn (Builder $query, string $search) => $query->matching($search));
    }

    #[Scope]
    protected function status(Builder $query, ProductStatus $status): Builder
    {
        return $query->where('enabled', $status->toBoolean());
    }

    #[Scope]
    protected function inCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Match a free-text term against the product name and description.
     */
    #[Scope]
    protected function matching(Builder $query, string $search): Builder
    {
        // Escape the LIKE wildcards so a term containing "%" or "_" is matched
        // literally instead of broadening the search.
        $term = '%'.addcslashes($search, '%_\\').'%';

        return $query->where(fn (Builder $query) => $query
            ->where('name', 'like', $term)
            ->orWhere('description', 'like', $term));
    }

    /**
     * Newest first, with the primary key as a unique tie-breaker so that
     * pagination and chunked exports never skip or repeat a row.
     */
    #[Scope]
    protected function newestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }
}
