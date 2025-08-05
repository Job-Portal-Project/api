<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\IndustryResource;
use App\Models\Industry;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Industries',
    description: 'Industry management endpoints. Industries represent broad categories of business sectors (e.g., Technology, Healthcare, Finance) and can contain multiple occupations. Supports filtering, searching, sorting, and optional relation loading.'
)]
class IndustryController extends Controller
{
    #[OA\Get(
        path: '/industries',
        summary: 'Get paginated list of industries',
        description: 'Retrieve a paginated list of industries with filtering, searching, and sorting capabilities. Optionally include related occupations. Supports full-text search by industry name.',
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
                description: 'Search term to filter industries by name (case-insensitive partial match)',
                schema: new OA\Schema(type: 'string', example: 'Technology')
            ),
            new OA\Parameter(
                name: 'relations[occupations]',
                in: 'query',
                required: false,
                description: 'Include related occupations in the response (set to "1" or "true" to include)',
                schema: new OA\Schema(type: 'string', enum: ['0', '1', 'false', 'true'], example: '1')
            ),
            new OA\Parameter(
                name: 'Accept-Language',
                in: 'header',
                required: false,
                description: 'Preferred language for response messages',
                schema: new OA\Schema(type: 'string', example: 'en')
            ),
        ],
        tags: ['Industries'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of industries retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Industry'),
                            description: 'Array of industry objects'
                        ),
                        new OA\Property(
                            property: 'links',
                            type: 'object',
                            description: 'Pagination links',
                            properties: [
                                new OA\Property(property: 'first', type: 'string', example: 'http://localhost/api/v1/industries?page=1'),
                                new OA\Property(property: 'last', type: 'string', example: 'http://localhost/api/v1/industries?page=10'),
                                new OA\Property(property: 'prev', type: 'string', nullable: true, example: null),
                                new OA\Property(property: 'next', type: 'string', example: 'http://localhost/api/v1/industries?page=2'),
                            ]
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            description: 'Pagination metadata',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'from', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 10),
                                new OA\Property(property: 'per_page', type: 'integer', example: 10),
                                new OA\Property(property: 'to', type: 'integer', example: 10),
                                new OA\Property(property: 'total', type: 'integer', example: 95),
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

    #[OA\Get(
        path: '/industries/{industry}',
        summary: 'Get specific industry by ID',
        description: 'Retrieve a specific industry by its ID. Always includes related occupations in the response to provide complete industry information.',
        parameters: [
            new OA\Parameter(
                name: 'industry',
                in: 'path',
                required: true,
                description: 'Industry ID',
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
        tags: ['Industries'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Industry retrieved successfully with related occupations',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Industry',
                            description: 'Industry object with occupations relationship loaded'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Industry not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'No query results for model [App\\Models\\Industry] 999',
                            description: 'Error message indicating the industry was not found'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function show(Industry $industry)
    {
        return response()->json(new IndustryResource($industry->load('occupations')));
    }
}
