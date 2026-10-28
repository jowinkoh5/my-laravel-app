<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkDeleteProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;

class ProductBulkDeleteController extends Controller
{
    /**
     * Soft delete every selected product in one statement.
     *
     * Authorization and the id list are validated by the form request.
     */
    public function __invoke(BulkDeleteProductRequest $request): RedirectResponse
    {
        $deleted = Product::query()->whereIn('id', $request->productIds())->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', trans_choice(':count product was deleted.|:count products were deleted.', $deleted, ['count' => $deleted]));
    }
}
