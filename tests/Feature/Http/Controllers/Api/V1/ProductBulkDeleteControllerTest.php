<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductBulkDeleteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_no_token_is_provided(): void
    {
        $product = Product::factory()->create();

        $this->deleteJson(route('api.v1.products.bulk-destroy'), ['ids' => [$product->id]])
            ->assertUnauthorized();

        $this->assertNotSoftDeleted($product);
    }

    public function test_returns_403_for_a_customer_token(): void
    {
        Sanctum::actingAs(User::factory()->customer()->create());

        $product = Product::factory()->create();

        $this->deleteJson(route('api.v1.products.bulk-destroy'), ['ids' => [$product->id]])
            ->assertForbidden();

        $this->assertNotSoftDeleted($product);
    }

    public function test_soft_deletes_the_selected_products_and_reports_the_count(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $selected = Product::factory()->count(3)->create();
        $untouched = Product::factory()->create();
        $ids = $selected->pluck('id')->all();

        $this->deleteJson(route('api.v1.products.bulk-destroy'), ['ids' => $ids])
            ->assertOk()
            ->assertJsonPath('message', 'Products deleted.')
            ->assertJsonPath('data.deleted_count', 3)
            ->assertJsonPath('data.ids', $ids);

        foreach ($selected as $product) {
            $this->assertSoftDeleted($product);
        }

        $this->assertNotSoftDeleted($untouched);
    }

    public function test_returns_422_for_an_empty_id_list(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->deleteJson(route('api.v1.products.bulk-destroy'), ['ids' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ids' => 'Select at least one product to delete.']);
    }

    public function test_returns_422_when_an_id_does_not_exist(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $product = Product::factory()->create();

        $this->deleteJson(route('api.v1.products.bulk-destroy'), ['ids' => [$product->id, 9999]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('ids.1');

        $this->assertNotSoftDeleted($product);
    }

    public function test_returns_422_when_an_id_is_already_soft_deleted(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $trashed = Product::factory()->trashed()->create();

        $this->deleteJson(route('api.v1.products.bulk-destroy'), ['ids' => [$trashed->id]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('ids.0');
    }
}
