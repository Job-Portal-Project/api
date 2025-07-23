<?php

namespace App\Http\Middleware\JWT;

use App\Enums\JWT\TokenType;
use App\Models\JWT\Token;
use App\Traits\TokenValidation;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

abstract class TokenAbstractMiddleware
{
    use TokenValidation;

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     *
     * @throws Throwable
     */
    abstract public function handle(Request $request, Closure $next): Response;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Throwable
     */
    protected function start(Request $request, TokenType $type): void
    {
        $token = $this->getTokenForRequest($request);

        throw_if(
            ! $record = Token::query()->where('id', $token?->claims()->get('jti'))->first(),
            AuthenticationException::class
        );

        throw_if(
            ! $record->tokenIs($type),
            AuthorizationException::class
        );

        $this->confirm($token, $record);
    }
}
