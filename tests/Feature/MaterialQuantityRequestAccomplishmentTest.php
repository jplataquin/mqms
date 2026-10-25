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
use App\Models\Unit;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\ComponentItem;
use App\Models\MaterialGroup;
use App\Models\MaterialItem;
use App\Models\MaterialQuantity;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialQuantityRequestItem;
use App\Models\Accomplishment;

class MaterialQuantityRequestAccomplishmentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;
    protected $section;
    protected $unit;
    protected $contractItem;
    protected $component;
    protected $componentItem;
    protected $materialGroup;
    protected $materialItem;
    protected $materialQuantity;
    protected $mqr;
    protected $mqrItem;

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

        $this->grantAccessCode($this->user, 'material_request:all:view');
        $this->grantAccessCode($this->user, 'material_request:own:view');

        $this->unit = new Unit();
        $this->unit->text = 'SQM';
        $this->unit->created_by = $this->user->id;
        $this->unit->save();

        $this->project = new Project();
        $this->project->name = 'Accomplishment Test Project';
        $this->project->status = 'ACTV';
        $this->project->created_by = $this->user->id;
        $this->project->save();

        $this->section = new Section();
        $this->section->project_id = $this->project->id;
        $this->section->name = 'Phase 1 Structural';
        $this->section->created_by = $this->user->id;
        $this->section->save();

        $this->contractItem = new ContractItem();
        $this->contractItem->section_id = $this->section->id;
        $this->contractItem->item_type = 'MAT';
        $this->contractItem->item_code = 'CI-401';
        $this->contractItem->description = 'Reinforced Concrete Slab';
        $this->contractItem->contract_quantity = 200.00;
        $this->contractItem->unit_id = $this->unit->id;
        $this->contractItem->contract_unit_price = 1500.00;
        $this->contractItem->created_by = $this->user->id;
        $this->contractItem->save();

        $this->component = new Component();
        $this->component->name = 'Ground Floor Slab';
        $this->component->contract_item_id = $this->contractItem->id;
        $this->component->section_id = $this->section->id;
        $this->component->quantity = 100;
        $this->component->unit_id = $this->unit->id;
        $this->component->use_count = 1;
        $this->component->status = 'APRV';
        $this->component->created_by = $this->user->id;
        $this->component->save();

        $this->componentItem = new ComponentItem();
        $this->componentItem->component_id = $this->component->id;
        $this->componentItem->name = 'Portland Cement Component';
        $this->componentItem->quantity = 50;
        $this->componentItem->unit_id = $this->unit->id;
        $this->componentItem->budget_price = 250.00;
        $this->componentItem->function_type_id = 1;
        $this->componentItem->function_variable = 1.0;
        $this->componentItem->created_by = $this->user->id;
        $this->componentItem->save();

        $this->materialGroup = new MaterialGroup();
        $this->materialGroup->name = 'Cement Products';
        $this->materialGroup->created_by = $this->user->id;
        $this->materialGroup->save();

        $this->materialItem = new MaterialItem();
        $this->materialItem->material_group_id = $this->materialGroup->id;
        $this->materialItem->name = 'Portland Type 1';
        $this->materialItem->brand = 'Holcim';
        $this->materialItem->specification_unit_packaging = '40kg bag';
        $this->materialItem->created_by = $this->user->id;
        $this->materialItem->save();

        $this->materialQuantity = new MaterialQuantity();
        $this->materialQuantity->component_item_id = $this->componentItem->id;
        $this->materialQuantity->material_item_id = $this->materialItem->id;
        $this->materialQuantity->quantity = 50;
        $this->materialQuantity->equivalent = 1.0;
        $this->materialQuantity->created_by = $this->user->id;
        $this->materialQuantity->save();

        $this->mqr = new MaterialQuantityRequest();
        $this->mqr->project_id = $this->project->id;
        $this->mqr->section_id = $this->section->id;
        $this->mqr->contract_item_id = $this->contractItem->id;
        $this->mqr->component_id = $this->component->id;
        $this->mqr->status = 'PEND';
        $this->mqr->description = 'Slab materials';
        $this->mqr->date_needed = '2026-11-01';
        $this->mqr->created_by = $this->user->id;
        $this->mqr->save();

        $this->mqrItem = new MaterialQuantityRequestItem();
        $this->mqrItem->material_quantity_request_id = $this->mqr->id;
        $this->mqrItem->component_item_id = $this->componentItem->id;
        $this->mqrItem->material_item_id = $this->materialItem->id;
        $this->mqrItem->requested_quantity = 20;
        $this->mqrItem->status = 'PEND';
        $this->mqrItem->save();
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
    public function it_calculates_contract_item_actual_accomplishment_correctly()
    {
        // Initially no accomplishments
        $this->assertEquals(0.0, $this->contractItem->latest_actual_accomplishment_quantity);
        $this->assertEquals(0.0, $this->contractItem->latest_actual_accomplishment_percentage);
        $this->assertEquals('0.00 SQM (0.00%)', $this->contractItem->latest_actual_accomplishment_text);

        // Add an actual accomplishment of 50 SQM out of 200 SQM (25%)
        $acc1 = new Accomplishment();
        $acc1->contract_item_id = $this->contractItem->id;
        $acc1->type = 'ACTUAL';
        $acc1->entry_data = '2026-10-15';
        $acc1->quantity = 50.00;
        $acc1->remarks = 'First pour';
        $acc1->created_by = $this->user->id;
        $acc1->save();

        $this->contractItem->refresh();
        $this->assertEquals(50.0, $this->contractItem->latest_actual_accomplishment_quantity);
        $this->assertEquals(25.0, $this->contractItem->latest_actual_accomplishment_percentage);
        $this->assertEquals('50.00 SQM (25.00%)', $this->contractItem->latest_actual_accomplishment_text);

        // Add a later actual accomplishment of 150 SQM out of 200 SQM (75%)
        $acc2 = new Accomplishment();
        $acc2->contract_item_id = $this->contractItem->id;
        $acc2->type = 'ACTUAL';
        $acc2->entry_data = '2026-10-20';
        $acc2->quantity = 150.00;
        $acc2->remarks = 'Second pour';
        $acc2->created_by = $this->user->id;
        $acc2->save();

        $this->contractItem->refresh();
        $this->assertEquals(150.0, $this->contractItem->latest_actual_accomplishment_quantity);
        $this->assertEquals(75.0, $this->contractItem->latest_actual_accomplishment_percentage);
        $this->assertEquals('150.00 SQM (75.00%)', $this->contractItem->latest_actual_accomplishment_text);
    }

    /** @test */
    public function it_displays_latest_actual_accomplishment_in_mqr_display_view()
    {
        $acc = new Accomplishment();
        $acc->contract_item_id = $this->contractItem->id;
        $acc->type = 'ACTUAL';
        $acc->entry_data = '2026-10-22';
        $acc->quantity = 60.00;
        $acc->remarks = 'Recent accomplishment';
        $acc->created_by = $this->user->id;
        $acc->save();

        $response = $this->actingAs($this->user)->get('/material_quantity_request/' . $this->mqr->id);

        $response->assertStatus(200);
        $response->assertSee('Actual Accomplishment');
        $response->assertSee('60.00 SQM (30.00%)');
    }

    /** @test */
    public function it_displays_latest_actual_accomplishment_in_mqr_print_view()
    {
        $acc = new Accomplishment();
        $acc->contract_item_id = $this->contractItem->id;
        $acc->type = 'ACTUAL';
        $acc->entry_data = '2026-10-22';
        $acc->quantity = 80.00;
        $acc->remarks = 'Print accomplishment test';
        $acc->created_by = $this->user->id;
        $acc->save();

        $response = $this->actingAs($this->user)->get('/material_quantity_request/print/' . $this->mqr->id);

        $response->assertStatus(200);
        $response->assertSee('Actual Accomplishment');
        $response->assertSee('80.00 SQM (40.00%)');
    }

    /** @test */
    public function it_displays_contract_item_and_latest_actual_accomplishment_in_mqr_review_view()
    {
        $acc = new Accomplishment();
        $acc->contract_item_id = $this->contractItem->id;
        $acc->type = 'ACTUAL';
        $acc->entry_data = '2026-10-22';
        $acc->quantity = 100.00;
        $acc->remarks = 'Halfway accomplished';
        $acc->created_by = $this->user->id;
        $acc->save();

        $response = $this->actingAs($this->user)->get('/review/material_quantity_request/' . $this->mqr->id);

        $response->assertStatus(200);
        $response->assertSee('Contract Item');
        $response->assertSee('CI-401');
        $response->assertSee('Reinforced Concrete Slab');
        $response->assertSee('Actual Accomplishment');
        $response->assertSee('100.00 SQM (50.00%)');
    }
}
