<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $basePath = realpath(__DIR__ . '/../..');
        putenv("APP_BASE_PATH={$basePath}");
        $_ENV['APP_BASE_PATH'] = $basePath;

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

    /** @test */
    public function guest_can_view_corporate_technological_welcome_page()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('MQMS');
        $response->assertSee('data-bs-theme="dark"', false);
        $response->assertSee('SYSTEM ONLINE');
        $response->assertSee('Engineering &amp; Quantity Control Platform', false);
        $response->assertSee('Operator Sign In');
        $response->assertSee('Work Breakdown Structure');
        $response->assertSee('Procurement &amp; Canvassing', false);
        $response->assertSee('AI &amp; MCP Integration', false);
        $response->assertSee('Registered Schema Entities');
    }

    /** @test */
    public function authenticated_user_sees_dashboard_and_token_actions()
    {
        $user = User::factory()->create([
            'name' => 'Commander J. Vance',
            'email' => 'vance@patrila.com',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('MQMS');
        $response->assertSee('Dashboard');
        $response->assertSee('Access Dashboard');
        $response->assertSee('API &amp; MCP Tokens', false);
        $response->assertDontSee('Operator Sign In');
    }
}
