<?php

namespace Tests\Unit\Unit\Services;

use App\Services\TokenService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Lcobucci\JWT\UnencryptedToken;
use Tests\TestCase;

class TokenServiceTest extends TestCase
{
    public function test_build_method_can_builds_tokens(): void
    {
        $service = new TokenService;

        $payloads = $service->data(Str::uuid()->toString());

        $tokens = $payloads->map(function (Collection $payload) use ($service) {
            return $service->build($payload);
        });

        $this->assertInstanceOf(Collection::class, $tokens);

        $tokens->each(function ($token) {
            $this->assertInstanceOf(UnencryptedToken::class, $token);
        });
    }
}
