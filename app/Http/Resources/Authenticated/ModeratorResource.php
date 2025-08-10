<?php

namespace App\Http\Resources\Authenticated;

use App\Enums\Role;
use Illuminate\Http\Request;

class ModeratorResource extends UserResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'profile' => [
                'full_name' => $this->resource(Role::MODERATOR)->getAttribute('full_name'),
            ]
        ]);
    }
}
