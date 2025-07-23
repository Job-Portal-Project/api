<?php

namespace App\Casts;

use App\Contracts\JWT\TokenServiceInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonException;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\UnencryptedToken;

class TokenCast implements CastsAttributes
{
    protected TokenServiceInterface $service;

    public function __construct(?TokenServiceInterface $service = null)
    {
        $this->service = $service ?? app()->get(TokenServiceInterface::class);
    }

    /**
     * Cast the given value.
     *
     * @param array<string, mixed> $attributes
     * @throws JsonException
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $decoded = json_decode(
            json: $value,
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );

        $claims = collect($decoded['claims'])->map(function ($value, $claim) {
            if (in_array($claim, RegisteredClaims::DATE_CLAIMS)) {
                return Carbon::parse($value['date'], $value['timezone'])->toDateTimeImmutable();
            }

            return $value;
        });

        $token = $this->service->build($claims);

        return $token;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param array<string, mixed> $attributes
     * @throws JsonException
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        /** @var  UnencryptedToken $value */

        $components = [
            'headers' => $value->headers()->all(),
            'claims' => $value->claims()->all(),
        ];

        return json_encode(
            $components,
            JSON_THROW_ON_ERROR
        );
    }
}
