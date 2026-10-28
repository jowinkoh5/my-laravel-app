<?php

namespace Tests\Feature\Models;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Support\ProductFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the model's casts, relationship and query scopes, which the
 * dashboard, catalogue, API and export all depend on.
 */
class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_its_category(): void
    {
        $category = Category::factory()->create(['name' => 'Garden']);
        $product = Product::factory()->for($category)->create();

        $this->assertTrue($product->category->is($category));
        $this->assertSame('Garden', $product->category->name);
    }

    public function test_a_category_has_many_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->count(3)->create();
        Product::factory()->create();

        $this->assertCount(3, $category->products);
    }

    public function test_casts_price_to_a_two_place_decimal_string(): void
    {
        $product = Product::factory()->create(['price' => 5]);

        $this->assertSame('5.00', $product->fresh()->price);
    }

    public function test_casts_stock_to_an_integer_and_enabled_to_a_boolean(): void
    {
        $product = Product::factory()->create(['stock' => '12', 'enabled' => 1])->fresh();

        $this->assertSame(12, $product->stock);
        $this->assertTrue($product->enabled);
    }

    public function test_deleting_keeps_the_row_and_hides_it_from_queries(): void
    {
        $product = Product::factory()->create();

        $product->delete();

        $this->assertSoftDeleted($product);
        $this->assertSame(0, Product::count());
        $this->assertSame(1, Product::withTrashed()->count());
    }

    public function test_status_scope_returns_only_enabled_products(): void
    {
        $enabled = Product::factory()->enabled()->create();
        Product::factory()->disabled()->create();

        $results = Product::query()->status(ProductStatus::Enabled)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($enabled));
    }

    public function test_status_scope_returns_only_disabled_products(): void
    {
        Product::factory()->enabled()->create();
        $disabled = Product::factory()->disabled()->create();

        $results = Product::query()->status(ProductStatus::Disabled)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($disabled));
    }

    public function test_in_category_scope_restricts_to_one_category(): void
    {
        $category = Category::factory()->create();
        $expected = Product::factory()->for($category)->create();
        Product::factory()->create();

        $results = Product::query()->inCategory($category->id)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($expected));
    }

    public function test_matching_scope_searches_the_name_and_the_description(): void
    {
        $byName = Product::factory()->create(['name' => 'Copper Kettle', 'description' => 'nothing']);
        $byDescription = Product::factory()->create(['name' => 'Teapot', 'description' => 'brushed copper body']);
        Product::factory()->create(['name' => 'Toaster', 'description' => 'steel']);

        $ids = Product::query()->matching('copper')->pluck('id');

        $this->assertCount(2, $ids);
        $this->assertTrue($ids->contains($byName->id));
        $this->assertTrue($ids->contains($byDescription->id));
    }

    public function test_matching_scope_treats_a_percent_sign_as_a_literal_character(): void
    {
        $literal = Product::factory()->create(['name' => 'Save 50% Bundle', 'description' => 'x']);
        Product::factory()->create(['name' => 'Plain Bundle', 'description' => 'y']);

        $results = Product::query()->matching('50%')->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($literal));
    }

    public function test_matching_scope_treats_an_underscore_as_a_literal_character(): void
    {
        $literal = Product::factory()->create(['name' => 'model_x', 'description' => 'x']);
        Product::factory()->create(['name' => 'modelAx', 'description' => 'y']);

        $results = Product::query()->matching('model_')->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($literal));
    }

    public function test_newest_first_scope_orders_by_creation_date_then_id(): void
    {
        $older = Product::factory()->create(['created_at' => now()->subDay()]);
        $newerA = Product::factory()->create(['created_at' => now()]);
        $newerB = Product::factory()->create(['created_at' => now()]);

        $ids = Product::query()->newestFirst()->pluck('id')->all();

        // Same timestamp: the higher id wins the tie, so ordering is stable.
        $this->assertSame([
            max($newerA->id, $newerB->id),
            min($newerA->id, $newerB->id),
            $older->id,
        ], $ids);
    }

    public function test_filtered_scope_with_no_filters_returns_everything(): void
    {
        Product::factory()->enabled()->count(2)->create();
        Product::factory()->disabled()->create();

        $this->assertSame(3, Product::query()->filtered(new ProductFilter)->count());
    }

    public function test_filtered_scope_combines_status_category_and_search(): void
    {
        $category = Category::factory()->create();

        $expected = Product::factory()->for($category)->enabled()->create(['name' => 'Walnut Stool']);

        // Each of these fails exactly one of the three filters.
        Product::factory()->for($category)->disabled()->create(['name' => 'Walnut Stool Spare']);
        Product::factory()->enabled()->create(['name' => 'Walnut Stool Clone']);
        Product::factory()->for($category)->enabled()->create(['name' => 'Oak Bench']);

        $results = Product::query()->filtered(new ProductFilter(
            status: ProductStatus::Enabled,
            categoryId: $category->id,
            search: 'walnut',
        ))->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($expected));
    }
}
