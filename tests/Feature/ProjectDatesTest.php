<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\AccessCode;
use App\Models\RoleAccessCode;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectDatesTest extends TestCase
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
            'status' => 'ACTV'
        ]);

        $this->grantAccessCodes($this->user, [
            'project:own:create',
            'project:all:create',
            'project:own:view',
            'project:all:view',
            'project:own:update',
            'project:all:update',
        ]);
    }

    protected function grantAccessCodes($user, array $codes)
    {
        $role = new Role();
        $role->name = 'Test Role ' . uniqid();
        $role->description = 'Test Description';
        $role->save();

        foreach ($codes as $codeString) {
            $accessCode = AccessCode::where('code', $codeString)->first();
            if (!$accessCode) {
                $accessCode = new AccessCode();
                $accessCode->code = $codeString;
                $accessCode->description = 'Test access code';
                $accessCode->save();
            }

            $roleAccessCode = new RoleAccessCode();
            $roleAccessCode->role_id = $role->id;
            $roleAccessCode->access_code_id = $accessCode->id;
            $roleAccessCode->save();
        }

        $userRole = new UserRole();
        $userRole->user_id = $user->id;
        $userRole->role_id = $role->id;
        $userRole->save();
    }

    public function test_can_create_project_with_start_date_and_calendar_days(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/project/create', [
            'name' => 'Project Alpha',
            'status' => 'ACTV',
            'start_date' => '2026-11-01',
            'calendar_days' => 180,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $projectId = $response->json('data.id');
        $this->assertNotNull($projectId);

        $project = Project::find($projectId);
        $this->assertNotNull($project);
        $this->assertEquals('Project Alpha', $project->name);
        $this->assertEquals('2026-11-01', $project->start_date->format('Y-m-d'));
        $this->assertEquals(180, $project->calendar_days);
    }

    public function test_can_create_project_with_null_dates(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/project/create', [
            'name' => 'Project Beta',
            'status' => 'ACTV',
            'start_date' => null,
            'calendar_days' => null,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $projectId = $response->json('data.id');
        $project = Project::find($projectId);
        $this->assertNull($project->start_date);
        $this->assertNull($project->calendar_days);
    }

    public function test_validation_fails_for_invalid_start_date_and_calendar_days(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/project/create', [
            'name' => 'Project Gamma',
            'status' => 'ACTV',
            'start_date' => 'invalid-date',
            'calendar_days' => 'not-an-integer',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => -2]);
        $response->assertJsonStructure(['data' => ['start_date', 'calendar_days']]);
    }

    public function test_can_update_project_start_date_and_calendar_days(): void
    {
        $project = new Project();
        $project->name = 'Project Delta';
        $project->status = 'ACTV';
        $project->created_by = $this->user->id;
        $project->save();

        $response = $this->actingAs($this->user)->postJson('/api/project/update', [
            'id' => $project->id,
            'name' => 'Project Delta Updated',
            'status' => 'ACTV',
            'start_date' => '2027-01-15',
            'calendar_days' => 90,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $project->refresh();
        $this->assertEquals('Project Delta Updated', $project->name);
        $this->assertEquals('2027-01-15', $project->start_date->format('Y-m-d'));
        $this->assertEquals(90, $project->calendar_days);
    }

    public function test_project_display_view_shows_start_date_and_calendar_days(): void
    {
        $project = new Project();
        $project->name = 'Project Epsilon';
        $project->status = 'ACTV';
        $project->start_date = '2026-12-01';
        $project->calendar_days = 120;
        $project->created_by = $this->user->id;
        $project->save();

        $response = $this->actingAs($this->user)->get('/project/' . $project->id);
        $response->assertStatus(200);
        $response->assertSee('Start Date');
        $response->assertSee('Calendar Days');
        $response->assertSee('2026-12-01');
        $response->assertSee('120');
    }
}
