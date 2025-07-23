<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TokenResource;
use App\Http\Resources\UserResource;
use App\Models\JWT\Token;
use App\Models\User;
use App\Repositories\TokenRepository;
use App\Repositories\UserRepository;
use App\Traits\TokenValidation;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag(
    name: 'Authentication',
    description: 'User authentication and registration endpoints with RSA-512 signed JWT tokens. Supports both access and refresh tokens with database storage and automatic revocation.'
)]
class AuthController extends Controller
{
    use TokenValidation;

    #[OA\Post(
        path: '/auth/register',
        summary: 'Register a new user',
        description: 'Create a new user account in the job portal system. Returns user data with both access and refresh JWT tokens for immediate use.',
        requestBody: new OA\RequestBody(
            description: 'User registration data',
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'passwordConfirm'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'John Doe',
                        description: 'User full name',
                        minLength: 1,
                        maxLength: 255
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'john@example.com',
                        description: 'Valid email address (must be unique)',
                        maxLength: 255
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        minLength: 8,
                        example: 'SecurePass123!',
                        description: 'Password with minimum 8 characters'
                    ),
                    new OA\Property(
                        property: 'passwordConfirm',
                        type: 'string',
                        format: 'password',
                        example: 'SecurePass123!',
                        description: 'Password confirmation (must match password)'
                    ),
                ]
            )
        ),
        parameters: [
            new OA\Parameter(
                name: 'Accept-Language',
                in: 'header',
                required: false,
                description: 'Preferred language for response messages (e.g., en, es, fr)',
                schema: new OA\Schema(type: 'string', example: 'en')
            ),
        ],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'User successfully registered with JWT tokens',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/User',
                            description: 'User data including new_tokens array with access and refresh tokens'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error - Invalid or missing required fields',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function register(Request $request, UserRepository $repository): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'passwordConfirm' => 'required|same:password',
        ]);

        $user = $repository->create($validated);
        $response = $this->userResponse($user, Response::HTTP_CREATED);

        return $response;
    }

    #[OA\Post(
        path: '/auth/authenticate',
        summary: 'Authenticate user',
        description: 'Authenticate a user with email and password. Returns user data with both access and refresh JWT tokens. Tokens are RSA-512 signed and stored in the database for security tracking.',
        requestBody: new OA\RequestBody(
            description: 'User login credentials',
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'john@example.com',
                        description: 'Registered user email address'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'SecurePass123!',
                        description: 'User password'
                    ),
                ]
            )
        ),
        parameters: [
            new OA\Parameter(
                name: 'Accept-Language',
                in: 'header',
                required: false,
                description: 'Preferred language for response messages',
                schema: new OA\Schema(type: 'string', example: 'en')
            ),
        ],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authentication successful with JWT tokens',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/User',
                            description: 'User data including new_tokens array with access and refresh tokens'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Authentication failed - Invalid credentials',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthenticationError')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error - Invalid or missing email/password',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function authenticate(Request $request, TokenRepository $repository): JsonResponse
    {
        [$email, $password] = array_values($request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]));

        $user = User::where('email', $email)->first();

        throw_if(
            ! $user || ! Hash::check($password, $user->password),
            AuthenticationException::class,
            'Unauthenticated.'
        );

        $response = $this->userResponse($repository->create([$user]), Response::HTTP_OK);

        return $response;
    }

    #[OA\Delete(
        path: '/auth/revoke',
        summary: 'Revoke user tokens',
        description: 'Revoke the current JWT token and all tokens in the same session group. This effectively logs out the user from all devices. Requires a valid access token.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'Accept-Language',
                in: 'header',
                required: false,
                description: 'Preferred language for response messages',
                schema: new OA\Schema(type: 'string', example: 'en')
            ),
        ],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Tokens successfully revoked - User logged out from all devices'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized - Invalid, expired, or missing access token',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthenticationError')
            ),
        ]
    )]
    public function revoke(Request $request): JsonResponse
    {
        $token = $this->getTokenForRequest($request);

        if ($token) {
            $this->service->revoke(Token::whereJsonContains('token->claims->grp', $token->claims()->get('grp'))->get());
        }

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    #[OA\Post(
        path: '/auth/refresh',
        summary: 'Refresh JWT tokens',
        description: 'Generate new access and refresh JWT tokens using a valid refresh token. The old tokens are automatically revoked. This endpoint requires a refresh token, not an access token.',
        security: [['refreshAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'Accept-Language',
                in: 'header',
                required: false,
                description: 'Preferred language for response messages',
                schema: new OA\Schema(type: 'string', example: 'en')
            ),
        ],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'New tokens successfully generated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'new_tokens',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Token'),
                            description: 'Array containing new access and refresh tokens'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized - Invalid, expired, revoked, or missing refresh token',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthenticationError')
            ),
        ]
    )]
    public function refresh(TokenRepository $repository): JsonResponse
    {
        /** @var \Illuminate\Foundation\Auth\User $user */
        $user = auth('api')->user();

        $userWithTokens = $repository->create([$user]);

        /** @var \Illuminate\Support\Collection $tokens */
        $tokens = $userWithTokens->getAttribute('new_tokens');

        $response = new JsonResponse([
            'new_tokens' => TokenResource::collection($tokens),
        ], Response::HTTP_CREATED);

        return $response;
    }

    #[OA\Get(
        path: '/auth/me',
        summary: 'Get authenticated user profile',
        description: 'Retrieve the authenticated user\'s profile information using a valid access token. Returns user data without tokens.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'Accept-Language',
                in: 'header',
                required: false,
                description: 'Preferred language for response messages',
                schema: new OA\Schema(type: 'string', example: 'en')
            ),
        ],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User profile retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/User',
                            description: 'User profile data (new_tokens will be null)'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized - Invalid, expired, revoked, or missing access token',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthenticationError')
            ),
        ]
    )]
    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        $response = $this->userResponse($user);

        return $response;
    }

    /**
     * Generate a user response with the given resource.
     */
    private function userResponse(?Authenticatable $record, int $status = 200): JsonResponse
    {
        $resource = new UserResource($record);
        $response = new JsonResponse($resource, $status);

        return $response;
    }
}
