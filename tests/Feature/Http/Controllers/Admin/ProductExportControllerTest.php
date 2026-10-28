<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Exports\ProductsExport;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ProductExportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $this->get(route('admin.products.export'))->assertRedirect(route('login'));
    }

    public function test_forbids_a_customer_from_exporting(): void
    {
        $this->actingAs(User::factory()->customer()->create())
            ->get(route('admin.products.export'))
            ->assertForbidden();
    }

    public function test_downloads_a_spreadsheet_named_for_the_current_date(): void
    {
        Excel::fake();

        Product::factory()->count(2)->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.products.export'))
            ->assertOk();

        Excel::assertDownloaded('products-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function test_export_carries_the_filters_from_the_query_string(): void
    {
        Excel::fake();

        $product = Product::factory()->enabled()->create();
        Product::factory()->disabled()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.products.export', ['status' => 'enabled']))
            ->assertOk();

        Excel::assertDownloaded(
            'products-'.now()->format('Y-m-d-His').'.xlsx',
            function (ProductsExport $export) use ($product): bool {
                $exported = $export->query()->pluck('id');

                return $exported->count() === 1 && $exported->first() === $product->id;
            },
        );
    }

    public function test_rejects_an_unknown_status_filter(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.products.export', ['status' => 'archived']))
            ->assertSessionHasErrors('status');
    }
}
