<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\IndustryResource;
use App\Models\Industry;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class IndustryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'page' => 'nullable|integer',
            'size' => 'nullable|integer',
            'order' => 'nullable|string|in:asc,desc',
            'order_by' => 'nullable|string|in:id,name,created_at,updated_at',
            'search' => 'nullable|string',
            'relations' => 'nullable|array',
            'relations.occupations' => 'nullable|bool',
        ]);

        $filters = [
            'page' => $filters['page'] ?? 1,
            'size' => $filters['size'] ?? 10,
            'order' => $filters['order'] ?? 'asc',
            'order_by' => $filters['order_by'] ?? 'id',
            'search' => $filters['search'] ?? null,
            'relations' => $filters['relations'] ?? [
                'occupations' => false,
            ],
        ];

        $industries = Industry::query()
            ->when(
                ($fetchOccupations = Arr::get($filters, 'relations.occupations')) && $fetchOccupations === '1',
                fn ($query, $occupations) => $query->with('occupations')
            )
            ->when(
                $filters['search'],
                fn ($query, $needle) => $query->where(
                    'name',
                    'LIKE',
                    "%{$needle}%"
                )
            )
            ->orderBy($filters['order_by'], $filters['order'])
            ->paginate(
                perPage: $filters['size'],
                page: $filters['page'],
            );

        return IndustryResource::collection($industries);
    }

    /**
     * Display the specified resource.
     */
    public function show(Industry $industry)
    {
        return response()->json(new IndustryResource($industry->load('occupations')));
    }
}
