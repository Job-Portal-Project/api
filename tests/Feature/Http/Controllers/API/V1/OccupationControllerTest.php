<?php

namespace Tests\Feature\Http\Controllers\API\V1;

use App\Contracts\JWT\TokenServiceInterface;
use App\Models\Industry;
use App\Models\Occupation;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Assets\Traits\AuthTestHelpers;
use Tests\TestCase;

class OccupationControllerTest extends TestCase
{
    use AuthTestHelpers;

    public function test_occupation_index_requires_authentication(): void
    {
        $response = $this->get(route('occupations.index'), [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(401);
    }

    public function test_occupation_index(): void
    {
        $industry = Industry::factory()->create();
        Occupation::factory()->count(5)->create(['industry_id' => $industry->id]);

        $response = $this
            ->authenticated()
            ->get(route('occupations.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'industry_id', 'created_at', 'updated_at'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_occupation_index_with_pagination(): void
    {
        $industry = Industry::factory()->create();
        Occupation::factory()->count(15)->create(['industry_id' => $industry->id]);

        $response = $this
            ->authenticated()
            ->get(route('occupations.index', ['page' => 1, 'size' => 5]));

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonCount(5, 'data');
    }

    public function test_occupation_index_with_search(): void
    {
        $industry = Industry::factory()->create()->occupations()->createMany([
            ['name' => $name1 = 'Software Engineering'],
            ['name' => $name2 = 'Data Analyst'],
        ]);

        $response = $this
            ->authenticated()
            ->get(route('occupations.index', ['search' => 'Software', 'size' => 9999]), [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);

        $response->assertStatus(200);

        $names = array_column($response->json('data'), 'name');

        collect([$name1, $name2])->each(fn (string $name) => in_array($name, $names));
    }

    public function test_occupation_index_with_ordering(): void
    {
        $industry = Industry::factory()->create()->occupations()->createMany([
            ['name' => $name1 = 'Business Analyst'],
            ['name' => $name2 = 'Industrial Engineer'],
        ]);

        $response = $this
            ->authenticated()
            ->get(route('occupations.index', ['order_by' => 'name', 'order' => 'asc', 'size' => 9999]), [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);

        $response->assertStatus(200);

        $names = array_column($response->json('data'), 'name');

        collect([$name1, $name2])
            ->each(fn (string $name) => $this->assertTrue(in_array($name, $names)));
    }

    public function test_occupation_index_validates_parameters(): void
    {
        $response = $this
            ->authenticated()
            ->get(route('occupations.index', ['page' => 'invalid']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['page']);
    }

    public function test_occupation_show_requires_authentication(): void
    {
        $industry = Industry::factory()->create();
        $occupation = Occupation::factory()->create(['industry_id' => $industry->id]);

        $response = $this->get(route('occupations.show', $occupation), [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(401);
    }

    public function test_occupation_show(): void
    {
        $industry = Industry::factory()->create();
        $occupation = Occupation::factory()->create(['industry_id' => $industry->id]);

        $response = $this
            ->authenticated()
            ->get(route('occupations.show', $occupation));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id', 'name', 'industry_id', 'created_at', 'updated_at',
            ])
            ->assertJson([
                'id' => $occupation->id,
                'name' => $occupation->name,
                'industry_id' => $occupation->industry_id,
            ]);
    }

    public function test_occupation_show_not_found(): void
    {
        $response = $this
            ->authenticated()
            ->get(route('occupations.show', 999));

        $response->assertStatus(404);
    }

    public function test_occupation_index_search_returns_empty_when_no_match(): void
    {
        $industry = Industry::factory()->create();
        Occupation::factory()->create([
            'name' => 'Software Engineer',
            'industry_id' => $industry->id,
        ]);

        $response = $this
            ->authenticated()
            ->get(route('occupations.index', ['search' => 'NonExistentJob']));

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}
