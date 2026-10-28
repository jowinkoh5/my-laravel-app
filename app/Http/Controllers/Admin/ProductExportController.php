<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexProductRequest;
use App\Support\ProductFilter;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductExportController extends Controller
{
    /**
     * Download the filtered product list as an .xlsx spreadsheet.
     *
     * Reuses IndexProductRequest so the export honours exactly the filters
     * that produced the list the administrator is looking at.
     */
    public function __invoke(IndexProductRequest $request): BinaryFileResponse
    {
        $filename = 'products-'.now()->format('Y-m-d-His').'.xlsx';

        return Excel::download(new ProductsExport(ProductFilter::fromRequest($request)), $filename);
    }
}
