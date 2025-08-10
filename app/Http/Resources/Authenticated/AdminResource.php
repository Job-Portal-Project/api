<?php

namespace App\Http\Resources\Authenticated;

use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends UserResource
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
                'phone' => $this->resource(Role::ADMIN)->getAttribute('phone'),
            ]
        ]);
    }
}
