<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OccupationResource;
use App\Models\Occupation;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Occupations',
    description: 'Occupation management endpoints. Occupations represent specific job roles within industries (e.g., Software Engineer, Data Analyst, Marketing Manager). Supports filtering, searching, and sorting capabilities.'
)]
class OccupationController extends Controller
{
    #[OA\Get(
        path: '/occupations',
        summary: 'Get paginated list of occupations',
        description: 'Retrieve a paginated list of occupations with filtering, searching, and sorting capabilities. Supports full-text search by occupation name.',
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Page number for pagination (starts from 1)',
                schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
            ),
            new OA\Parameter(
                name: 'size',
                in: 'query',
                required: false,
                description: 'Number of items per page (1-100)',
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, example: 10)
            ),
            new OA\Parameter(
                name: 'order',
                in: 'query',
                required: false,
                description: 'Sort direction',
                schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], example: 'asc')
            ),
            new OA\Parameter(
                name: 'order_by',
                in: 'query',
                required: false,
                description: 'Field to sort by',
                schema: new OA\Schema(type: 'string', enum: ['id', 'name', 'created_at', 'updated_at'], example: 'id')
            ),
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Search term to filter occupations by name (case-insensitive partial match)',
                schema: new OA\Schema(type: 'string', example: 'Engineer')
            ),
            new OA\Parameter(
                name: 'Accept-Language',
                in: 'header',
                required: false,
                description: 'Preferred language for response messages',
                schema: new OA\Schema(type: 'string', example: 'en')
            ),
        ],
        tags: ['Occupations'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of occupations retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Occupation'),
                            description: 'Array of occupation objects'
                        ),
                        new OA\Property(
                            property: 'links',
                            type: 'object',
                            description: 'Pagination links',
                            properties: [
                                new OA\Property(property: 'first', type: 'string', example: 'http://localhost/api/v1/occupations?page=1'),
                                new OA\Property(property: 'last', type: 'string', example: 'http://localhost/api/v1/occupations?page=25'),
                                new OA\Property(property: 'prev', type: 'string', nullable: true, example: null),
                                new OA\Property(property: 'next', type: 'string', example: 'http://localhost/api/v1/occupations?page=2'),
                            ]
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            description: 'Pagination metadata',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'from', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 25),
                                new OA\Property(property: 'per_page', type: 'integer', example: 10),
                                new OA\Property(property: 'to', type: 'integer', example: 10),
                                new OA\Property(property: 'total', type: 'integer', example: 245),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error - Invalid query parameters',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
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

        return OccupationResource::collection($occupations);
    }

    #[OA\Get(
        path: '/occupations/{occupation}',
        summary: 'Get specific occupation by ID',
        description: 'Retrieve a specific occupation by its ID. Returns basic occupation information without related industry data to keep response lightweight.',
        parameters: [
            new OA\Parameter(
                name: 'occupation',
                in: 'path',
                required: true,
                description: 'Occupation ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'Accept-Language',
                in: 'header',
                required: false,
                description: 'Preferred language for response messages',
                schema: new OA\Schema(type: 'string', example: 'en')
            ),
        ],
        tags: ['Occupations'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Occupation retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Occupation',
                            description: 'Occupation object without industry relationship loaded'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Occupation not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'No query results for model [App\\Models\\Occupation] 999',
                            description: 'Error message indicating the occupation was not found'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function show(Occupation $occupation)
    {
        return response()->json(new OccupationResource($occupation));
    }
}
