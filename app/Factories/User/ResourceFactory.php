<?php

namespace App\Factories\User;

use App\Enums\Role;
use App\Http\Resources\Authenticated\AdminResource;
use App\Http\Resources\Authenticated\CandidateResource;
use App\Http\Resources\Authenticated\CompanyResource;
use App\Http\Resources\Authenticated\ModeratorResource;
use App\Http\Resources\Authenticated\UserResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use LogicException;
use Spatie\Permission\Traits\HasRoles;

class ResourceFactory extends UserResource
{
    public const ROLE_RESOURCE_MAPPING = [
        Role::CANDIDATE->value => CandidateResource::class,
        Role::COMPANY->value => CompanyResource::class,
        Role::MODERATOR->value => ModeratorResource::class,
        Role::ADMIN->value => AdminResource::class,
    ];

    /**
     * Create the appropriate resource based on user roles
     *
     * @throws LogicException
     */
    private static function create(Authenticatable $user): UserResource
    {
        if (! in_array(HasRoles::class, class_uses_recursive($user))) {
            throw new LogicException("The user must use '".HasRoles::class."' trait.");
        }

        /** @var Collection $userRoles */
        $userRoles = $user->getAttribute('roles')->pluck('name')->toArray();

        if (empty($userRoles)) {
            return new UserResource($user);
        }

        foreach (self::ROLE_RESOURCE_MAPPING as $role => $resourceClass) {
            if (in_array($role, $userRoles, true)) {
                return new $resourceClass($user);
            }
        }

        return new UserResource($user);
    }

    public function toArray(Request $request): array
    {
        return self::create($this->resource)->toArray($request);
    }
}
