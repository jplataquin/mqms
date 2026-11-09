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
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialQuantityRequestItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\PaymentTerm;
use App\Models\Unit;

class MaterialQuantityRequestVoidTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;
    protected $section;
    protected $contractItem;
    protected $component;
    protected $componentItem;
    protected $unit;
    protected $materialItem;
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

        $this->grantAccessCode($this->user, 'material_request:all:void');
        $this->grantAccessCode($this->user, 'material_request:all:view');

        $this->unit = new Unit();
        $this->unit->text = 'pcs';
        $this->unit->created_by = $this->user->id;
        $this->unit->save();

        $this->project = new Project();
        $this->project->name = 'Void Test Project';
        $this->project->status = 'ACTV';
        $this->project->created_by = $this->user->id;
        $this->project->save();

        $this->section = new Section();
        $this->section->name = 'Phase 1';
        $this->section->project_id = $this->project->id;
        $this->section->created_by = $this->user->id;
        $this->section->save();

        $this->contractItem = new ContractItem();
        $this->contractItem->section_id = $this->section->id;
        $this->contractItem->item_type = 'MAT';
        $this->contractItem->item_code = 'ITEM-01';
        $this->contractItem->description = 'Contract Item 1';
        $this->contractItem->contract_quantity = 200.00;
        $this->contractItem->unit_id = $this->unit->id;
        $this->contractItem->contract_unit_price = 1500.00;
        $this->contractItem->created_by = $this->user->id;
        $this->contractItem->save();

        $this->component = new Component();
        $this->component->name = 'Component 1';
        $this->component->contract_item_id = $this->contractItem->id;
        $this->component->section_id = $this->section->id;
        $this->component->quantity = 100;
        $this->component->unit_id = $this->unit->id;
        $this->component->use_count = 1;
        $this->component->status = 'APRV';
        $this->component->created_by = $this->user->id;
        $this->component->save();

        $this->componentItem = new ComponentItem();
        $this->componentItem->name = 'CI 1';
        $this->componentItem->component_id = $this->component->id;
        $this->componentItem->unit_id = $this->unit->id;
        $this->componentItem->quantity = 100;
        $this->componentItem->budget_price = 1000.00;
        $this->componentItem->function_type_id = 1;
        $this->componentItem->function_variable = 1.0;
        $this->componentItem->created_by = $this->user->id;
        $this->componentItem->save();

        $this->materialItem = new MaterialItem();
        $this->materialItem->material_group_id = 1;
        $this->materialItem->name = 'Cement';
        $this->materialItem->specification_unit_packaging = '40kg Bag';
        $this->materialItem->brand = 'Holcim';
        $this->materialItem->created_by = $this->user->id;
        $this->materialItem->save();

        $this->supplier = new Supplier();
        $this->supplier->name = 'Supplier A';
        $this->supplier->address = '123 Street';
        $this->supplier->primary_contact_no = '09123456789';
        $this->supplier->created_by = $this->user->id;
        $this->supplier->save();

        $this->paymentTerm = new PaymentTerm();
        $this->paymentTerm->text = 'Cash on Delivery';
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

    protected function createMqr($status = 'APRV')
    {
        $mqr = new MaterialQuantityRequest();
        $mqr->project_id = $this->project->id;
        $mqr->section_id = $this->section->id;
        $mqr->contract_item_id = $this->contractItem->id;
        $mqr->component_id = $this->component->id;
        $mqr->description = 'Test MQR';
        $mqr->date_needed = '2026-12-01';
        $mqr->status = $status;
        $mqr->created_by = $this->user->id;
        $mqr->save();

        $item = new MaterialQuantityRequestItem();
        $item->material_quantity_request_id = $mqr->id;
        $item->component_item_id = $this->componentItem->id;
        $item->material_item_id = $this->materialItem->id;
        $item->requested_quantity = 50;
        $item->status = $status;
        $item->save();

        return $mqr;
    }

    /** @test */
    public function it_can_void_approved_material_quantity_request_when_no_purchase_orders_exist()
    {
        $mqr = $this->createMqr('APRV');

        $response = $this->actingAs($this->user)->postJson('/api/material_quantity_request/void', [
            'id' => $mqr->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $mqr->refresh();
        $this->assertEquals('VOID', $mqr->status);
        $this->assertEquals($this->user->id, $mqr->void_by);
        $this->assertNotNull($mqr->void_at);

        foreach ($mqr->Items as $item) {
            $this->assertEquals('VOID', $item->status);
        }
    }

    /** @test */
    public function it_prevents_voiding_material_quantity_request_when_active_purchase_order_exists_until_po_is_voided()
    {
        $mqr = $this->createMqr('APRV');

        // Create an active (APRV) PO linked to this MQR
        $po = new PurchaseOrder();
        $po->project_id = $this->project->id;
        $po->section_id = $this->section->id;
        $po->component_id = $this->component->id;
        $po->contract_item_id = $this->contractItem->id;
        $po->material_quantity_request_id = $mqr->id;
        $po->supplier_id = $this->supplier->id;
        $po->payment_term_id = $this->paymentTerm->id;
        $po->status = 'APRV';
        $po->created_by = $this->user->id;
        $po->save();

        // Attempt to void MQR -> Should fail with strict message
        $response = $this->actingAs($this->user)->postJson('/api/material_quantity_request/void', [
            'id' => $mqr->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 0]);
        $this->assertStringContainsString('Existing purchase orders must first be voided', $response->json('message'));
        $this->assertEquals('APRV', $mqr->fresh()->status);

        // Void the purchase order
        $po->status = 'VOID';
        $po->save();

        // Now attempt to void MQR -> Should succeed
        $response2 = $this->actingAs($this->user)->postJson('/api/material_quantity_request/void', [
            'id' => $mqr->id,
        ]);

        $response2->assertStatus(200);
        $response2->assertJson(['status' => 1]);
        $this->assertEquals('VOID', $mqr->fresh()->status);
    }

    /** @test */
    public function it_denies_voiding_material_quantity_request_when_user_lacks_permission()
    {
        $mqr = $this->createMqr('APRV');

        $unauthorizedUser = User::factory()->create([
            'status' => 'ACTV',
        ]);

        $response = $this->actingAs($unauthorizedUser)->postJson('/api/material_quantity_request/void', [
            'id' => $mqr->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 0]);
        $this->assertStringContainsString('Access Denied', $response->json('message'));
        $this->assertEquals('APRV', $mqr->fresh()->status);
    }

    /** @test */
    public function it_can_request_void_for_approved_material_quantity_request_and_set_status_to_revo()
    {
        $this->grantAccessCode($this->user, 'material_request:all:request_void');

        $mqr = $this->createMqr('APRV');

        $response = $this->actingAs($this->user)->postJson('/api/material_quantity_request/request_void', [
            'id' => $mqr->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $mqr->refresh();
        $this->assertEquals('REVO', $mqr->status);
        $this->assertEquals('APRV', $mqr->previous_status);
    }

    /** @test */
    public function it_prevents_requesting_void_when_active_purchase_orders_exist()
    {
        $this->grantAccessCode($this->user, 'material_request:all:request_void');

        $mqr = $this->createMqr('APRV');

        $po = new PurchaseOrder();
        $po->project_id = $this->project->id;
        $po->section_id = $this->section->id;
        $po->component_id = $this->component->id;
        $po->contract_item_id = $this->contractItem->id;
        $po->material_quantity_request_id = $mqr->id;
        $po->supplier_id = $this->supplier->id;
        $po->payment_term_id = $this->paymentTerm->id;
        $po->status = 'APRV';
        $po->created_by = $this->user->id;
        $po->save();

        $response = $this->actingAs($this->user)->postJson('/api/material_quantity_request/request_void', [
            'id' => $mqr->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 0]);
        $this->assertStringContainsString('Existing purchase orders must first be voided', $response->json('message'));
        $this->assertEquals('APRV', $mqr->fresh()->status);
    }

    /** @test */
    public function it_can_void_revo_material_quantity_request_via_review()
    {
        $mqr = $this->createMqr('APRV');
        $mqr->previous_status = 'APRV';
        $mqr->status = 'REVO';
        $mqr->save();

        $response = $this->actingAs($this->user)->postJson('/api/review/material_quantity_request/void', [
            'id' => $mqr->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $mqr->refresh();
        $this->assertEquals('VOID', $mqr->status);
        $this->assertEquals($this->user->id, $mqr->void_by);
        $this->assertNotNull($mqr->void_at);
    }

    /** @test */
    public function it_can_reject_void_and_revert_status_to_previous_status()
    {
        $mqr = $this->createMqr('CLSD');
        $mqr->previous_status = 'CLSD';
        $mqr->status = 'REVO';
        $mqr->save();

        $response = $this->actingAs($this->user)->postJson('/api/review/material_quantity_request/reject_void', [
            'id' => $mqr->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $mqr->refresh();
        $this->assertEquals('CLSD', $mqr->status);
        $this->assertNull($mqr->previous_status);
    }
}
