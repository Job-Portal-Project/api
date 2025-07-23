<?php

namespace App\Services;

use App\Contracts\JWT\TokenServiceInterface;
use App\Enums\JWT\TokenType;
use App\Models\JWT\Token;
use App\Models\JWT\TokenBlacklist;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha512;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\UnencryptedToken;

class TokenService implements TokenServiceInterface
{
    /**
     * Get JWT configuration.
     */
    public function config(): Configuration
    {
        $privateKey = config('jwt.private_key_path');
        $publicKey = config('jwt.public_key_path');

        return Configuration::forAsymmetricSigner(
            new Sha512,
            InMemory::file($privateKey),
            InMemory::file($publicKey),
        );
    }

    /**
     * Build and sign a new JWT token.
     *
     * @throws Exception
     */
    public function build(Collection $claims): UnencryptedToken
    {
        $config = $this->config();

        $builder = $config->builder();

        // Map claims to their corresponding builder methods
        $methods = [
            RegisteredClaims::ID => 'identifiedBy',
            RegisteredClaims::SUBJECT => 'relatedTo',
            RegisteredClaims::ISSUER => 'issuedBy',
            RegisteredClaims::ISSUED_AT => 'issuedAt',
            RegisteredClaims::EXPIRATION_TIME => 'expiresAt',
            RegisteredClaims::NOT_BEFORE => 'canOnlyBeUsedAfter',
        ];

        $claims->each(function ($value, string $name) use (&$builder, $methods) {
            $builder = array_key_exists($name, $methods)
                ? $builder->{$methods[$name]}($value)
                : $builder->withClaim($name, $value);
        });

        return $builder->getToken(
            $config->signer(),
            $config->signingKey()
        );
    }

    /**
     * Generate claims for access and refresh tokens.
     */
    public function data(string $sub): Collection
    {
        $time = now();
        $iss = config('app.url');
        $grp = Str::uuid()->toString();

        // Generate claims for both access and refresh tokens
        $payloads = collect(TokenType::cases())->map(function (TokenType $type) use ($sub, $time, $iss, $grp) {
            $jti = Str::uuid()->toString();

            return collect([
                RegisteredClaims::ID => $jti,
                'grp' => $grp,
                'typ' => $type->value,
                RegisteredClaims::SUBJECT => $sub,
                RegisteredClaims::ISSUER => $iss,
                RegisteredClaims::ISSUED_AT => $time->toDateTimeImmutable(),
                RegisteredClaims::EXPIRATION_TIME => $time->copy()->addMinutes(config("jwt.{$type->value}.ttl"))->toDateTimeImmutable(),
                RegisteredClaims::NOT_BEFORE => $time->copy()->addMinutes(config("jwt.{$type->value}.cbu"))->toDateTimeImmutable(),
            ]);
        });

        return $payloads;
    }

    /**
     * Revoke a token or a collection of tokens.
     */
    public function revoke(Token|Collection $token): void
    {
        $collection = ($token instanceof Token) ? collect([$token]) : $token;

        // Insert tokens into the blacklist
        $collection = $collection->map(fn (Token $token) => ['jwt_token_id' => $token->id]);

        TokenBlacklist::insert($collection->toArray());
    }
}
