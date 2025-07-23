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
abstract class Controller
{
    //
}
