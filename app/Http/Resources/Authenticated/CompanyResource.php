<?php

namespace App\Http\Resources\Authenticated;

use App\Enums\Role;
use Illuminate\Http\Request;

class CompanyResource extends UserResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'profile' => [
                'website' => $this->resource(Role::COMPANY)->getAttribute('website'),
            ]
        ]);
    }
}
