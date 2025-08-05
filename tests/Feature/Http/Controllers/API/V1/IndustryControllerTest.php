<?php

namespace Tests\Feature\Http\Controllers\API\V1;

use App\Contracts\JWT\TokenServiceInterface;
use App\Models\Industry;
use App\Models\Occupation;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IndustryControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_industry_index_requires_authentication(): void
    {
        $response = $this->get(route('industries.index'), [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(401);
    }

    public function test_industry_index(): void
    {
        $response = $this
            ->authenticated()
            ->get(route('industries.index'));

        $response->assertStatus(200);
    }

    public function test_industry_index_without_relationships(): void
    {
        Industry::factory()->count(3)->create();

        $response = $this
            ->authenticated()
            ->get(route('industries.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'created_at', 'updated_at'],
                ],
                'links',
                'meta',
            ]);

        $data = $response->json('data');
        if (! empty($data)) {
            $this->assertArrayNotHasKey('occupations', $data[0]);
        }
    }

    public function test_industry_index_with_relationships(): void
    {
        $industry = Industry::factory()->create();
        Occupation::factory()->count(2)->create(['industry_id' => $industry->id]);

        $response = $this
            ->authenticated()
            ->get(route('industries.index', ['relations' => ['occupations' => true]]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'name', 'created_at', 'updated_at',
                        'occupations' => [
                            '*' => ['id', 'name', 'industry_id', 'created_at', 'updated_at'],
                        ],
                    ],
                ],
                'links',
                'meta',
            ]);

        $data = $response->json('data');
        if (! empty($data)) {
            $this->assertArrayHasKey('occupations', $data[0]);
        }
    }

    public function test_industry_show_requires_authentication(): void
    {
        $industry = Industry::factory()->create();

        $response = $this->get(route('industries.show', $industry), [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(401);
    }

    public function test_industry_show(): void
    {
        $industry = Industry::factory()->create();

        $response = $this
            ->authenticated()
            ->get(route('industries.show', $industry));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id', 'name', 'created_at', 'updated_at',
            ])
            ->assertJson([
                'id' => $industry->id,
                'name' => $industry->name,
            ]);
    }

    public function test_industry_show_with_relationships(): void
    {
        $industry = Industry::factory()->create();
        Occupation::factory()->count(3)->create(['industry_id' => $industry->id]);

        $response = $this
            ->authenticated()
            ->get(route('industries.show', [$industry, 'relations' => ['occupations' => true]]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id', 'name', 'created_at', 'updated_at',
                'occupations' => [
                    '*' => ['id', 'name', 'industry_id', 'created_at', 'updated_at'],
                ],
            ]);
    }

    public function test_industry_show_not_found(): void
    {
        $response = $this
            ->authenticated()
            ->get(route('industries.show', 999));

        $response->assertStatus(404);
    }

    private function authenticated(): IndustryControllerTest
    {
        $user = (new UserRepository(app()->make(TokenServiceInterface::class)))->create(
            User::factory()->definition(),
        );

        return $this->actingAs($user, 'api')
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$user->getAttribute('new_tokens')->get(0)->token->toString(),
            ]);
    }
}
