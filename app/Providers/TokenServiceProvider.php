<?php

namespace App\Providers;

use App\Contracts\JWT\TokenServiceInterface;
use App\Enums\Role;
use App\Http\Requests\User\StoreRequest;
use App\Services\Auth\Guard\TokenGuard;
use App\Services\TokenService;
use Illuminate\Auth\RequestGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class TokenServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind the TokenServiceInterface to the TokenService implementation
        $this->app->bind(TokenServiceInterface::class, TokenService::class);
        $this->app->bind(StoreRequest::class, function () {
            $role = request()->input('role', Role::CANDIDATE->value);

            return app(StoreRequest::ROLE_REQUEST_MAPPING[$role]);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Extend Laravel's Auth system with the custom JWT-based guard
        Auth::extend('jwt', function ($app, $name, array $config) {
            return new RequestGuard(
                new TokenGuard(app()->get(TokenServiceInterface::class)),
                request(),
                Auth::createUserProvider($config['provider'])
            );
        });
    }
}
