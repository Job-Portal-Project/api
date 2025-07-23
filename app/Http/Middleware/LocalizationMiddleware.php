<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\Translation\Exception\InvalidArgumentException;

class LocalizationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $availableLocales = config('translatable.locales') ?? [];

        $defaultLocale = config('app.locale');
        $locale = $request->header('Accept-Language', $defaultLocale);

        if (! in_array($locale, $availableLocales))
        {
            $locale = $defaultLocale;
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
