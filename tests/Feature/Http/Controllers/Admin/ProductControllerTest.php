<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $this->get(route('admin.products.index'))->assertRedirect(route('login'));
    }

    public function test_forbids_a_customer_from_the_product_list(): void
    {
        $this->actingAs(User::factory()->customer()->create())
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_lists_products_for_an_administrator(): void
    {
        $product = Product::factory()->create(['name' => 'Walnut Desk']);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Walnut Desk')
            ->assertSee($product->category->name);
    }

    public function test_lists_include_disabled_products(): void
    {
        Product::factory()->disabled()->create(['name' => 'Hidden Lamp']);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Hidden Lamp');
    }

    public function test_excludes_soft_deleted_products_from_the_list(): void
    {
        Product::factory()->trashed()->create(['name' => 'Retired Chair']);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertDontSee('Retired Chair');
    }

    public function test_status_filter_narrows_the_list_to_enabled_products(): void
    {
        Product::factory()->enabled()->create(['name' => 'Visible Mug']);
        Product::factory()->disabled()->create(['name' => 'Hidden Mug']);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index', ['status' => 'enabled']))
            ->assertOk()
            ->assertSee('Visible Mug')
            ->assertDontSee('Hidden Mug');
    }

    public function test_status_filter_narrows_the_list_to_disabled_products(): void
    {
        Product::factory()->enabled()->create(['name' => 'Visible Mug']);
        Product::factory()->disabled()->create(['name' => 'Hidden Mug']);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index', ['status' => 'disabled']))
            ->assertOk()
            ->assertSee('Hidden Mug')
            ->assertDontSee('Visible Mug');
    }

    public function test_rejects_an_unknown_status_filter(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.products.index', ['status' => 'archived']))
            ->assertSessionHasErrors('status');
    }

    public function test_paginates_the_list_at_the_requested_page_size(): void
    {
        Product::factory()->count(8)->create();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.products.index', ['per_page' => 5]));

        $response->assertOk();
        $this->assertCount(5, $response->viewData('products')->items());
        $this->assertSame(8, $response->viewData('products')->total());
    }

    public function test_rejects_a_page_size_above_the_maximum(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.products.index', ['per_page' => 101]))
            ->assertSessionHasErrors('per_page');
    }

    public function test_renders_the_create_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('New product');
    }

    public function test_valid_payload_creates_a_product_and_redirects_to_the_list(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), [
                'name' => 'Standing Desk',
                'category_id' => $category->id,
                'description' => 'Height adjustable.',
                'price' => '499.95',
                'stock' => 12,
                'enabled' => '1',
            ])
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('products', [
            'name' => 'Standing Desk',
            'category_id' => $category->id,
            'price' => '499.95',
            'stock' => 12,
            'enabled' => true,
        ]);
    }

    public function test_empty_payload_reports_every_required_field(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), [])
            ->assertSessionHasErrors(['name', 'category_id', 'price', 'stock', 'enabled']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_rejects_a_category_that_does_not_exist(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $this->validPayload(['category_id' => 9999]))
            ->assertSessionHasErrors(['category_id' => 'The selected category no longer exists.']);
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function invalidPrices(): array
    {
        return [
            'negative' => [-1],
            'three decimal places' => ['10.555'],
            'not a number' => ['free'],
        ];
    }

    #[DataProvider('invalidPrices')]
    public function test_rejects_an_invalid_price(mixed $price): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $this->validPayload(['price' => $price]))
            ->assertSessionHasErrors('price');

        $this->assertDatabaseCount('products', 0);
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function invalidStock(): array
    {
        return [
            'negative' => [-1],
            'fractional' => [1.5],
            'not a number' => ['plenty'],
        ];
    }

    #[DataProvider('invalidStock')]
    public function test_rejects_invalid_stock(mixed $stock): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $this->validPayload(['stock' => $stock]))
            ->assertSessionHasErrors('stock');
    }

    public function test_forbids_a_customer_from_creating_a_product(): void
    {
        $this->actingAs(User::factory()->customer()->create())
            ->post(route('admin.products.store'), $this->validPayload())
            ->assertForbidden();

        $this->assertDatabaseCount('products', 0);
    }

    public function test_renders_the_edit_form_with_the_current_values(): void
    {
        $product = Product::factory()->create(['name' => 'Oak Shelf']);

        $this->actingAs($this->admin())
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('Oak Shelf');
    }

    public function test_valid_payload_updates_the_product(): void
    {
        $product = Product::factory()->create(['name' => 'Old Name', 'stock' => 1]);

        $this->actingAs($this->admin())
            ->put(route('admin.products.update', $product), [
                'name' => 'New Name',
                'category_id' => $product->category_id,
                'description' => 'Updated copy.',
                'price' => '25.00',
                'stock' => 40,
                'enabled' => '0',
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Name',
            'stock' => 40,
            'enabled' => false,
        ]);
    }

    public function test_forbids_a_customer_from_updating_a_product(): void
    {
        $product = Product::factory()->create(['name' => 'Untouched']);

        $this->actingAs(User::factory()->customer()->create())
            ->put(route('admin.products.update', $product), $this->validPayload([
                'category_id' => $product->category_id,
            ]))
            ->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Untouched']);
    }

    public function test_deleting_a_product_soft_deletes_it(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('status');

        $this->assertSoftDeleted($product);
    }

    public function test_forbids_a_customer_from_deleting_a_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->customer()->create())
            ->delete(route('admin.products.destroy', $product))
            ->assertForbidden();

        $this->assertNotSoftDeleted($product);
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Example Product',
            'category_id' => Category::factory()->create()->id,
            'description' => 'An example.',
            'price' => '19.99',
            'stock' => 5,
            'enabled' => '1',
        ], $overrides);
    }
}
