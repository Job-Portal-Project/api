<?php

namespace App\Repositories;

use App\Contracts\JWT\TokenServiceInterface;
use App\Enums\Role;
use App\Models\Admin;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\JWT\Token;
use App\Models\Moderator;
use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lcobucci\JWT\UnencryptedToken;

class UserRepository extends AbstractRepository
{
    protected string $model = User::class;

    public function __construct(
        protected TokenServiceInterface $service
    ) {
        parent::__construct();
    }

    public function create(array $data): Authenticatable
    {
        $profileData = Arr::pull($data, 'data');
        $userData = $data;
        $user = DB::transaction(function () use ($profileData, $userData) {
            /** @var User $user */
            $role = \Spatie\Permission\Models\Role::query()->firstWhere(
                'name',
                Role::from(Arr::pull($userData, 'role'))->value
            );
            $user = parent::create($userData);
            $user->assignRole($role);
            $this->createProfile($role, array_merge($profileData, [
                'user_id' => $user->getKey()
            ]));

            $tokenPayloads = $this->service->data(sub: ((string) $user->getAuthIdentifier()));

            /** @var Collection<UnencryptedToken> $tokens */
            $tokens = $tokenPayloads->map(fn ($payload) => $this->service->build($payload));

            /** @var Collection<array> $tokenData */
            $tokenData = $tokens->map(function (UnencryptedToken $token) use ($user) {
                return [
                    'id' => $token->claims()->get('jti'),
                    'token' => $token,
                    'tokenable_id' => $user->id,
                    'tokenable_type' => get_class($user),
                ];
            });

            $user->tokens()->createMany($tokenData);

            $user->setAttribute(
                'new_tokens',
                $tokenData->map(fn ($token) => new Token($token))
            );

            return $user;
        });

        return $user;
    }

    public function createProfile(\Spatie\Permission\Models\Role $role, array $data)
    {
        $model = match ($role->getAttribute('name')) {
            Role::CANDIDATE->value => Candidate::class,
            Role::ADMIN->value => Admin::class,
            Role::MODERATOR->value => Moderator::class,
            Role::COMPANY->value => Company::class,
        };

        return $model::query()->create($data);
    }
}
