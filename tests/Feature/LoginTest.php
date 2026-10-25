<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
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

    /**
     * Test that the login page renders successfully with the animated canvas background.
     */
    public function test_login_screen_renders_with_welcome_animated_background()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);

        // Verify animated canvas background matches welcome page
        $response->assertSee('<canvas id="tech-canvas" aria-hidden="true"></canvas>', false);
        $response->assertSee('initNodes()', false);
        $response->assertSee('requestAnimationFrame(draw)', false);
        $response->assertSee('rgba(99, 102, 241,', false); // matching indigo node connection lines

        // Verify form fields and styling
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="remember"', false);
        $response->assertSee('MQMS', false);
        $response->assertSee('Back to Welcome Page', false);
    }

    /**
     * Test user authentication via login screen form.
     */
    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'operator@mqms.local',
            'password' => Hash::make('secret-password-123'),
            'status' => 'ACTV',
        ]);

        $response = $this->withoutMiddleware(VerifyCsrfToken::class)->post('/login', [
            'email' => 'operator@mqms.local',
            'password' => 'secret-password-123',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test login failure with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_password()
    {
        User::factory()->create([
            'email' => 'operator2@mqms.local',
            'password' => Hash::make('correct-password'),
            'status' => 'ACTV',
        ]);

        $response = $this->withoutMiddleware(VerifyCsrfToken::class)
            ->from('/login')
            ->post('/login', [
                'email' => 'operator2@mqms.local',
                'password' => 'wrong-password',
            ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
