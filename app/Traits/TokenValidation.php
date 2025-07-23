<?php

namespace App\Traits;

use App\Models\JWT\Token;
use App\Contracts\JWT\TokenServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Exception;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha512;
use Lcobucci\JWT\UnencryptedToken;
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
     *
     * @param  Request  $request
     * @return UnencryptedToken|null
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
                $token = $this->config->parser()->parse($token);
            } catch (Exception) {
                $token = null;
            }
        }

        return $token;
    }

    /**
     * Verify the validity of a JWT token.
     *
     * @throws AuthorizationException
     */
    private function validate(UnencryptedToken $token, Token $record): void
    {
        // Token validation logic here, including checking signature, expiration, and revocation
    }
}
