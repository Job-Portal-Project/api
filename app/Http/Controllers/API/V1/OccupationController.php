<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OccupationResource;
use App\Models\Industry;
use App\Models\Occupation;
use Illuminate\Http\Request;

class OccupationController extends Controller
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
        ]);

        $filters = [
            'page' => $filters['page'] ?? 1,
            'size' => $filters['size'] ?? 10,
            'order' => $filters['order'] ?? 'asc',
            'order_by' => $filters['order_by'] ?? 'id',
            'search' => $filters['search'] ?? null,
        ];

        $occupations = Occupation::query()
            ->when(
                $filters['search'],
                fn($query, $needle) => $query->where(
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

        return OccupationResource::collection($occupations);
    }

    /**
     * Display the specified resource.
     */
    public function show(Occupation $occupation)
    {
        return response()->json(new OccupationResource($occupation));
    }
}
