<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_the_catalogue_for_a_guest(): void
    {
        Product::factory()->enabled()->create(['name' => 'Linen Shirt']);

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Linen Shirt');
    }

    public function test_hides_disabled_products_from_the_catalogue(): void
    {
        Product::factory()->disabled()->create(['name' => 'Draft Product']);

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertDontSee('Draft Product');
    }

    public function test_hides_soft_deleted_products_from_the_catalogue(): void
    {
        Product::factory()->trashed()->create(['name' => 'Removed Product']);

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertDontSee('Removed Product');
    }

    public function test_a_visitor_cannot_reveal_disabled_products_through_the_status_filter(): void
    {
        Product::factory()->disabled()->create(['name' => 'Draft Product']);
        Product::factory()->enabled()->create(['name' => 'Live Product']);

        $this->get(route('catalog.index', ['status' => 'disabled']))
            ->assertOk()
            ->assertDontSee('Draft Product')
            ->assertSee('Live Product');
    }

    public function test_category_filter_narrows_the_catalogue(): void
    {
        $wanted = Category::factory()->create(['name' => 'Footwear']);
        Product::factory()->for($wanted)->create(['name' => 'Running Shoe']);
        Product::factory()->create(['name' => 'Desk Lamp']);

        $this->get(route('catalog.index', ['category_id' => $wanted->id]))
            ->assertOk()
            ->assertSee('Running Shoe')
            ->assertDontSee('Desk Lamp');
    }

    public function test_search_matches_the_product_description(): void
    {
        Product::factory()->create(['name' => 'Alpha', 'description' => 'waterproof canvas']);
        Product::factory()->create(['name' => 'Beta', 'description' => 'suede finish']);

        $this->get(route('catalog.index', ['search' => 'waterproof']))
            ->assertOk()
            ->assertSee('Alpha')
            ->assertDontSee('Beta');
    }

    public function test_renders_an_enabled_product_detail_page(): void
    {
        $product = Product::factory()->enabled()->create([
            'name' => 'Cotton Tote',
            'description' => 'Roomy and washable.',
        ]);

        $this->get(route('catalog.show', $product))
            ->assertOk()
            ->assertSee('Cotton Tote')
            ->assertSee('Roomy and washable.');
    }

    public function test_returns_404_for_a_disabled_product_detail_page(): void
    {
        $product = Product::factory()->disabled()->create();

        $this->get(route('catalog.show', $product))->assertNotFound();
    }

    public function test_returns_404_for_a_soft_deleted_product_detail_page(): void
    {
        $product = Product::factory()->trashed()->create();

        $this->get(route('catalog.show', $product))->assertNotFound();
    }
}
