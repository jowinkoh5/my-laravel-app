<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Http\Requests\IndexProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Support\ProductFilter;
use Illuminate\View\View;

/**
 * The public storefront.
 *
 * Open to guests and limited to enabled products; the status filter is forced
 * rather than taken from the request so a visitor cannot list hidden products.
 */
class CatalogController extends Controller
{
    public function index(IndexProductRequest $request): View
    {
        $requested = ProductFilter::fromRequest($request);

        $filter = new ProductFilter(
            status: ProductStatus::Enabled,
            categoryId: $requested->categoryId,
            search: $requested->search,
        );

        $products = Product::query()
            ->with('category')
            ->filtered($filter)
            ->newestFirst()
            ->paginate($request->perPage())
            ->withQueryString();

        return view('catalog.index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(),
            'filter' => $filter,
        ]);
    }

    /**
     * Show one enabled product.
     *
     * The status scope is part of the lookup, so a disabled product returns
     * 404 rather than revealing that it exists.
     */
    public function show(Product $product): View
    {
        abort_unless($product->enabled, 404);

        $product->load('category');

        return view('catalog.show', ['product' => $product]);
    }
}
