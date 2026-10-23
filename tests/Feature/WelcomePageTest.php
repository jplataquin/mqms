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
    public function guest_can_view_minimalist_technological_welcome_page()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('MQMS');
        $response->assertSee('Material &amp; Quantity Management System', false);
        $response->assertSee('id="tech-canvas"', false);
        $response->assertSee('Sign In');
        $response->assertSee('data-bs-theme="dark"', false);

        // Verify all cards and text blocks have been removed
        $response->assertDontSee('Core System Modules');
        $response->assertDontSee('module-card');
        $response->assertDontSee('Engineering &amp; Quantity Control Platform', false);
    }

    /** @test */
    public function authenticated_user_sees_enter_dashboard_action()
    {
        $user = User::factory()->create([
            'name' => 'Commander J. Vance',
            'email' => 'vance@patrila.com',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('MQMS');
        $response->assertSee('Enter Dashboard');
        $response->assertDontSee('Sign In');
    }
}
