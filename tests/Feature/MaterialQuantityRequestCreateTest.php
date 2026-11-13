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
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\ComponentItem;
use App\Models\MaterialItem;
use App\Models\MaterialGroup;
use App\Models\MaterialQuantity;
use App\Models\Unit;

class MaterialQuantityRequestCreateTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;
    protected $section;
    protected $contractItem;
    protected $component;
    protected $componentItem;
    protected $materialItem;
    protected $unit;

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
        $this->grantAccessCode($this->user, 'material_request:all:create');
        $this->grantAccessCode($this->user, 'material_request:own:view');
        $this->grantAccessCode($this->user, 'material_request:all:view');

        $this->unit = new Unit();
        $this->unit->text = 'pcs';
        $this->unit->created_by = $this->user->id;
        $this->unit->save();

        $this->project = new Project();
        $this->project->name = 'Test Project';
        $this->project->status = 'ACTV';
        $this->project->created_by = $this->user->id;
        $this->project->save();

        $this->section = new Section();
        $this->section->name = 'Test Section';
        $this->section->project_id = $this->project->id;
        $this->section->created_by = $this->user->id;
        $this->section->save();

        $this->contractItem = new ContractItem();
        $this->contractItem->section_id = $this->section->id;
        $this->contractItem->item_type = 'MAT';
        $this->contractItem->item_code = 'ITEM-01';
        $this->contractItem->description = 'Test Contract Item';
        $this->contractItem->contract_quantity = 200.00;
        $this->contractItem->unit_id = $this->unit->id;
        $this->contractItem->contract_unit_price = 1500.00;
        $this->contractItem->created_by = $this->user->id;
        $this->contractItem->save();

        $this->component = new Component();
        $this->component->name = 'Test Component';
        $this->component->contract_item_id = $this->contractItem->id;
        $this->component->section_id = $this->section->id;
        $this->component->quantity = 100;
        $this->component->unit_id = $this->unit->id;
        $this->component->use_count = 1;
        $this->component->status = 'APRV';
        $this->component->created_by = $this->user->id;
        $this->component->save();

        $this->componentItem = new ComponentItem();
        $this->componentItem->name = 'Component Item 1';
        $this->componentItem->component_id = $this->component->id;
        $this->componentItem->unit_id = $this->unit->id;
        $this->componentItem->quantity = 1000;
        $this->componentItem->budget_price = 250.00;
        $this->componentItem->function_type_id = 1;
        $this->componentItem->function_variable = 1.0;
        $this->componentItem->created_by = $this->user->id;
        $this->componentItem->save();

        $group = new MaterialGroup();
        $group->name = 'Cement Group';
        $group->created_by = $this->user->id;
        $group->save();

        $this->materialItem = new MaterialItem();
        $this->materialItem->material_group_id = $group->id;
        $this->materialItem->name = 'Portland Cement';
        $this->materialItem->specification_unit_packaging = '40kg Bag';
        $this->materialItem->brand = 'Holcim';
        $this->materialItem->created_by = $this->user->id;
        $this->materialItem->save();

        $mq = new MaterialQuantity();
        $mq->component_item_id = $this->componentItem->id;
        $mq->material_item_id = $this->materialItem->id;
        $mq->quantity = 500;
        $mq->equivalent = 1;
        $mq->created_by = $this->user->id;
        $mq->save();
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

            $roleAccess = new RoleAccessCode();
            $roleAccess->role_id = $role->id;
            $roleAccess->access_code_id = $accessCode->id;
            $roleAccess->save();
        }

        $userRole = UserRole::where('user_id', $user->id)->where('role_id', $role->id)->first();
        if (!$userRole) {
            $userRole = new UserRole();
            $userRole->user_id = $user->id;
            $userRole->role_id = $role->id;
            $userRole->save();
        }
    }

    public function test_can_create_material_quantity_request_successfully()
    {
        $response = $this->actingAs($this->user)->postJson('/api/material_quantity_request/create', [
            'project_id' => $this->project->id,
            'section_id' => $this->section->id,
            'contract_item_id' => $this->contractItem->id,
            'component_id' => $this->component->id,
            'description' => 'Test MQR Request',
            'date_needed' => 'Nov 13, 2026',
            'items' => json_encode([
                [
                    'component_item_id' => $this->componentItem->id,
                    'material_item_id' => $this->materialItem->id,
                    'requested_quantity' => 10,
                ]
            ]),
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('status'));
        $this->assertNotEmpty($response->json('data.id'));
    }

    public function test_fails_when_items_are_empty_array()
    {
        $response = $this->actingAs($this->user)->postJson('/api/material_quantity_request/create', [
            'project_id' => $this->project->id,
            'section_id' => $this->section->id,
            'contract_item_id' => $this->contractItem->id,
            'component_id' => $this->component->id,
            'description' => 'Test MQR Request',
            'date_needed' => 'Nov 13, 2026',
            'items' => json_encode([]),
        ]);

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('status'));
    }

    public function test_fails_when_items_have_empty_values()
    {
        $response = $this->actingAs($this->user)->postJson('/api/material_quantity_request/create', [
            'project_id' => $this->project->id,
            'section_id' => $this->section->id,
            'contract_item_id' => $this->contractItem->id,
            'component_id' => $this->component->id,
            'description' => 'Test MQR Request',
            'date_needed' => 'Nov 13, 2026',
            'items' => json_encode([
                [
                    'component_item_id' => null,
                    'material_item_id' => null,
                    'requested_quantity' => '',
                ]
            ]),
        ]);

        $response->assertStatus(200);
        $this->assertEquals(-2, $response->json('status'));
    }

    public function test_fails_when_project_is_not_active()
    {
        $this->project->status = 'ARCH';
        $this->project->save();

        $response = $this->actingAs($this->user)->postJson('/api/material_quantity_request/create', [
            'project_id' => $this->project->id,
            'section_id' => $this->section->id,
            'contract_item_id' => $this->contractItem->id,
            'component_id' => $this->component->id,
            'description' => 'Test MQR Request',
            'date_needed' => 'Nov 13, 2026',
            'items' => json_encode([
                [
                    'component_item_id' => $this->componentItem->id,
                    'material_item_id' => $this->materialItem->id,
                    'requested_quantity' => 10,
                ]
            ]),
        ]);

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('status'));
    }

    public function test_can_create_with_items_as_array_and_iso_date()
    {
        $response = $this->actingAs($this->user)->postJson('/api/material_quantity_request/create', [
            'project_id' => $this->project->id,
            'section_id' => $this->section->id,
            'contract_item_id' => $this->contractItem->id,
            'component_id' => $this->component->id,
            'description' => 'Test MQR Request with Array and ISO Date',
            'date_needed' => '2026-11-20',
            'items' => [
                [
                    'component_item_id' => $this->componentItem->id,
                    'material_item_id' => $this->materialItem->id,
                    'requested_quantity' => 15,
                ]
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('status'));
        $this->assertNotEmpty($response->json('data.id'));
    }

    public function test_fails_with_invalid_date_format()
    {
        $response = $this->actingAs($this->user)->postJson('/api/material_quantity_request/create', [
            'project_id' => $this->project->id,
            'section_id' => $this->section->id,
            'contract_item_id' => $this->contractItem->id,
            'component_id' => $this->component->id,
            'description' => 'Test MQR Request',
            'date_needed' => 'invalid-date-string',
            'items' => json_encode([
                [
                    'component_item_id' => $this->componentItem->id,
                    'material_item_id' => $this->materialItem->id,
                    'requested_quantity' => 10,
                ]
            ]),
        ]);

        $response->assertStatus(200);
        $this->assertEquals(-2, $response->json('status'));
    }
}
