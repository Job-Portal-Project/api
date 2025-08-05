<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Job Portal API',
    description: 'A comprehensive job portal API with RSA-512 signed JWT authentication. The system uses separate access and refresh tokens stored in a database with automatic revocation and blacklisting capabilities.',
    contact: new OA\Contact(
        name: 'Job Portal API Support',
        url: 'https://example.com/support',
        email: 'support@example.com'
    )
)]
#[OA\Server(
    url: '/api/v1',
    description: 'Job Portal API Server - All endpoints are prefixed with /api/v1'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'JWT Access Token - Use the access token from authentication/registration response. Format: Bearer {access_jwt_token}'
)]
#[OA\SecurityScheme(
    securityScheme: 'refreshAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'JWT Refresh Token - Use the refresh token to get new access tokens. Format: Bearer {refresh_jwt_token}'
)]
#[OA\Schema(
    schema: 'ValidationError',
    title: 'Validation Error Response',
    description: 'Standard Laravel validation error response',
    properties: [
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'The given data was invalid.',
            description: 'Human readable error message'
        ),
        new OA\Property(
            property: 'errors',
            type: 'object',
            description: 'Field-specific validation errors',
            example: [
                'email' => ['The email field is required.', 'The email must be a valid email address.'],
                'password' => ['The password must be at least 8 characters.'],
            ]
        ),
    ]
)]
#[OA\Schema(
    schema: 'AuthenticationError',
    title: 'Authentication Error Response',
    description: 'Authentication failure response',
    properties: [
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'Unauthenticated.',
            description: 'Authentication error message'
        ),
    ]
)]
#[OA\Schema(
    schema: 'Industry',
    title: 'Industry',
    description: 'Industry model representing business sectors',
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            example: 1,
            description: 'Unique industry identifier'
        ),
        new OA\Property(
            property: 'name',
            type: 'string',
            example: 'Technology',
            description: 'Industry name'
        ),
        new OA\Property(
            property: 'created_at',
            type: 'string',
            format: 'date-time',
            example: '2024-01-15T10:30:00.000000Z',
            description: 'Timestamp when the industry was created'
        ),
        new OA\Property(
            property: 'updated_at',
            type: 'string',
            format: 'date-time',
            example: '2024-01-15T10:30:00.000000Z',
            description: 'Timestamp when the industry was last updated'
        ),
        new OA\Property(
            property: 'occupations',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Occupation'),
            description: 'Array of related occupations (only included when relations[occupations] is requested or in show endpoint)',
            nullable: true
        ),
    ]
)]
#[OA\Schema(
    schema: 'Occupation',
    title: 'Occupation',
    description: 'Occupation model representing job roles within industries',
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            example: 1,
            description: 'Unique occupation identifier'
        ),
        new OA\Property(
            property: 'name',
            type: 'string',
            example: 'Software Engineer',
            description: 'Occupation name'
        ),
        new OA\Property(
            property: 'industry_id',
            type: 'integer',
            example: 1,
            description: 'ID of the industry this occupation belongs to'
        ),
        new OA\Property(
            property: 'created_at',
            type: 'string',
            format: 'date-time',
            example: '2024-01-15T10:30:00.000000Z',
            description: 'Timestamp when the occupation was created'
        ),
        new OA\Property(
            property: 'updated_at',
            type: 'string',
            format: 'date-time',
            example: '2024-01-15T10:30:00.000000Z',
            description: 'Timestamp when the occupation was last updated'
        ),
        new OA\Property(
            property: 'industry',
            ref: '#/components/schemas/Industry',
            description: 'Related industry object (when relationship is loaded)',
            nullable: true
        ),
    ]
)]
abstract class Controller
{
    //
}
