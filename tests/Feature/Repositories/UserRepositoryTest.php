<?php

namespace Tests\Feature\Repositories;

use App\Enums\Role;
use App\Models\JWT\Token;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_create_method_creating_users_correctly(): void
    {
        $repository = app()->make(UserRepository::class);

        $userData = [
            'email' => $this->faker->safeEmail,
            'password' => $this->faker->password,
            'role' => Role::CANDIDATE->value,
            'data' => [
                'name' => $this->faker->name,
            ]
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
