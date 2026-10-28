<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_credentials_return_201_with_a_bearer_token(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'password']);

        $response = $this->postJson(route('api.v1.tokens.store'), [
            'email' => $admin->email,
            'password' => 'password',
            'device_name' => 'phpunit',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.id', $admin->id)
            ->assertJsonPath('data.user.role', 'admin')
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'name', 'email', 'role']]]);

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $admin->id,
            'name' => 'phpunit',
        ]);
    }

    public function test_returns_422_for_a_wrong_password(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'password']);

        $this->postJson(route('api.v1.tokens.store'), [
            'email' => $admin->email,
            'password' => 'not-the-password',
            'device_name' => 'phpunit',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email' => trans('auth.failed')]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_returns_422_for_an_unknown_email_without_revealing_it_is_unknown(): void
    {
        $this->postJson(route('api.v1.tokens.store'), [
            'email' => 'nobody@example.com',
            'password' => 'password',
            'device_name' => 'phpunit',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email' => trans('auth.failed')]);
    }

    public function test_returns_422_when_fields_are_missing(): void
    {
        $this->postJson(route('api.v1.tokens.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password', 'device_name']);
    }

    public function test_sixth_failed_attempt_is_throttled(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'password']);

        foreach (range(1, 5) as $ignored) {
            $this->postJson(route('api.v1.tokens.store'), [
                'email' => $admin->email,
                'password' => 'not-the-password',
                'device_name' => 'phpunit',
            ]);
        }

        $response = $this->postJson(route('api.v1.tokens.store'), [
            'email' => $admin->email,
            'password' => 'password',
            'device_name' => 'phpunit',
        ]);

        $response->assertUnprocessable();
        $this->assertStringContainsString(
            'Too many login attempts',
            $response->json('errors.email.0'),
        );
    }

    public function test_issuing_a_token_for_the_same_device_replaces_the_previous_one(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'password']);

        foreach (range(1, 2) as $ignored) {
            $this->postJson(route('api.v1.tokens.store'), [
                'email' => $admin->email,
                'password' => 'password',
                'device_name' => 'phpunit',
            ])->assertCreated();
        }

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_a_customer_may_obtain_a_token_but_it_cannot_reach_the_product_endpoints(): void
    {
        $customer = User::factory()->customer()->create(['password' => 'password']);

        $token = $this->postJson(route('api.v1.tokens.store'), [
            'email' => $customer->email,
            'password' => 'password',
            'device_name' => 'phpunit',
        ])->assertCreated()->json('data.token');

        $this->withToken($token)
            ->getJson(route('api.v1.products.index'))
            ->assertForbidden();
    }

    public function test_revoking_the_current_token_deletes_it(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('phpunit')->plainTextToken;

        $this->withToken($token)
            ->deleteJson(route('api.v1.tokens.destroy'))
            ->assertOk()
            ->assertJsonPath('message', 'Token revoked.');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    /**
     * The token is revoked directly rather than through the revoke endpoint so
     * that this test issues a single HTTP request: the authentication guard
     * memoizes the resolved user, so a second request in the same test would
     * still see the user from the first one.
     */
    public function test_returns_401_for_a_revoked_token(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('phpunit')->plainTextToken;
        $admin->tokens()->delete();

        $this->withToken($token)
            ->getJson(route('api.v1.products.index'))
            ->assertUnauthorized();
    }

    public function test_returns_401_for_a_malformed_token(): void
    {
        $this->withToken('1|not-a-real-token')
            ->getJson(route('api.v1.products.index'))
            ->assertUnauthorized();
    }

    public function test_returns_401_when_revoking_without_a_token(): void
    {
        $this->deleteJson(route('api.v1.tokens.destroy'))->assertUnauthorized();
    }
}
