<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\AccessCode;
use App\Models\RoleAccessCode;
use App\Models\Project;

class MaterialQuantityRequestSelectTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project1;
    protected $project2;

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

        $this->grantAccessCode($this->user, 'material_request:own:create');

        $this->project1 = new Project();
        $this->project1->name = 'Metro Railway Project';
        $this->project1->status = 'ACTV';
        $this->project1->created_by = $this->user->id;
        $this->project1->save();

        $this->project2 = new Project();
        $this->project2->name = 'Highway Extension Project';
        $this->project2->status = 'ACTV';
        $this->project2->created_by = $this->user->id;
        $this->project2->save();
    }

    protected function grantAccessCode($user, $codeString)
    {
        $accessCode = AccessCode::where('code', $codeString)->first();
        if (!$accessCode) {
            $accessCode = new AccessCode();
            $accessCode->code = $codeString;
            $accessCode->description = 'Test code';
            $accessCode->save();
        }

        $role = Role::where('name', 'Role ' . $codeString)->first();
        if (!$role) {
            $role = new Role();
            $role->name = 'Role ' . $codeString;
            $role->description = 'Test role';
            $role->save();

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

    /** @test */
    public function it_denies_access_without_permission()
    {
        $unauthorizedUser = User::factory()->create(['status' => 'ACTV']);

        $response = $this->actingAs($unauthorizedUser)->get('/material_quantity_request/select/create');
        $response->assertSee('Access Denied');
    }

    /** @test */
    public function it_renders_searchable_inputs_and_datalists_on_select_create_page()
    {
        $response = $this->actingAs($this->user)->get('/material_quantity_request/select/create');

        $response->assertStatus(200);

        // Verify searchable inputs exist
        $response->assertSee('id="projectInput"', false);
        $response->assertSee('id="sectionInput"', false);
        $response->assertSee('id="contractItemInput"', false);
        $response->assertSee('id="componentInput"', false);

        // Verify datalists exist
        $response->assertSee('id="projectList"', false);
        $response->assertSee('id="sectionList"', false);
        $response->assertSee('id="contractItemList"', false);
        $response->assertSee('id="componentList"', false);

        // Verify clear buttons exist
        $response->assertSee('id="clearProjectBtn"', false);
        $response->assertSee('id="clearSectionBtn"', false);
        $response->assertSee('id="clearContractItemBtn"', false);
        $response->assertSee('id="clearComponentBtn"', false);

        // Verify hidden value inputs exist
        $response->assertSee('id="projectSelect"', false);
        $response->assertSee('id="sectionSelect"', false);
        $response->assertSee('id="contractItemSelect"', false);
        $response->assertSee('id="componentSelect"', false);

        // Verify active projects are in projectList datalist
        $response->assertSee('value="Metro Railway Project"', false);
        $response->assertSee('value="Highway Extension Project"', false);
    }
}
