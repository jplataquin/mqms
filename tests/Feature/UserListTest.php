<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserListTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function createApplication()
    {
        $app = parent::createApplication();

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]]);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'status' => 'ACTV',
        ]);
    }

    /** @test */
    public function it_can_render_the_users_list_page()
    {
        $response = $this->actingAs($this->user)->get('/users');
        $response->assertStatus(200);
        $response->assertSee('Users');
        $response->assertSee('id="list"', false);
        $response->assertSee('item.status', false);
        $response->assertSee('justify-content-between', false);
        $response->assertSee('bg-success', false);
        $response->assertSee('bg-danger', false);
    }

    /** @test */
    public function it_returns_user_status_in_api_user_list()
    {
        $deactivatedUser = User::factory()->create([
            'name' => 'Deactivated User',
            'email' => 'deactivated@example.com',
            'status' => 'DCTV',
        ]);

        $response = $this->actingAs($this->user)->get('/api/user/list');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);

        $activeItem = collect($data)->firstWhere('id', $this->user->id);
        $this->assertNotNull($activeItem);
        $this->assertEquals('ACTV', $activeItem['status']);

        $deactivatedItem = collect($data)->firstWhere('id', $deactivatedUser->id);
        $this->assertNotNull($deactivatedItem);
        $this->assertEquals('DCTV', $deactivatedItem['status']);
    }
}
