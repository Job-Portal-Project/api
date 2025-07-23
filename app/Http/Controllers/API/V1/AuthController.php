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
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthController extends Controller
{
    use TokenValidation;

    /**
     * Register a new user.
     *
     * @param  Request  $request
     * @param  UserRepository  $repository
     * @return JsonResponse
     */
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

    /**
     * Authenticate a user and return a JWT token.
     *
     * @param  Request  $request
     * @param  TokenRepository  $repository
     * @return JsonResponse
     * @throws Throwable
     */
    public function authenticate(Request $request, TokenRepository $repository): JsonResponse
    {
        list($email, $password) = array_values($request->validate([
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

    /**
     * Revoke the current JWT token.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function revoke(Request $request): JsonResponse
    {
        $token = $this->getTokenForRequest($request);

        $this->service->revoke(Token::whereJsonContains('token->claims->grp', $token->claims()->get('grp'))->get());

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Refresh the JWT token.
     *
     * @param  TokenRepository  $repository
     * @return JsonResponse
     */
    public function refresh(TokenRepository $repository): JsonResponse
    {
        $user = auth('api')->user();

        $tokens = $repository->create([$user])->new_tokens;

        $response = new JsonResponse([
            'new_tokens' => TokenResource::collection($tokens)
        ], Response::HTTP_CREATED);

        return $response;
    }

    /**
     * Get the authenticated user's details.
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        $response = $this->userResponse($user);

        return $response;
    }

    /**
     * Generate a user response with the given resource.
     *
     * @param  Authenticatable|null  $record
     * @param  int  $status
     * @return JsonResponse
     */
    private function userResponse(?Authenticatable $record, int $status = 200): JsonResponse
    {
        $resource = new UserResource($record);
        $response = new JsonResponse($resource, $status);

        return $response;
    }
}
