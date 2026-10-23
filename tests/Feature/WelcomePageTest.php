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
    public function guest_can_view_futuristic_welcome_page()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('MQMS // AEON');
        $response->assertSee('data-bs-theme="dark"', false);
        $response->assertSee('GATEWAY ACTIVE &bull; MCP READY', false);
        $response->assertSee('Precision Resource');
        $response->assertSee('Initialize Operator Session');
        $response->assertSee('Sign In');
        $response->assertSee('Work Breakdown Structure');
        $response->assertSee('Autonomous AI & MCP Protocol', false);
        $response->assertSee('Model Context Protocol Enabled');
    }

    /** @test */
    public function authenticated_user_sees_mission_control_and_dashboard_actions()
    {
        $user = User::factory()->create([
            'name' => 'Commander J. Vance',
            'email' => 'vance@patrila.com',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('MQMS // AEON');
        $response->assertSee('Mission Control');
        $response->assertSee('Launch Mission Dashboard');
        $response->assertSee('Manage MCP Tokens');
        $response->assertDontSee('Initialize Operator Session');
    }
}
