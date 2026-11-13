<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\AccessCode;
use App\Models\RoleAccessCode;
use App\Models\MaterialQuantityRequest;

class MaterialQuantityRequestListTest extends TestCase
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

        $this->grantAccessCode($this->user, 'material_request:all:list');
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
        }

        $roleAccessCode = RoleAccessCode::where('role_id', $role->id)
            ->where('access_code_id', $accessCode->id)
            ->first();
        if (!$roleAccessCode) {
            $roleAccessCode = new RoleAccessCode();
            $roleAccessCode->role_id = $role->id;
            $roleAccessCode->access_code_id = $accessCode->id;
            $roleAccessCode->save();
        }

        $userRole = UserRole::where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->first();
        if (!$userRole) {
            $userRole = new UserRole();
            $userRole->user_id = $user->id;
            $userRole->role_id = $role->id;
            $userRole->save();
        }
    }

    public function test_it_renders_material_quantity_requests_page()
    {
        $response = $this->actingAs($this->user)->get('/material_quantity_requests');

        $response->assertStatus(200);
        $response->assertSee('item.component?.name ?? \'\'', false);
    }

    public function test_api_list_returns_records_with_null_component()
    {
        $mqr = new MaterialQuantityRequest();
        $mqr->project_id = 99999;
        $mqr->section_id = 99999;
        $mqr->contract_item_id = 99999;
        $mqr->component_id = 99999;
        $mqr->description = 'Test description';
        $mqr->status = 'PEND';
        $mqr->created_by = $this->user->id;
        $mqr->save();

        $response = $this->actingAs($this->user)->getJson('/api/material_quantity_request/list?page=1&limit=10');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
        ]);
        $this->assertCount(1, $response->json('data'));
        $this->assertNull($response->json('data.0.component'));
    }
}
