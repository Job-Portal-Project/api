<?php

namespace App\Http\Resources\Authenticated;

use App\Enums\Role;
use App\Http\Resources\Authenticated\UserResource as BaseResource;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Http\Request;

class CandidateResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'profile' => [
                'name' => $this->resource(Role::CANDIDATE)->getAttribute('name'),
            ]
        ]);
    }
}
