<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\Unit;
use App\Models\MaterialGroup;
use App\Models\MaterialItem;
use App\Models\MaterialQuantityRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\PaymentTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseOrderReceivedTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;
    protected $section;
    protected $contractItem;
    protected $component;
    protected $supplier;
    protected $paymentTerm;
    protected $materialGroup;
    protected $materialCement;
    protected $po;
    protected $poItem;

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

        $this->user = User::factory()->create();

        $unit = new Unit();
        $unit->text = 'Bags';
        $unit->created_by = $this->user->id;
        $unit->save();

        $this->supplier = new Supplier();
        $this->supplier->name = 'Apex Hardware';
        $this->supplier->address = '123 Main St';
        $this->supplier->primary_contact_no = '09123456789';
        $this->supplier->created_by = $this->user->id;
        $this->supplier->save();

        $this->paymentTerm = new PaymentTerm();
        $this->paymentTerm->text = 'Cash on Delivery';
        $this->paymentTerm->created_by = $this->user->id;
        $this->paymentTerm->save();

        $this->materialGroup = new MaterialGroup();
        $this->materialGroup->name = 'Masonry';
        $this->materialGroup->created_by = $this->user->id;
        $this->materialGroup->save();

        $this->materialCement = new MaterialItem();
        $this->materialCement->material_group_id = $this->materialGroup->id;
        $this->materialCement->name = 'Portland Cement';
        $this->materialCement->specification_unit_packaging = '40kg Bag';
        $this->materialCement->brand = 'Holcim';
        $this->materialCement->created_by = $this->user->id;
        $this->materialCement->save();

        $this->project = new Project();
        $this->project->name = 'Commercial Plaza';
        $this->project->status = 'ACTV';
        $this->project->created_by = $this->user->id;
        $this->project->save();

        $this->section = new Section();
        $this->section->project_id = $this->project->id;
        $this->section->name = 'Foundation Phase';
        $this->section->created_by = $this->user->id;
        $this->section->save();

        $this->contractItem = new ContractItem();
        $this->contractItem->section_id = $this->section->id;
        $this->contractItem->item_type = 'MAT';
        $this->contractItem->item_code = '100';
        $this->contractItem->description = 'Civil Works';
        $this->contractItem->contract_quantity = 500;
        $this->contractItem->unit_id = $unit->id;
        $this->contractItem->contract_unit_price = 250.00;
        $this->contractItem->created_by = $this->user->id;
        $this->contractItem->save();

        $this->component = new Component();
        $this->component->section_id = $this->section->id;
        $this->component->contract_item_id = $this->contractItem->id;
        $this->component->name = 'Footing Column';
        $this->component->unit_id = $unit->id;
        $this->component->quantity = 100;
        $this->component->status = 'ACTV';
        $this->component->created_by = $this->user->id;
        $this->component->save();

        $componentItem = new \App\Models\ComponentItem();
        $componentItem->component_id = $this->component->id;
        $componentItem->function_type_id = 3;
        $componentItem->function_variable = 10;
        $componentItem->name = 'Concrete Pouring';
        $componentItem->unit_id = $unit->id;
        $componentItem->quantity = 100;
        $componentItem->budget_price = 250.00;
        $componentItem->created_by = $this->user->id;
        $componentItem->save();

        $mqr = new MaterialQuantityRequest();
        $mqr->project_id = $this->project->id;
        $mqr->section_id = $this->section->id;
        $mqr->component_id = $this->component->id;
        $mqr->contract_item_id = $this->contractItem->id;
        $mqr->status = 'APRV';
        $mqr->description = 'Test MQR';
        $mqr->created_by = $this->user->id;
        $mqr->save();

        $this->po = new PurchaseOrder();
        $this->po->project_id = $this->project->id;
        $this->po->section_id = $this->section->id;
        $this->po->component_id = $this->component->id;
        $this->po->contract_item_id = $this->contractItem->id;
        $this->po->material_quantity_request_id = $mqr->id;
        $this->po->supplier_id = $this->supplier->id;
        $this->po->payment_term_id = $this->paymentTerm->id;
        $this->po->status = 'APRV';
        $this->po->received_status = 'PEND';
        $this->po->created_by = $this->user->id;
        $this->po->extras = json_encode([]);
        $this->po->save();

        $this->poItem = new PurchaseOrderItem();
        $this->poItem->purchase_order_id = $this->po->id;
        $this->poItem->component_item_id = $componentItem->id;
        $this->poItem->material_quantity_request_item_id = 1;
        $this->poItem->material_canvass_id = 1;
        $this->poItem->material_item_id = $this->materialCement->id;
        $this->poItem->status = 'APRV';
        $this->poItem->quantity = 100;
        $this->poItem->price = 250.00;
        $this->poItem->save();
    }

    public function test_partial_receiving_updates_received_status_to_part()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/purchase_order/received/create', [
            'purchase_order_id' => $this->po->id,
            'receipt_no'        => 'DR-001',
            'received_date'     => '2026-10-09',
            'remarks'           => 'First batch partial pickup',
            'items'             => [
                [
                    'purchase_order_item_id' => $this->poItem->id,
                    'quantity_received'      => 40,
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'data'   => [
                'received_status' => 'PART'
            ]
        ]);

        $this->po->refresh();
        $this->assertEquals('PART', $this->po->received_status);

        $this->poItem->refresh();
        $this->assertEquals(40, $this->poItem->received_quantity);
        $this->assertEquals(60, $this->poItem->remaining_quantity);
    }

    public function test_receiving_more_than_remaining_is_rejected()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/purchase_order/received/create', [
            'purchase_order_id' => $this->po->id,
            'receipt_no'        => 'DR-001',
            'received_date'     => '2026-10-09',
            'items'             => [
                [
                    'purchase_order_item_id' => $this->poItem->id,
                    'quantity_received'      => 150, // exceeds 100
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 0
        ]);
        $this->assertStringContainsString('exceeds remaining balance', $response->json('message'));
    }

    public function test_completing_all_deliveries_updates_received_status_to_comp()
    {
        $this->actingAs($this->user);

        // First delivery: 60
        $this->postJson('/api/purchase_order/received/create', [
            'purchase_order_id' => $this->po->id,
            'receipt_no'        => 'DR-001',
            'received_date'     => '2026-10-09',
            'items'             => [
                [
                    'purchase_order_item_id' => $this->poItem->id,
                    'quantity_received'      => 60,
                ]
            ]
        ]);

        // Second delivery: remaining 40
        $response = $this->postJson('/api/purchase_order/received/create', [
            'purchase_order_id' => $this->po->id,
            'receipt_no'        => 'DR-002',
            'received_date'     => '2026-10-10',
            'items'             => [
                [
                    'purchase_order_item_id' => $this->poItem->id,
                    'quantity_received'      => 40,
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'data'   => [
                'received_status' => 'COMP'
            ]
        ]);

        $this->po->refresh();
        $this->assertEquals('COMP', $this->po->received_status);

        $this->poItem->refresh();
        $this->assertEquals(100, $this->poItem->received_quantity);
        $this->assertEquals(0, $this->poItem->remaining_quantity);
    }

    public function test_list_received_history()
    {
        $this->actingAs($this->user);

        $this->postJson('/api/purchase_order/received/create', [
            'purchase_order_id' => $this->po->id,
            'receipt_no'        => 'DR-100',
            'received_date'     => '2026-10-09',
            'items'             => [
                [
                    'purchase_order_item_id' => $this->poItem->id,
                    'quantity_received'      => 50,
                ]
            ]
        ]);

        $response = $this->getJson('/api/purchase_order/received/list?purchase_order_id=' . $this->po->id);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
        ]);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('DR-100', $response->json('data.0.receipt_no'));
    }

    public function test_purchase_order_display_view_renders_with_received_data()
    {
        $this->actingAs($this->user);

        // Add user role/permission for PO viewing
        $role = new \App\Models\Role();
        $role->name = 'Admin';
        $role->save();

        $accessCode = new \App\Models\AccessCode();
        $accessCode->code = 'purchase_order:all:view';
        $accessCode->description = 'View all PO';
        $accessCode->save();

        $roleAccessCode = new \App\Models\RoleAccessCode();
        $roleAccessCode->role_id = $role->id;
        $roleAccessCode->access_code_id = $accessCode->id;
        $roleAccessCode->save();

        $userRole = new \App\Models\UserRole();
        $userRole->user_id = $this->user->id;
        $userRole->role_id = $role->id;
        $userRole->save();

        $this->postJson('/api/purchase_order/received/create', [
            'purchase_order_id' => $this->po->id,
            'receipt_no'        => 'DR-999',
            'received_date'     => '2026-10-09',
            'items'             => [
                [
                    'purchase_order_item_id' => $this->poItem->id,
                    'quantity_received'      => 25,
                ]
            ]
        ]);

        $response = $this->get('/purchase_order/' . $this->po->id);
        $response->assertStatus(200);
        $response->assertSee('Receiving History');
        $response->assertSee('DR-999');
        $response->assertSee('Receive Items');
    }
}
