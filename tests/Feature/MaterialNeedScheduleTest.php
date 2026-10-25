<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialNeedScheduleTest extends TestCase
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
    public function it_can_render_the_material_need_schedule_page()
    {
        $response = $this->actingAs($this->user)->get('/material_need_schedule');

        $response->assertStatus(200);
        $response->assertSee('Mat. Need Schedule');
        $response->assertSee('href="/material_need_schedule"', false);
        $response->assertDontSee('Material Objectives');
        $response->assertSee('/api/material_need_schedule');
    }

    /** @test */
    public function it_redirects_old_objectives_material_route_to_material_need_schedule()
    {
        $response = $this->actingAs($this->user)->get('/objectives/material');

        $response->assertRedirect('/material_need_schedule');
    }

    /** @test */
    public function it_can_fetch_material_need_schedule_data_via_new_api_endpoint()
    {
        $project = new Project();
        $project->name = 'Test Project Alpha';
        $project->status = 'ACTV';
        $project->created_by = $this->user->id;
        $project->updated_by = $this->user->id;
        $project->save();

        $from = Carbon::now()->format('M d, Y');
        $to = Carbon::now()->addDays(10)->format('M d, Y');

        $response = $this->actingAs($this->user)->getJson('/api/material_need_schedule?project_id=' . $project->id . '&from=' . urlencode($from) . '&to=' . urlencode($to));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
        ]);
    }

    /** @test */
    public function it_supports_backward_compatible_api_objectives_material_endpoint()
    {
        $from = Carbon::now()->format('M d, Y');
        $to = Carbon::now()->addDays(10)->format('M d, Y');

        $response = $this->actingAs($this->user)->getJson('/api/objectives/material?from=' . urlencode($from) . '&to=' . urlencode($to));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
        ]);
    }
}
