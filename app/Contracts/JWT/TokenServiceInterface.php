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
     */
    public function revoke(Token|Collection $token): void;

    /**
     * Get the JWT configuration, including signer and key paths.
     */
    public function config(): Configuration;

    /**
     * Generate JWT claims based on the subject.
     */
    public function data(string $sub): Collection;

    /**
     * Build and return the signed JWT.
     */
    public function build(Collection $claims): UnencryptedToken;
}
