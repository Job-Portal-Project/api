<?php

namespace Tests\Feature\Traits;

use Illuminate\Support\Facades\Config;
use Mockery;
use Tests\Assets\Traits\AuthTestHelpers;
use Tests\TestCase;

class TokenValidationTest extends TestCase
{
    use AuthTestHelpers;

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function test_validate_method_revokes_the_token_when_provided_invalid_token(): void
    {
        Config::set('jwt.access.ttl', -10);

        $registered = $this->registerUser();
        $token = $registered->json('new_tokens.0');

        $me = $this->me($token['token']);

        $me->assertStatus(403)
            ->assertJson([
                'message' => 'The token is expired',
            ]);

        $this->assertDatabaseHas('jwt_token_blacklist', [
            'jwt_token_id' => $token['claims']['jti'],
        ]);
    }

    public function test_validate_method_returns_correct_error_when_provided_revoked_token(): void
    {
        Config::set('jwt.access.ttl', -10);

        $registered = $this->registerUser();
        $token = $registered->json('new_tokens.0');

        $this->me($token['token'])->assertStatus(403)->assertJson([
            'message' => 'The token is expired',
        ]);

        $this->assertDatabaseHas('jwt_token_blacklist', [
            'jwt_token_id' => $token['claims']['jti'],
        ]);

        $this->me($token['token'])->assertStatus(403)->assertJson([
            'message' => 'The token is revoked',
        ]);
    }
}
