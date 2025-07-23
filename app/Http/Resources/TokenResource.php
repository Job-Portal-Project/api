<?php

namespace App\Http\Resources;

use App\Contracts\JWT\TokenServiceInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Lcobucci\JWT\UnencryptedToken;

class TokenResource extends JsonResource
{
    protected TokenServiceInterface $service;

    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->service = app()->get(TokenServiceInterface::class);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function toArray(Request $request): array
    {
        /** @var UnencryptedToken $token */
        $token = $this->resource->token;

        return [
            'headers' => $token->headers()->all(),
            'claims' => $token->claims()->all(),
            'token' => $token->toString(),
        ];
    }
}
