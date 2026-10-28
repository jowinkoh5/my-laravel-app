<?php

namespace Tests\Feature\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_the_login_screen_for_a_guest(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Log in');
    }

    public function test_valid_credentials_authenticate_and_redirect_to_the_dashboard(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'password']);

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.products.index'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_wrong_password_keeps_the_visitor_a_guest_and_reports_the_failure(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'password']);

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'not-the-password',
        ])->assertSessionHasErrors(['email' => trans('auth.failed')]);

        $this->assertGuest();
    }

    public function test_empty_payload_reports_both_required_fields(): void
    {
        $this->post(route('login'), [])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }

    public function test_sixth_failed_attempt_is_throttled(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'password']);

        foreach (range(1, 5) as $ignored) {
            $this->post(route('login'), [
                'email' => $admin->email,
                'password' => 'not-the-password',
            ]);
        }

        $response = $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'not-the-password',
        ]);

        $response->assertSessionHasErrorsIn('default', ['email']);
        $this->assertStringContainsString(
            'Too many login attempts',
            session('errors')->first('email'),
        );

        // Correct credentials are refused too while the throttle is active.
        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertGuest();

        RateLimiter::clear('login');
    }

    public function test_a_customer_can_authenticate_but_cannot_reach_the_dashboard(): void
    {
        $customer = User::factory()->customer()->create(['password' => 'password']);

        $this->post(route('login'), [
            'email' => $customer->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($customer);

        $this->get(route('admin.products.index'))->assertForbidden();
    }

    public function test_logging_out_returns_the_visitor_to_the_catalogue_as_a_guest(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('logout'))->assertRedirect(route('catalog.index'));

        $this->assertGuest();
    }
}
