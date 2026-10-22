<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Section;
use App\Models\MaterialGroup;
use App\Models\MaterialItem;
use App\Models\Supplier;
use App\Models\PaymentTerm;
use App\Models\DirectPurchaseOrder;
use App\Models\DirectPurchaseOrderItem;
use App\Models\DirectPurchaseOrderReceived;
use App\Models\DirectPurchaseOrderReceivedItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DirectPurchaseOrderReceivedTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;
    protected $section;
    protected $supplier;
    protected $paymentTerm;
    protected $materialGroup;
    protected $materialItem;
    protected $dpo;
    protected $dpoItem;

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

        $this->supplier = new Supplier();
        $this->supplier->name = 'Apex Direct Hardware';
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

        $this->materialItem = new MaterialItem();
        $this->materialItem->material_group_id = $this->materialGroup->id;
        $this->materialItem->name = 'Portland Cement';
        $this->materialItem->specification_unit_packaging = '40kg Bag';
        $this->materialItem->brand = 'Holcim';
        $this->materialItem->created_by = $this->user->id;
        $this->materialItem->save();

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

        $this->dpo = new DirectPurchaseOrder();
        $this->dpo->supplier_id = $this->supplier->id;
        $this->dpo->payment_term_id = $this->paymentTerm->id;
        $this->dpo->project_id = $this->project->id;
        $this->dpo->section_id = $this->section->id;
        $this->dpo->status = 'APRV';
        $this->dpo->received_status = 'PEND';
        $this->dpo->created_by = $this->user->id;
        $this->dpo->extras = json_encode([]);
        $this->dpo->save();

        $this->dpoItem = new DirectPurchaseOrderItem();
        $this->dpoItem->direct_purchase_order_id = $this->dpo->id;
        $this->dpoItem->material_item_id = $this->materialItem->id;
        $this->dpoItem->status = 'APRV';
        $this->dpoItem->quantity = 100;
        $this->dpoItem->price = 250.00;
        $this->dpoItem->save();
    }

    public function test_partial_receiving_updates_received_status_to_part()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/direct_purchase_order/received/create', [
            'direct_purchase_order_id' => $this->dpo->id,
            'receipt_no'               => 'DDR-001',
            'received_date'            => '2026-10-22',
            'remarks'                  => 'First partial delivery',
            'items'                    => [
                [
                    'direct_purchase_order_item_id' => $this->dpoItem->id,
                    'quantity_received'             => 40,
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

        $this->dpo->refresh();
        $this->assertEquals('PART', $this->dpo->received_status);

        $this->dpoItem->refresh();
        $this->assertEquals(40, $this->dpoItem->received_quantity);
        $this->assertEquals(60, $this->dpoItem->remaining_quantity);
    }

    public function test_receiving_more_than_remaining_is_rejected()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/direct_purchase_order/received/create', [
            'direct_purchase_order_id' => $this->dpo->id,
            'receipt_no'               => 'DDR-001',
            'received_date'            => '2026-10-22',
            'items'                    => [
                [
                    'direct_purchase_order_item_id' => $this->dpoItem->id,
                    'quantity_received'             => 150, // exceeds 100
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
        $this->postJson('/api/direct_purchase_order/received/create', [
            'direct_purchase_order_id' => $this->dpo->id,
            'receipt_no'               => 'DDR-001',
            'received_date'            => '2026-10-22',
            'items'                    => [
                [
                    'direct_purchase_order_item_id' => $this->dpoItem->id,
                    'quantity_received'             => 60,
                ]
            ]
        ]);

        // Second delivery: remaining 40
        $response = $this->postJson('/api/direct_purchase_order/received/create', [
            'direct_purchase_order_id' => $this->dpo->id,
            'receipt_no'               => 'DDR-002',
            'received_date'            => '2026-10-23',
            'items'                    => [
                [
                    'direct_purchase_order_item_id' => $this->dpoItem->id,
                    'quantity_received'             => 40,
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

        $this->dpo->refresh();
        $this->assertEquals('COMP', $this->dpo->received_status);

        $this->dpoItem->refresh();
        $this->assertEquals(100, $this->dpoItem->received_quantity);
        $this->assertEquals(0, $this->dpoItem->remaining_quantity);
    }

    public function test_cannot_receive_items_for_non_approved_direct_po()
    {
        $this->actingAs($this->user);

        $this->dpo->status = 'PEND';
        $this->dpo->save();

        $response = $this->postJson('/api/direct_purchase_order/received/create', [
            'direct_purchase_order_id' => $this->dpo->id,
            'receipt_no'               => 'DDR-001',
            'received_date'            => '2026-10-22',
            'items'                    => [
                [
                    'direct_purchase_order_item_id' => $this->dpoItem->id,
                    'quantity_received'             => 50,
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 0,
            'message' => 'Can only receive items for approved Direct Purchase Orders.'
        ]);
    }

    public function test_list_received_records_for_direct_purchase_order()
    {
        $this->actingAs($this->user);

        $this->postJson('/api/direct_purchase_order/received/create', [
            'direct_purchase_order_id' => $this->dpo->id,
            'receipt_no'               => 'DDR-001',
            'received_date'            => '2026-10-22',
            'remarks'                  => 'First partial delivery',
            'items'                    => [
                [
                    'direct_purchase_order_item_id' => $this->dpoItem->id,
                    'quantity_received'             => 40,
                ]
            ]
        ]);

        $response = $this->getJson('/api/direct_purchase_order/received/list?direct_purchase_order_id=' . $this->dpo->id);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
        ]);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('DDR-001', $response->json('data.0.receipt_no'));
    }
}
