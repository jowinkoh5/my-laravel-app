<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkDeleteProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductBulkDeleteController extends Controller
{
    /**
     * Soft delete every product in the supplied id list.
     *
     * Authorization and the id list are validated by the form request.
     */
    public function __invoke(BulkDeleteProductRequest $request): JsonResponse
    {
        $ids = $request->productIds();

        $deleted = Product::query()->whereIn('id', $ids)->delete();

        return response()->json([
            'message' => 'Products deleted.',
            'data' => [
                'deleted_count' => $deleted,
                'ids' => $ids,
            ],
        ]);
    }
}
