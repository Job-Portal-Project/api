<?php

namespace Tests\Feature\Http\Controllers\API\V1;

use App\Contracts\JWT\TokenServiceInterface;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IndustryControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
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
        $response = $this
            ->authenticated()
            ->get(route('industries.index'));

        $response->assertStatus(200);
        $data = $response->json('data');

        if (! empty($data)) {
            $this->assertArrayNotHasKey('occupations', $data[0]);
        }
    }

    public function test_industry_index_with_relationships(): void
    {
        $response = $this
            ->authenticated()
            ->get(route('industries.index', ['relations' => ['occupations' => true]]));

        $response->assertStatus(200);
        $data = $response->json('data');

        if (! empty($data)) {
            $this->assertArrayHasKey('occupations', $data[0]);
        }
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
