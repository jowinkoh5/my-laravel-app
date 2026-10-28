<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexProductRequest;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Support\ProductFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Product endpoints for authenticated administrators.
 *
 * Every response is shaped by {@see ProductResource}, which embeds the
 * category's id and name.
 */
class ProductController extends Controller
{
    /**
     * List products, optionally filtered by category, status or search term.
     */
    public function index(IndexProductRequest $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->with('category')
            ->filtered(ProductFilter::fromRequest($request))
            ->newestFirst()
            ->paginate($request->perPage())
            ->withQueryString();

        return ProductResource::collection($products);
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return ProductResource::make($product->load('category'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Product $product): ProductResource
    {
        return ProductResource::make($product->load('category'));
    }

    /**
     * Soft delete a product.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted.',
            'data' => ['id' => $product->id],
        ]);
    }
}
