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
use App\Models\MaterialQuantity;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialQuantityRequestItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\PaymentTerm;
use App\Models\Unit;

class MaterialQuantityRequestCloseTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;
    protected $section;
    protected $contractItem;
    protected $component;
    protected $componentItem;
    protected $unit;
    protected $materialItem1;
    protected $materialItem2;
    protected $supplier;
    protected $paymentTerm;

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

        $this->grantAccessCode($this->user, 'material_request:all:close');
        $this->grantAccessCode($this->user, 'material_request:all:update');
        $this->grantAccessCode($this->user, 'material_request:all:view');
        $this->grantAccessCode($this->user, 'purchase_order:own:create');
        $this->grantAccessCode($this->user, 'purchase_order:all:create');
        $this->grantAccessCode($this->user, 'material_canvass:own:create');
        $this->grantAccessCode($this->user, 'material_canvass:all:create');

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

        $materialGroup = new \App\Models\MaterialGroup();
        $materialGroup->name = 'Masonry';
        $materialGroup->created_by = $this->user->id;
        $materialGroup->save();

        $this->materialItem1 = new MaterialItem();
        $this->materialItem1->material_group_id = $materialGroup->id;
        $this->materialItem1->name = 'Cement';
        $this->materialItem1->specification_unit_packaging = '40kg Bag';
        $this->materialItem1->brand = 'Holcim';
        $this->materialItem1->created_by = $this->user->id;
        $this->materialItem1->save();

        $this->materialItem2 = new MaterialItem();
        $this->materialItem2->material_group_id = $materialGroup->id;
        $this->materialItem2->name = 'Sand';
        $this->materialItem2->specification_unit_packaging = 'cu.m';
        $this->materialItem2->brand = 'Local';
        $this->materialItem2->created_by = $this->user->id;
        $this->materialItem2->save();

        $mq1 = new MaterialQuantity();
        $mq1->component_item_id = $this->componentItem->id;
        $mq1->material_item_id = $this->materialItem1->id;
        $mq1->quantity = 500;
        $mq1->equivalent = 1;
        $mq1->created_by = $this->user->id;
        $mq1->save();

        $mq2 = new MaterialQuantity();
        $mq2->component_item_id = $this->componentItem->id;
        $mq2->material_item_id = $this->materialItem2->id;
        $mq2->quantity = 500;
        $mq2->equivalent = 1;
        $mq2->created_by = $this->user->id;
        $mq2->save();

        $this->supplier = new Supplier();
        $this->supplier->name = 'Supplier Inc';
        $this->supplier->address = '123 Test St';
        $this->supplier->primary_contact_no = '09123456789';
        $this->supplier->created_by = $this->user->id;
        $this->supplier->save();

        $this->paymentTerm = new PaymentTerm();
        $this->paymentTerm->text = '30 Days';
        $this->paymentTerm->created_by = $this->user->id;
        $this->paymentTerm->save();
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

    public function test_cannot_close_mqr_if_not_approved()
    {
        $mqr = new MaterialQuantityRequest();
        $mqr->project_id = $this->project->id;
        $mqr->section_id = $this->section->id;
        $mqr->contract_item_id = $this->contractItem->id;
        $mqr->component_id = $this->component->id;
        $mqr->description = 'Test MQR description';
        $mqr->status = 'PEND';
        $mqr->created_by = $this->user->id;
        $mqr->save();

        $response = $this->actingAs($this->user)->postJson('/api/material_quantity_request/close', [
            'id' => $mqr->id,
        ]);

        $response->assertJson([
            'status' => 0,
            'message' => 'Material Request is not in Approved status',
        ]);
    }

    public function test_close_mqr_with_no_pos_sets_quantity_to_zero_and_preserves_original_quantity()
    {
        $mqr = new MaterialQuantityRequest();
        $mqr->project_id = $this->project->id;
        $mqr->section_id = $this->section->id;
        $mqr->contract_item_id = $this->contractItem->id;
        $mqr->component_id = $this->component->id;
        $mqr->description = 'Test MQR description';
        $mqr->status = 'APRV';
        $mqr->created_by = $this->user->id;
        $mqr->save();

        $item = new MaterialQuantityRequestItem();
        $item->material_quantity_request_id = $mqr->id;
        $item->component_item_id = $this->componentItem->id;
        $item->material_item_id = $this->materialItem1->id;
        $item->requested_quantity = 50;
        $item->status = 'APRV';
        $item->save();

        $response = $this->actingAs($this->user)->postJson('/api/material_quantity_request/close', [
            'id' => $mqr->id,
        ]);

        $response->assertJson([
            'status' => 1,
            'message' => 'Material Request closed successfully',
        ]);

        $mqr->refresh();
        $this->assertEquals('CLSD', $mqr->status);

        $item->refresh();
        $this->assertEquals('CLSD', $item->status);
        $this->assertEquals(0, (float)$item->requested_quantity);
        $this->assertEquals(50, (float)$item->original_quantity);
    }

    public function test_close_mqr_sums_both_aprv_and_pend_pos()
    {
        $mqr = new MaterialQuantityRequest();
        $mqr->project_id = $this->project->id;
        $mqr->section_id = $this->section->id;
        $mqr->contract_item_id = $this->contractItem->id;
        $mqr->component_id = $this->component->id;
        $mqr->description = 'Test MQR description';
        $mqr->status = 'APRV';
        $mqr->created_by = $this->user->id;
        $mqr->save();

        $item1 = new MaterialQuantityRequestItem();
        $item1->material_quantity_request_id = $mqr->id;
        $item1->component_item_id = $this->componentItem->id;
        $item1->material_item_id = $this->materialItem1->id;
        $item1->requested_quantity = 100;
        $item1->status = 'APRV';
        $item1->save();

        $po1 = new PurchaseOrder();
        $po1->material_quantity_request_id = $mqr->id;
        $po1->project_id = $this->project->id;
        $po1->section_id = $this->section->id;
        $po1->contract_item_id = $this->contractItem->id;
        $po1->component_id = $this->component->id;
        $po1->supplier_id = $this->supplier->id;
        $po1->payment_term_id = $this->paymentTerm->id;
        $po1->status = 'APRV';
        $po1->created_by = $this->user->id;
        $po1->save();

        $mc = new \App\Models\MaterialCanvass();
        $mc->material_quantity_request_id = $mqr->id;
        $mc->material_quantity_request_item_id = $item1->id;
        $mc->supplier_id = $this->supplier->id;
        $mc->payment_term_id = $this->paymentTerm->id;
        $mc->price = 10.00;
        $mc->status = 'APRV';
        $mc->created_by = $this->user->id;
        $mc->save();

        // PO item 1: APRV with 30 qty
        $poi1 = new PurchaseOrderItem();
        $poi1->purchase_order_id = $po1->id;
        $poi1->material_quantity_request_item_id = $item1->id;
        $poi1->material_canvass_id = $mc->id;
        $poi1->component_item_id = $this->componentItem->id;
        $poi1->material_item_id = $this->materialItem1->id;
        $poi1->quantity = 30;
        $poi1->price = 10.00;
        $poi1->status = 'APRV';
        $poi1->save();

        // PO item 2: PEND with 20 qty (should be included!)
        $poi2 = new PurchaseOrderItem();
        $poi2->purchase_order_id = $po1->id;
        $poi2->material_quantity_request_item_id = $item1->id;
        $poi2->material_canvass_id = $mc->id;
        $poi2->component_item_id = $this->componentItem->id;
        $poi2->material_item_id = $this->materialItem1->id;
        $poi2->quantity = 20;
        $poi2->price = 10.00;
        $poi2->status = 'PEND';
        $poi2->save();

        // PO item 3: VOID with 15 qty (should NOT be included!)
        $poi3 = new PurchaseOrderItem();
        $poi3->purchase_order_id = $po1->id;
        $poi3->material_quantity_request_item_id = $item1->id;
        $poi3->material_canvass_id = $mc->id;
        $poi3->component_item_id = $this->componentItem->id;
        $poi3->material_item_id = $this->materialItem1->id;
        $poi3->quantity = 15;
        $poi3->price = 10.00;
        $poi3->status = 'VOID';
        $poi3->save();

        $response = $this->actingAs($this->user)->postJson('/api/material_quantity_request/close', [
            'id' => $mqr->id,
        ]);

        $response->assertJson([
            'status' => 1,
            'message' => 'Material Request closed successfully',
        ]);

        $mqr->refresh();
        $this->assertEquals('CLSD', $mqr->status);

        $item1->refresh();
        $this->assertEquals(50, (float)$item1->requested_quantity); // 30 (APRV) + 20 (PEND)
        $this->assertEquals(100, (float)$item1->original_quantity);
        $this->assertEquals('CLSD', $item1->status);
    }

    public function test_cannot_create_po_for_closed_mqr()
    {
        $mqr = new MaterialQuantityRequest();
        $mqr->project_id = $this->project->id;
        $mqr->section_id = $this->section->id;
        $mqr->contract_item_id = $this->contractItem->id;
        $mqr->component_id = $this->component->id;
        $mqr->description = 'Test MQR description';
        $mqr->status = 'CLSD';
        $mqr->created_by = $this->user->id;
        $mqr->save();

        $response = $this->actingAs($this->user)->postJson('/api/purchase_order/create', [
            'material_quantity_request_id' => $mqr->id,
            'supplier_id' => $this->supplier->id,
            'payment_term_id' => $this->paymentTerm->id,
            'items' => json_encode([
                [
                    'material_quantity_request_item_id' => 1,
                    'component_item_id' => $this->componentItem->id,
                    'material_item_id' => $this->materialItem1->id,
                    'order_quantity' => 10,
                    'price' => '10.00',
                ]
            ]),
            'extras' => json_encode([]),
        ]);

        $response->assertJson([
            'status' => 0,
            'message' => 'Material Quantity Request is not approved or is closed',
        ]);
    }

    public function test_cannot_create_material_canvass_for_closed_mqr()
    {
        $mqr = new MaterialQuantityRequest();
        $mqr->project_id = $this->project->id;
        $mqr->section_id = $this->section->id;
        $mqr->contract_item_id = $this->contractItem->id;
        $mqr->component_id = $this->component->id;
        $mqr->description = 'Test MQR description';
        $mqr->status = 'CLSD';
        $mqr->created_by = $this->user->id;
        $mqr->save();

        $response = $this->actingAs($this->user)->postJson('/api/material_canvass/create', [
            'material_quantity_request_id' => $mqr->id,
            'canvassItems' => json_encode([]),
        ]);

        $response->assertJson([
            'status' => 0,
            'message' => 'Material Quantity Request has not yet been approved',
        ]);
    }

    public function test_budget_calculation_considers_closed_mqr_as_consumed()
    {
        // Budget quantity is 500 for materialItem1 on componentItem (use_count = 1, so total budget is 500)
        // Create an MQR that is CLSD with requested_quantity = 400
        $closedMqr = new MaterialQuantityRequest();
        $closedMqr->project_id = $this->project->id;
        $closedMqr->section_id = $this->section->id;
        $closedMqr->contract_item_id = $this->contractItem->id;
        $closedMqr->component_id = $this->component->id;
        $closedMqr->description = 'Closed MQR';
        $closedMqr->status = 'CLSD';
        $closedMqr->created_by = $this->user->id;
        $closedMqr->save();

        $closedItem = new MaterialQuantityRequestItem();
        $closedItem->material_quantity_request_id = $closedMqr->id;
        $closedItem->component_item_id = $this->componentItem->id;
        $closedItem->material_item_id = $this->materialItem1->id;
        $closedItem->requested_quantity = 400;
        $closedItem->original_quantity = 450;
        $closedItem->status = 'CLSD';
        $closedItem->save();

        // Level 1 budget diagnostic check
        $diagnostic = new \App\Protocols\LevelOneBudgetDiagnostic();
        $result = $diagnostic->run([
            'project_id' => $this->project->id,
            'component_id' => $this->component->id,
            'include_closed' => true,
        ]);

        $this->assertEquals(0, $result['violations_count']);
        $this->assertTrue($result['passed']);

        $this->grantAccessCode($this->user, 'material_request:own:create');

        // Total component item quantity is 1000, but let's test requesting more than remaining:
        // Say we have closedItem with 900 requested_quantity.
        $closedItem->requested_quantity = 900;
        $closedItem->save();

        // Requesting 150 (900 + 150 = 1050 > 1000) should fail with out of budget
        $response = $this->actingAs($this->user)->postJson('/api/material_quantity_request/create', [
            'project_id' => $this->project->id,
            'section_id' => $this->section->id,
            'contract_item_id' => $this->contractItem->id,
            'component_id' => $this->component->id,
            'description' => 'New MQR Test',
            'date_needed' => 'Nov 20, 2026',
            'items' => json_encode([
                [
                    'component_item_id' => $this->componentItem->id,
                    'material_item_id' => $this->materialItem1->id,
                    'requested_quantity' => 150,
                ]
            ]),
        ]);

        $this->assertEquals(0, $response->json('status'));
        $this->assertStringContainsString('Out of budget', $response->json('message'));
    }
}
