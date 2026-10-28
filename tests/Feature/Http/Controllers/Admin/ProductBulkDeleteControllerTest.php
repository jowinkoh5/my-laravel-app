<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Http\Requests\BulkDeleteProductRequest;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductBulkDeleteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $product = Product::factory()->create();

        $this->delete(route('admin.products.bulk-destroy'), ['ids' => [$product->id]])
            ->assertRedirect(route('login'));

        $this->assertNotSoftDeleted($product);
    }

    public function test_forbids_a_customer_from_deleting_in_bulk(): void
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->customer()->create())
            ->delete(route('admin.products.bulk-destroy'), ['ids' => [$product->id]])
            ->assertForbidden();

        $this->assertNotSoftDeleted($product);
    }

    public function test_soft_deletes_every_selected_product_and_leaves_the_rest(): void
    {
        $selected = Product::factory()->count(3)->create();
        $untouched = Product::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.products.bulk-destroy'), [
                'ids' => $selected->pluck('id')->all(),
            ])
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('status', '3 products were deleted.');

        foreach ($selected as $product) {
            $this->assertSoftDeleted($product);
        }

        $this->assertNotSoftDeleted($untouched);
    }

    public function test_reports_a_single_deletion_in_the_singular(): void
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.products.bulk-destroy'), ['ids' => [$product->id]])
            ->assertSessionHas('status', '1 product was deleted.');
    }

    public function test_rejects_an_empty_selection(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.products.bulk-destroy'), ['ids' => []])
            ->assertSessionHasErrors(['ids' => 'Select at least one product to delete.']);
    }

    public function test_rejects_a_missing_selection(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.products.bulk-destroy'), [])
            ->assertSessionHasErrors('ids');
    }

    public function test_rejects_an_id_that_does_not_exist(): void
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.products.bulk-destroy'), ['ids' => [$product->id, 9999]])
            ->assertSessionHasErrors('ids.1');

        $this->assertNotSoftDeleted($product);
    }

    public function test_rejects_an_already_soft_deleted_id(): void
    {
        $trashed = Product::factory()->trashed()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.products.bulk-destroy'), ['ids' => [$trashed->id]])
            ->assertSessionHasErrors('ids.0');
    }

    public function test_rejects_more_ids_than_the_configured_maximum(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.products.bulk-destroy'), [
                'ids' => range(1, BulkDeleteProductRequest::MAX_IDS + 1),
            ])
            ->assertSessionHasErrors('ids');
    }
}
