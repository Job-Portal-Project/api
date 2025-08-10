<?php

namespace App\Http\Resources\Authenticated;

use App\Http\Resources\Authenticated\UserResource as BaseResource;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Http\Request;

class CandidateResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'name' => $this->resource()->getAttribute('name'),
        ]);
    }
}
