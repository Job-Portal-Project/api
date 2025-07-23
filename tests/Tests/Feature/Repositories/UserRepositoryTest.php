<?php

namespace Tests\Feature\Repositories;

use App\Models\JWT\Token;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_create_method_creating_users_correctly(): void
    {
        $repository = app()->make(UserRepository::class);

        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => $this->faker->password,
        ];

        $user = $repository->create($userData);

        $userID = $user->id;

        $this->assertDatabaseHas((new User)->getTable(), [
            'id' => $userID,
        ]);

        /** @var Collection $tokens */
        $tokens = $user->getAttribute('new_tokens');

        $tokens->map(function (Token $record) use ($userID) {
            $this->assertEquals($userID, $record->token->claims()->get('sub'));
        });
    }
}
