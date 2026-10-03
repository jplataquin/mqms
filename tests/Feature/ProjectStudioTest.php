<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\ComponentItem;
use App\Models\Unit;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\AccessCode;
use App\Models\RoleAccessCode;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectStudioTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;
    protected $section;
    protected $contractItem;
    protected $component;
    protected $componentItem;

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

        // Setup access codes for project, section, contract item, and component views
        $this->grantAccessCodes($this->user, [
            'project:all:view',
            'section:all:view',
            'contract_item:all:view',
            'component:all:view',
        ]);

        $unit = new Unit();
        $unit->text = 'Pieces';
        $unit->created_by = $this->user->id;
        $unit->save();

        $this->project = new Project();
        $this->project->name = 'Metropolis Tower';
        $this->project->status = 'ACTV';
        $this->project->created_by = $this->user->id;
        $this->project->save();

        $this->section = new Section();
        $this->section->project_id = $this->project->id;
        $this->section->name = 'Phase 1 - Substructure';
        $this->section->created_by = $this->user->id;
        $this->section->save();

        $this->contractItem = new ContractItem();
        $this->contractItem->section_id = $this->section->id;
        $this->contractItem->item_code = 'CI-101';
        $this->contractItem->description = 'Structural Excavation';
        $this->contractItem->item_type = 'MAT';
        $this->contractItem->unit_id = $unit->id;
        $this->contractItem->contract_quantity = 100;
        $this->contractItem->contract_unit_price = 500;
        $this->contractItem->created_by = $this->user->id;
        $this->contractItem->save();

        $this->component = new Component();
        $this->component->contract_item_id = $this->contractItem->id;
        $this->component->section_id = $this->section->id;
        $this->component->name = 'Piles Foundation';
        $this->component->unit_id = $unit->id;
        $this->component->quantity = 50;
        $this->component->status = 'ACTV';
        $this->component->created_by = $this->user->id;
        $this->component->save();

        $this->componentItem = new ComponentItem();
        $this->componentItem->component_id = $this->component->id;
        $this->componentItem->name = 'Reinforcing Rebar #6';
        $this->componentItem->unit_id = $unit->id;
        $this->componentItem->quantity = 100;
        $this->componentItem->budget_price = 150;
        $this->componentItem->function_type_id = 3;
        $this->componentItem->function_variable = 10;
        $this->componentItem->created_by = $this->user->id;
        $this->componentItem->save();
    }

    /** @test */
    public function it_can_render_the_project_studio_index_page()
    {
        $response = $this->actingAs($this->user)->get('/project/studio/' . $this->project->id);

        $response->assertStatus(200);
        $response->assertSee('Project Studio');
        $response->assertSee('Metropolis Tower');
        $response->assertSee('update-node', false);
        $response->assertSee('tree.rename_node', false);
    }

    /** @test */
    public function it_can_fetch_studio_root_node_and_sections()
    {
        $response = $this->actingAs($this->user)->getJson('/api/project/studio/node?project_id=' . $this->project->id);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertNotEmpty($data);
        $this->assertEquals('project_' . $this->project->id, $data[0]['id']);
        $this->assertEquals('Metropolis Tower', $data[0]['text']);
        $this->assertEquals('section_' . $this->section->id, $data[0]['children'][0]['id']);
    }

    /** @test */
    public function it_can_fetch_studio_lazy_children()
    {
        // Fetch children of section
        $response = $this->actingAs($this->user)->getJson('/api/project/studio/node/children?type=section&id=' . $this->section->id);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertNotEmpty($data);
        $this->assertEquals('contract_item_' . $this->contractItem->id, $data[0]['id']);

        // Fetch children of contract item
        $response = $this->actingAs($this->user)->getJson('/api/project/studio/node/children?type=contract_item&id=' . $this->contractItem->id);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertNotEmpty($data);
        $this->assertEquals('component_' . $this->component->id, $data[0]['id']);

        // Fetch children of component
        $response = $this->actingAs($this->user)->getJson('/api/project/studio/node/children?type=component&id=' . $this->component->id);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertNotEmpty($data);
        $this->assertEquals('component_item_' . $this->componentItem->id, $data[0]['id']);
    }

    /** @test */
    public function it_has_update_node_postmessage_in_display_views()
    {
        // Project display view
        $response = $this->actingAs($this->user)->get('/project/' . $this->project->id);
        $response->assertStatus(200);
        $response->assertSee("action: 'update-node'", false);
        $response->assertSee("type: 'project'", false);

        // Section display view
        $response = $this->actingAs($this->user)->get('/project/section/' . $this->section->id);
        $response->assertStatus(200);
        $response->assertSee("action: 'update-node'", false);
        $response->assertSee("type: 'section'", false);

        // Contract Item display view
        $response = $this->actingAs($this->user)->get('/project/section/contract_item/' . $this->contractItem->id);
        $response->assertStatus(200);
        $response->assertSee("action: 'update-node'", false);
        $response->assertSee("type: 'contract_item'", false);

        // Component display view
        $response = $this->actingAs($this->user)->get('/project/section/contract_item/component/' . $this->component->id);
        $response->assertStatus(200);
        $response->assertSee("action: 'update-node'", false);
        $response->assertSee("type: 'component'", false);
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
}
