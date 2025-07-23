<?php

namespace App\Repositories;

use App\Contracts\JWT\TokenServiceInterface;
use App\Models\JWT\Token;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lcobucci\JWT\UnencryptedToken;

class TokenRepository extends AbstractRepository
{
    protected string $model = Token::class;

    public function __construct(protected TokenServiceInterface $service)
    {
        parent::__construct();
    }

    public function create(array $data): Authenticatable
    {
        $user = DB::transaction(function () use ($data) {
            $user = current($data);

            $tokenPayloads = $this->service->data(sub: $user->id);

            /** @var Collection<UnencryptedToken> $tokens */
            $tokens = $tokenPayloads->map(fn ($payload) => $this->service->build($payload));

            /** @var Collection<array> $tokenData */
            $tokenData = $tokens->map(function(UnencryptedToken $token) use ($user) {
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
}
