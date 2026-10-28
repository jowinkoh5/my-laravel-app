<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function guardedEndpoints(): array
    {
        return [
            'index' => ['getJson', 'api.v1.products.index'],
            'store' => ['postJson', 'api.v1.products.store'],
        ];
    }

    #[DataProvider('guardedEndpoints')]
    public function test_returns_401_when_no_token_is_provided(string $method, string $route): void
    {
        $this->{$method}(route($route))->assertUnauthorized();
    }

    #[DataProvider('guardedEndpoints')]
    public function test_returns_403_for_a_customer_token(string $method, string $route): void
    {
        Sanctum::actingAs(User::factory()->customer()->create());

        $this->{$method}(route($route))->assertForbidden();
    }

    public function test_returns_the_product_list_with_the_category_id_and_name(): void
    {
        $this->actingAsAdmin();

        $category = Category::factory()->create(['name' => 'Peripherals']);
        $product = Product::factory()->for($category)->create([
            'name' => 'Trackball',
            'price' => '89.50',
            'stock' => 7,
        ]);

        $this->getJson(route('api.v1.products.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $product->id)
            ->assertJsonPath('data.0.name', 'Trackball')
            ->assertJsonPath('data.0.price', '89.50')
            ->assertJsonPath('data.0.stock', 7)
            ->assertJsonPath('data.0.enabled', true)
            ->assertJsonPath('data.0.category.id', $category->id)
            ->assertJsonPath('data.0.category.name', 'Peripherals');
    }

    public function test_list_excludes_soft_deleted_products(): void
    {
        $this->actingAsAdmin();

        Product::factory()->create();
        Product::factory()->trashed()->create();

        $this->getJson(route('api.v1.products.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_list_filters_by_category(): void
    {
        $this->actingAsAdmin();

        $wanted = Category::factory()->create();
        $expected = Product::factory()->for($wanted)->create();
        Product::factory()->count(2)->create();

        $this->getJson(route('api.v1.products.index', ['category_id' => $wanted->id]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_list_filters_by_status(): void
    {
        $this->actingAsAdmin();

        $enabled = Product::factory()->enabled()->create();
        Product::factory()->disabled()->create();

        $this->getJson(route('api.v1.products.index', ['status' => 'enabled']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $enabled->id);
    }

    public function test_list_returns_422_for_an_unknown_category(): void
    {
        $this->actingAsAdmin();

        $this->getJson(route('api.v1.products.index', ['category_id' => 9999]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category_id');
    }

    public function test_list_is_paginated_with_links_and_meta(): void
    {
        $this->actingAsAdmin();

        Product::factory()->count(7)->create();

        $this->getJson(route('api.v1.products.index', ['per_page' => 3]))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 7)
            ->assertJsonPath('meta.per_page', 3)
            ->assertJsonPath('meta.last_page', 3)
            ->assertJsonStructure(['data', 'links' => ['first', 'last', 'prev', 'next'], 'meta']);
    }

    public function test_list_returns_422_for_a_page_size_above_the_maximum(): void
    {
        $this->actingAsAdmin();

        $this->getJson(route('api.v1.products.index', ['per_page' => 101]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('per_page');
    }

    public function test_returns_one_product_with_its_category(): void
    {
        $this->actingAsAdmin();

        $product = Product::factory()->create(['name' => 'Desk Mat']);

        $this->getJson(route('api.v1.products.show', $product))
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.name', 'Desk Mat')
            ->assertJsonPath('data.category.id', $product->category_id);
    }

    public function test_returns_404_for_a_soft_deleted_product(): void
    {
        $this->actingAsAdmin();

        $product = Product::factory()->trashed()->create();

        $this->getJson(route('api.v1.products.show', $product))->assertNotFound();
    }

    public function test_returns_404_for_an_unknown_product(): void
    {
        $this->actingAsAdmin();

        $this->getJson(route('api.v1.products.show', 9999))->assertNotFound();
    }

    public function test_valid_payload_creates_a_product_and_returns_201(): void
    {
        $this->actingAsAdmin();

        $category = Category::factory()->create(['name' => 'Audio']);

        $this->postJson(route('api.v1.products.store'), [
            'name' => 'Studio Monitor',
            'category_id' => $category->id,
            'description' => 'Near-field reference speaker.',
            'price' => 349.99,
            'stock' => 4,
            'enabled' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Studio Monitor')
            ->assertJsonPath('data.price', '349.99')
            ->assertJsonPath('data.category.name', 'Audio');

        $this->assertDatabaseHas('products', [
            'name' => 'Studio Monitor',
            'category_id' => $category->id,
            'stock' => 4,
        ]);
    }

    public function test_returns_422_when_the_payload_is_empty(): void
    {
        $this->actingAsAdmin();

        $this->postJson(route('api.v1.products.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'category_id', 'price', 'stock', 'enabled']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_returns_422_for_a_price_with_too_many_decimal_places(): void
    {
        $this->actingAsAdmin();

        $this->postJson(route('api.v1.products.store'), [
            'name' => 'Odd Price',
            'category_id' => Category::factory()->create()->id,
            'price' => '10.555',
            'stock' => 1,
            'enabled' => true,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'price' => 'The price may have at most two decimal places.',
            ]);
    }

    public function test_deleting_a_product_soft_deletes_it(): void
    {
        $this->actingAsAdmin();

        $product = Product::factory()->create();

        $this->deleteJson(route('api.v1.products.destroy', $product))
            ->assertOk()
            ->assertJsonPath('message', 'Product deleted.')
            ->assertJsonPath('data.id', $product->id);

        $this->assertSoftDeleted($product);
    }

    public function test_returns_403_when_a_customer_deletes_a_product(): void
    {
        Sanctum::actingAs(User::factory()->customer()->create());

        $product = Product::factory()->create();

        $this->deleteJson(route('api.v1.products.destroy', $product))->assertForbidden();

        $this->assertNotSoftDeleted($product);
    }

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        return $admin;
    }
}
