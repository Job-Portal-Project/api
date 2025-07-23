<?php

namespace Tests\Feature\Http\Controllers\API;

use App\Models\JWT\TokenBlacklist;
use Illuminate\Support\Facades\Config;
use Tests\Assets\Traits\AuthTestHelpers;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use AuthTestHelpers;

    /**
     * Test that user registration returns a 200 status.
     */
    public function test_user_registration_returns_status_201(): void
    {
        $response = $this->registerUser();
        $response->assertStatus(201);
    }

    /**
     * Test that user registration returns the correct JSON structure.
     */
    public function test_user_registration_returns_correct_json_structure(): void
    {
        $response = $this->registerUser();
        $response->assertJsonStructure($this->registeredUserResponseStructure);
    }

    /**
     * Test that the "me" endpoint returns a 200 status when accessed with a valid token.
     */
    public function test_me_endpoint_returns_status_200_with_valid_token(): void
    {
        $response = $this->me();
        $response->assertOk();
    }

    /**
     * Test that the "me" endpoint returns the correct user data structure.
     */
    public function test_me_endpoint_returns_correct_user_data_structure(): void
    {
        $this->me()->assertOk()->assertJsonStructure($this->userResponseStructure);
    }

    /**
     * Test that the "me" endpoint returns a 403 status when accessed with an invalid token type.
     */
    public function test_me_endpoint_returns_status_403_with_invalid_token_type(): void
    {
        $registered = $this->registerUser();
        $refreshToken = $registered->json('new_tokens.1.token');

        $this->me($refreshToken)->assertStatus(403)->assertJson([
            'message' => 'This action is unauthorized.',
        ]);
    }

    /**
     * Test that the "me" endpoint returns a 401 status when accessed with an invalid token.
     */
    public function test_me_endpoint_returns_status_401_with_invalid_token(): void
    {
        $this->me('invalid token')->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    /**
     * Test that the "me" endpoint returns a 403 status when the token cannot be used yet.
     */
    public function test_me_endpoint_returns_status_403_when_token_cannot_be_used_yet(): void
    {
        Config::set('jwt.access.cbu', 30);

        $me = $this->me();

        $me->assertStatus(403)
            ->assertJson([
                'message' => 'The token cannot be used yet',
            ]);
    }

    /**
     * Test that the "me" endpoint returns a 403 status when the token is expired.
     */
    public function test_me_endpoint_returns_status_403_when_token_is_expired(): void
    {
        Config::set('jwt.access.ttl', -10);

        $me = $this->me();

        $me->assertStatus(403)
            ->assertJson([
                'message' => 'The token is expired',
            ]);
    }

    public function test_authenticate_endpoint_returns_status_200_when_provided_valid_credentials(): void
    {
        $credentials = [
            'email' => $this->faker->safeEmail,
            'password' => 'password', // Use a fixed password to ensure consistency
        ];

        $auth = $this->authenticate($credentials);

        $auth->assertStatus(200)
            ->assertJsonStructure($this->authenticatedUserResponseStructure);
    }

    public function test_authenticate_endpoint_returns_401_when_provided_invalid_credentials(): void
    {
        $auth = $this->authenticate(authenticate: [
            'email' => $this->faker->safeEmail,
            'password' => $this->faker->password,
        ]);

        $auth->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_authenticate_endpoint_returns_422_when_provided_missing_credentials(): void
    {
        $auth = $this->authenticate(authenticate: [
            'email' => $this->faker->safeEmail,
        ]);

        $auth->assertStatus(422);
    }

    public function test_revoke_endpoint_returns_status_204(): void
    {
        $revoke = $this->revoke('new_tokens.0.token');

        $revoke->assertStatus(204);
    }

    public function test_revoke_endpoint_returns_401_when_provided_invalid_token(): void
    {
        $revoke = $this->revoke('invalid token');

        $revoke->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_revoke_endpoint_returns_403_with_invalid_token_type(): void
    {
        $revoke = $this->revoke('new_tokens.1.token');

        $revoke->assertStatus(403)
            ->assertJson([
                'message' => 'This action is unauthorized.',
            ]);
    }

    public function test_revoke_endpoint_returns_correct_errors_when_provided_revoked_tokens_by_different_situations(): void
    {
        $registered = $this->registerUser();

        $revokeRequest = fn (string $token) => $this->delete(route('auth.revoke'), [], [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ]);

        $revokedTokens = [
            'access_token' => $registered->json('new_tokens.0.token'),
            'refresh_token' => $registered->json('new_tokens.1.token'),
        ];

        $access = current($revokedTokens);
        $refresh = next($revokedTokens);

        $revokeRequest($refresh)->assertStatus(403)->assertJson([
            'message' => 'This action is unauthorized.',
        ]);

        $revokeRequest($access)->assertNoContent()->isEmpty();

        foreach ($revokedTokens as $revokedToken) {
            $revokeRequest($revokedToken)->assertStatus(403)->assertJson([
                'message' => ($revokedToken === $refresh) ? 'This action is unauthorized.' : 'The token is revoked',
            ]);
        }
    }

    public function test_revoke_endpoint_inserts_tokens_to_blacklist(): void
    {
        $registered = $this->registerUser();

        $revoke = $this->delete(route('auth.revoke'), [], [
            'Authorization' => 'Bearer '.$registered->json('new_tokens.0.token'),
            'Accept' => 'application/json',
        ]);

        $revoke->assertNoContent();

        collect($registered->json('new_tokens'))->each(function (array $token) {
            $this->assertDatabaseHas((new TokenBlacklist)->getTable(), [
                'jwt_token_id' => $token['claims']['jti'],
            ]);
        });
    }

    public function test_refresh_endpoint_returns_201_with_valid_token_type(): void
    {
        $this->refresh('new_tokens.1.token')->assertCreated()->assertJsonStructure([
            'new_tokens' => [
                $this->tokenResponseStructure,
                $this->tokenResponseStructure,
            ],
        ]);
    }

    public function test_refresh_endpoint_returns_403_with_invalid_token_type(): void
    {
        $this->refresh('new_tokens.0.token')->assertStatus(403)->assertJson([
            'message' => 'This action is unauthorized.',
        ]);
    }

    public function test_refresh_endpoint_returns_401_when_provided_invalid_token(): void
    {
        $this->refresh('invalid token')->assertUnauthorized()->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function test_refresh_endpoint_returns_valid_tokens_when_provided_valid_token(): void
    {
        $refreshRequest = fn (string $token) => $this->post(route('auth.refresh'), [], [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ]);

        $validJsonStructure = [
            'new_tokens' => [
                $this->tokenResponseStructure,
                $this->tokenResponseStructure,
            ],
        ];

        $registered = $this->registerUser();

        $refreshToken = $registered->json('new_tokens.1.token');
        $firstRefreshRequest = $refreshRequest($refreshToken);
        $firstRefreshRequest->assertCreated()->assertJsonStructure($validJsonStructure);

        $newRefreshToken = $firstRefreshRequest->json('new_tokens.1.token');
        $secondRefreshRequest = $refreshRequest($newRefreshToken);
        $secondRefreshRequest->assertCreated()->assertJsonStructure($validJsonStructure);
    }
}
