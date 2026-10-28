<?php

namespace App\Exports;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Support\ProductFilter;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exports the product list, honouring the filters applied in the dashboard.
 *
 * Implements FromQuery rather than FromCollection so the rows stream in chunks
 * and the whole catalogue is never held in memory at once.
 */
class ProductsExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly ProductFilter $filter = new ProductFilter) {}

    /**
     * @return Builder<Product>
     */
    public function query(): Builder
    {
        // `newestFirst` orders by the primary key as well, giving the chunked
        // reader the unique sort it needs to avoid skipping or repeating rows.
        return Product::query()
            ->with('category')
            ->filtered($this->filter)
            ->newestFirst();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Category',
            'Description',
            'Price (MYR)',
            'Stock',
            'Status',
            'Created At',
        ];
    }

    /**
     * @param  Product  $row
     * @return array<int, mixed>
     */
    public function map(mixed $row): array
    {
        return [
            $row->id,
            $row->name,
            $row->category?->name,
            $row->description,
            (float) $row->price,
            $row->stock,
            ProductStatus::fromBoolean($row->enabled)->label(),
            $row->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    /**
     * @return array<int|string, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
