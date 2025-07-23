<?php

namespace App\Traits;

use App\Contracts\JWT\TokenServiceInterface;
use App\Models\JWT\Token;
use Carbon\FactoryImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Exception;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha512;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\RelatedTo;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\ConstraintViolation;
use Throwable;

trait TokenValidation
{
    protected TokenServiceInterface $service;

    protected Configuration $config;

    protected string $inputKey;

    public function __construct(
        ?TokenServiceInterface $service = null,
        string $inputKey = 'token',
    ) {
        $this->service = $service ?? app()->get(TokenServiceInterface::class);
        $this->config = $this->service->config();
        $this->inputKey = $inputKey;
    }

    /**
     * Get the token for the current request.
     */
    private function getTokenForRequest(Request $request): ?UnencryptedToken
    {
        $token = $request->query($this->inputKey);

        if (empty($token)) {
            $token = $request->input($this->inputKey);
        }

        if (empty($token)) {
            $token = $request->cookie($this->inputKey);
        }

        if (empty($token)) {
            $token = $request->bearerToken() ?: null;
        }

        if ($token) {
            try {
                $parsedToken = $this->config->parser()->parse($token);

                return $parsedToken instanceof UnencryptedToken ? $parsedToken : null;
            } catch (Exception) {
                return null;
            }
        }

        return null;
    }

    private function isRevoked(Token $token): bool
    {
        return $token->isRevoked()->exists();
    }

    /**
     * @throws AuthorizationException
     */
    private function verify(UnencryptedToken $token, Token $record): void
    {
        $constraints = [
            new IdentifiedBy($record->id),
            new RelatedTo((string) $record->tokenable_id),
            new LooseValidAt(new FactoryImmutable),
            new SignedWith(new Sha512, InMemory::file(config('jwt.public_key_path'))),
        ];

        try {
            foreach ($constraints as $constraint) {
                $constraint->assert($token);
            }
        } catch (ConstraintViolation $e) {
            throw new AuthorizationException($e->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     * @throws Throwable
     */
    private function confirm(UnencryptedToken $token, Token $record): void
    {
        throw_if($this->isRevoked($record), AuthorizationException::class, 'The token is revoked');

        try {
            $this->verify($token, $record);
        } catch (AuthorizationException $e) {
            if ($token->isExpired(now())) {
                $this->service->revoke($record);
            }

            throw $e;
        }
    }
}
