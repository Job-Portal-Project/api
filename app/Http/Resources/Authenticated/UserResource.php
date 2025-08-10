<?php

namespace App\Http\Resources\Authenticated;

use App\Http\Resources\TokenResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    title: 'User',
    description: 'User model with JWT token support',
    required: ['id', 'name', 'email', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            example: 1,
            description: 'Unique user identifier'
        ),
        new OA\Property(
            property: 'name',
            type: 'string',
            example: 'John Doe',
            description: 'User full name'
        ),
        new OA\Property(
            property: 'email',
            type: 'string',
            format: 'email',
            example: 'john@example.com',
            description: 'User email address (unique)'
        ),
        new OA\Property(
            property: 'email_verified_at',
            type: 'string',
            format: 'date-time',
            nullable: true,
            example: '2024-01-15T09:30:00.000000Z',
            description: 'Email verification timestamp (null if not verified)'
        ),
        new OA\Property(
            property: 'created_at',
            type: 'string',
            format: 'date-time',
            example: '2024-01-15T09:30:00.000000Z',
            description: 'User creation timestamp'
        ),
        new OA\Property(
            property: 'updated_at',
            type: 'string',
            format: 'date-time',
            example: '2024-01-15T09:30:00.000000Z',
            description: 'User last update timestamp'
        ),
        new OA\Property(
            property: 'new_tokens',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Token'),
            nullable: true,
            description: 'Array of newly generated JWT tokens (access and refresh). Only present immediately after authentication/registration/refresh operations.'
        ),
    ]
)]
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'new_tokens' => $this->whenHas('new_tokens', fn () => TokenResource::collection($this->resource->getAttribute('new_tokens'))),
        ]);
    }
}
