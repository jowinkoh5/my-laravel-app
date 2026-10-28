<?php

namespace Tests\Feature\Exports;

use App\Enums\ProductStatus;
use App\Exports\ProductsExport;
use App\Models\Category;
use App\Models\Product;
use App\Support\ProductFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ProductsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_headings_describe_every_exported_column(): void
    {
        $this->assertSame(
            ['ID', 'Name', 'Category', 'Description', 'Price (MYR)', 'Stock', 'Status', 'Created At'],
            (new ProductsExport)->headings(),
        );
    }

    public function test_maps_a_product_onto_the_heading_columns(): void
    {
        $category = Category::factory()->create(['name' => 'Lighting']);
        $product = Product::factory()->for($category)->enabled()->create([
            'name' => 'Floor Lamp',
            'description' => 'Brass stem.',
            'price' => '129.50',
            'stock' => 6,
            'created_at' => '2026-01-02 03:04:05',
        ]);

        $row = (new ProductsExport)->map($product);

        $this->assertSame([
            $product->id,
            'Floor Lamp',
            'Lighting',
            'Brass stem.',
            129.50,
            6,
            'Enabled',
            '2026-01-02 03:04:05',
        ], $row);
    }

    public function test_maps_a_disabled_product_with_a_disabled_status(): void
    {
        $product = Product::factory()->disabled()->create();

        $this->assertSame(
            ProductStatus::Disabled->label(),
            (new ProductsExport)->map($product)[6],
        );
    }

    public function test_query_excludes_soft_deleted_products(): void
    {
        $live = Product::factory()->create();
        Product::factory()->trashed()->create();

        $this->assertSame([$live->id], (new ProductsExport)->query()->pluck('id')->all());
    }

    public function test_query_applies_the_supplied_filter(): void
    {
        $category = Category::factory()->create();
        $expected = Product::factory()->for($category)->enabled()->create();

        Product::factory()->for($category)->disabled()->create();
        Product::factory()->enabled()->create();

        $export = new ProductsExport(new ProductFilter(
            status: ProductStatus::Enabled,
            categoryId: $category->id,
        ));

        $this->assertSame([$expected->id], $export->query()->pluck('id')->all());
    }

    public function test_query_orders_by_the_primary_key_so_chunked_reads_are_stable(): void
    {
        $products = Product::factory()->count(3)->create(['created_at' => now()]);

        $this->assertSame(
            $products->pluck('id')->sortDesc()->values()->all(),
            (new ProductsExport)->query()->pluck('id')->all(),
        );
    }

    public function test_the_spreadsheet_is_generated_without_error(): void
    {
        Product::factory()->count(3)->create();

        $contents = Excel::raw(new ProductsExport, ExcelWriter::XLSX);

        $this->assertNotEmpty($contents);
    }
}
