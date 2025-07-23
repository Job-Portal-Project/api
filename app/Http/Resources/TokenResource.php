<?php

namespace App\Http\Resources;

use App\Contracts\JWT\TokenServiceInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Lcobucci\JWT\UnencryptedToken;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Token',
    title: 'JWT Token',
    description: 'RSA-512 signed JWT token with database backing',
    required: ['headers', 'claims', 'token'],
    properties: [
        new OA\Property(
            property: 'headers',
            type: 'object',
            description: 'JWT token headers with RSA-512 signature algorithm',
            example: [
                'typ' => 'JWT',
                'alg' => 'RS512',
            ]
        ),
        new OA\Property(
            property: 'claims',
            type: 'object',
            description: 'JWT token claims with user and token metadata',
            example: [
                'iss' => 'job-portal-api',
                'sub' => '1',
                'aud' => 'job-portal-client',
                'exp' => 1737633000,
                'nbf' => 1737547200,
                'iat' => 1737547200,
                'jti' => '01936b2e-4c3e-7234-9876-0123456789ab',
                'typ' => 'access',
                'grp' => 'auth_session_123',
            ]
        ),
        new OA\Property(
            property: 'token',
            type: 'string',
            description: 'The complete JWT token string (RSA-512 signed)',
            example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzUxMiJ9.eyJpc3MiOiJqb2ItcG9ydGFsLWFwaSIsInN1YiI6IjEiLCJhdWQiOiJqb2ItcG9ydGFsLWNsaWVudCIsImV4cCI6MTczNzYzMzAwMCwibmJmIjoxNzM3NTQ3MjAwLCJpYXQiOjE3Mzc1NDcyMDAsImp0aSI6IjAxOTM2YjJlLTRjM2UtNzIzNC05ODc2LTAxMjM0NTY3ODlhYiIsInR5cCI6ImFjY2VzcyIsImdycCI6ImF1dGhfc2Vzc2lvbl8xMjMifQ.signature...'
        ),
    ]
)]
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
