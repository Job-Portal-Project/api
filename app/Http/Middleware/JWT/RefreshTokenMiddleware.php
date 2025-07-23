<?php

namespace App\Http\Middleware\JWT;

use App\Enums\JWT\TokenType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RefreshTokenMiddleware extends TokenAbstractMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure(Request): (Response)  $next
     * @return Response
     * @throws Throwable
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->start($request, TokenType::REFRESH);

        return $next($request);
    }
}
