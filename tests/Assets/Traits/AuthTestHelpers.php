<?php

namespace Tests\Assets\Traits;

use App\Contracts\JWT\TokenServiceInterface;
use App\Enums\Role;
use App\Models\Candidate;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;

trait AuthTestHelpers
{
    use DatabaseTransactions, WithFaker;

    protected array $userResponseStructure;

    protected array $tokenResponseStructure;

    protected array $registeredUserResponseStructure;

    protected array $authenticatedUserResponseStructure;

    public function setUp(): void
    {
        parent::setUp();

        $this->tokenResponseStructure = [
            'headers' => [
                'typ',
                'alg',
            ],
            'claims' => [
                'jti',
                'grp',
                'typ',
                'iat',
                'exp',
                'nbf',
            ],
            'token',
        ];

        $this->userResponseStructure = [
            'id',
            'name',
            'email',
            'updated_at',
            'created_at',
        ];

        $this->registeredUserResponseStructure = array_merge($this->userResponseStructure, [
            'new_tokens' => [
                $this->tokenResponseStructure,
                $this->tokenResponseStructure,
            ],
        ]);

        $this->authenticatedUserResponseStructure = $this->registeredUserResponseStructure;
    }

    /**
     * Create a new user and return the response.
     */
    protected function registerUser(array $overrides = []): TestResponse
    {
        $user = array_merge([
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password',
            'passwordConfirm' => 'password',
            'role' => 'candidate',
            'data' => [
                'name' => 'John Doe',
            ]
        ], $overrides);

        if (isset($overrides['password'])) {
            $user['passwordConfirm'] = $overrides['password'];
        }

        return $this->post(route('auth.register'), $user, [
            'Accept' => 'application/json',
        ]);
    }

    /**
     * Get the authenticated user's information.
     */
    protected function me(?string $token = null, array $overrides = []): TestResponse
    {
        $user = $this->registerUser($overrides);

        $token = $token ?? $user->json('new_tokens.0.token');

        return $this->get(route('auth.me'), [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ]);
    }

    protected function authenticate(array $register = [], array $authenticate = []): TestResponse
    {
        $this->registerUser($register);

        return $this->post(route('auth.authenticate'), array_merge($register, $authenticate), [
            'Accept' => 'application/json',
        ]);
    }

    protected function revoke(?string $key = null): TestResponse
    {
        $registered = $this->registerUser();

        $token = $registered->json($key);

        return $this->delete(route('auth.revoke'), [], [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ]);
    }

    protected function refresh(?string $key = null): TestResponse
    {
        $registered = $this->registerUser();

        $token = $registered->json($key);

        return $this->post(route('auth.refresh'), [], [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ]);
    }

    private function authenticated(): static
    {
        $user = (new UserRepository(app()->make(TokenServiceInterface::class)))->create(array_merge(
            User::factory()->definition(),
            ['role' => Role::CANDIDATE->value],
            ['data' => Candidate::factory()->definition()]
        ));

        return $this->actingAs($user, 'api')
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$user->getAttribute('new_tokens')->get(0)->token->toString(),
            ]);
    }
}
