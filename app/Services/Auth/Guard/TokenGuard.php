<?php

namespace App\Services\Auth\Guard;

use App\Traits\TokenValidation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Throwable;

class TokenGuard
{
    use GuardHelpers;
    use TokenValidation;

    /**
     * Handle the incoming request and authenticate the user based on JWT token.
     *
     *
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function __invoke(Request $request, UserProvider $provider): ?Authenticatable
    {
        // Retrieve and validate the JWT from the request
        throw_if(! $token = $this->getTokenForRequest($request), AuthenticationException::class);

        // Retrieve the user based on the token's "sub" (subject) claim
        throw_if(! $tokenable = $provider->retrieveById($token->claims()->get('sub')), AuthenticationException::class);

        // Set the authenticated user
        return $this->user = $tokenable;
    }
}
