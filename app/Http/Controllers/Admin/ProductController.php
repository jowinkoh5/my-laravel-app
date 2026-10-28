<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexProductRequest;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Support\ProductFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Show the paginated, filterable product list.
     */
    public function index(IndexProductRequest $request): View
    {
        $filter = ProductFilter::fromRequest($request);

        $products = Product::query()
            ->with('category')
            ->filtered($filter)
            ->newestFirst()
            ->paginate($request->perPage())
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => $this->categoriesForSelect(),
            'filter' => $filter,
            'statuses' => ProductStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => $this->categoriesForSelect(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = Product::create($request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('status', "Product \"{$product->name}\" was created.");
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $this->categoriesForSelect(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('status', "Product \"{$product->name}\" was updated.");
    }

    /**
     * Soft delete a single product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', "Product \"{$product->name}\" was deleted.");
    }

    /**
     * Categories keyed by id for the form's select element.
     *
     * @return Collection<int, string>
     */
    private function categoriesForSelect(): Collection
    {
        return Category::query()->orderBy('name')->pluck('name', 'id');
    }
}
