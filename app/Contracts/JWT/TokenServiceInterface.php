<?php

namespace App\Contracts\JWT;

use App\Models\JWT\Token;
use Illuminate\Support\Collection;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\UnencryptedToken;

interface TokenServiceInterface
{
    /**
     * Revoke one or more tokens.
     *
     * @param  Token|Collection  $token
     * @return void
     */
    public function revoke(Token|Collection $token): void;

    /**
     * Get the JWT configuration, including signer and key paths.
     *
     * @return Configuration
     */
    public function config(): Configuration;

    /**
     * Generate JWT claims based on the subject.
     *
     * @param  string  $sub
     * @return Collection
     */
    public function data(string $sub): Collection;

    /**
     * Build and return the signed JWT.
     *
     * @param  Collection  $claims
     * @return UnencryptedToken
     */
    public function build(Collection $claims): UnencryptedToken;
}
