<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\MaterialGroup;
use App\Models\MaterialItem;
use App\Models\MaterialQuantityRequest;
use App\Models\DirectMaterialQuantityRequest;
use App\Models\Supplier;
use App\Models\PaymentTerm;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\DirectPurchaseOrder;
use App\Models\DirectPurchaseOrderItem;
use App\Models\ApiCredential;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseOrderAPITest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $credential;
    protected $project;
    protected $section;
    protected $contractItem;
    protected $component;
    protected $supplier;
    protected $paymentTerm;
    protected $materialItem1;
    protected $materialItem2;
    protected $mqr;
    protected $dmqr;

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

        $this->credential = new ApiCredential();
        $this->credential->name = 'Test Partner';
        $this->credential->api_key = 'test_key_' . uniqid();
        $this->credential->secret_key = 'test_secret_' . uniqid();
        $this->credential->created_by = $this->user->id;
        $this->credential->save();

        $this->project = new Project();
        $this->project->name = 'Project Alpha';
        $this->project->status = 'ACTV';
        $this->project->created_by = $this->user->id;
        $this->project->save();

        $this->section = new Section();
        $this->section->name = 'Section Phase 1';
        $this->section->project_id = $this->project->id;
        $this->section->created_by = $this->user->id;
        $this->section->save();

        $unit = new \App\Models\Unit();
        $unit->text = 'LOT';
        $unit->created_by = $this->user->id;
        $unit->save();

        $this->contractItem = new ContractItem();
        $this->contractItem->item_code = 'CI-101';
        $this->contractItem->description = 'Contract Item 101 Description';
        $this->contractItem->section_id = $this->section->id;
        $this->contractItem->unit_id = $unit->id;
        $this->contractItem->contract_quantity = 1;
        $this->contractItem->contract_unit_price = 1000;
        $this->contractItem->created_by = $this->user->id;
        $this->contractItem->save();

        $this->component = new Component();
        $this->component->name = 'Component Foundation';
        $this->component->section_id = $this->section->id;
        $this->component->contract_item_id = $this->contractItem->id;
        $this->component->unit_id = $unit->id;
        $this->component->quantity = 1.0;
        $this->component->status = 'APRV';
        $this->component->created_by = $this->user->id;
        $this->component->save();

        $this->supplier = new Supplier();
        $this->supplier->name = 'Apex Concrete Supply';
        $this->supplier->address = '123 Main St';
        $this->supplier->primary_contact_no = '09123456789';
        $this->supplier->created_by = $this->user->id;
        $this->supplier->save();

        $this->paymentTerm = new PaymentTerm();
        $this->paymentTerm->text = 'Net 30 Days';
        $this->paymentTerm->created_by = $this->user->id;
        $this->paymentTerm->save();

        $group = new MaterialGroup();
        $group->name = 'Cement Group';
        $group->created_by = $this->user->id;
        $group->save();

        $this->materialItem1 = new MaterialItem();
        $this->materialItem1->material_group_id = $group->id;
        $this->materialItem1->name = 'Portland Cement Type 1';
        $this->materialItem1->brand = 'Holcim';
        $this->materialItem1->specification_unit_packaging = 'bag';
        $this->materialItem1->created_by = $this->user->id;
        $this->materialItem1->save();

        $this->materialItem2 = new MaterialItem();
        $this->materialItem2->material_group_id = $group->id;
        $this->materialItem2->name = 'Deformed Steel Bar 16mm';
        $this->materialItem2->brand = 'Steel Corp';
        $this->materialItem2->specification_unit_packaging = 'pc';
        $this->materialItem2->created_by = $this->user->id;
        $this->materialItem2->save();

        $this->mqr = new MaterialQuantityRequest();
        $this->mqr->project_id = $this->project->id;
        $this->mqr->section_id = $this->section->id;
        $this->mqr->component_id = $this->component->id;
        $this->mqr->description = 'Sample MQR description';
        $this->mqr->status = 'APRV';
        $this->mqr->date_needed = '2026-11-15';
        $this->mqr->created_by = $this->user->id;
        $this->mqr->save();

        $this->dmqr = new DirectMaterialQuantityRequest();
        $this->dmqr->project_id = $this->project->id;
        $this->dmqr->section_id = $this->section->id;
        $this->dmqr->description = 'Sample DMQR description';
        $this->dmqr->status = 'APRV';
        $this->dmqr->date_needed = '2026-11-20';
        $this->dmqr->created_by = $this->user->id;
        $this->dmqr->save();
    }

    protected function makeAuthHeaders(string $method, string $path, string $body = ''): array
    {
        $timestamp = time();
        $payload = strtoupper($method) . $path . $timestamp . $body;
        $signature = hash_hmac('sha256', $payload, $this->credential->secret_key);

        return [
            'X-API-KEY' => $this->credential->api_key,
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
            'Accept' => 'application/json',
        ];
    }

    /** @test */
    public function it_can_list_purchase_orders_and_filter_by_attributes()
    {
        // PO 1
        $po1 = new PurchaseOrder();
        $po1->project_id = $this->project->id;
        $po1->section_id = $this->section->id;
        $po1->contract_item_id = $this->contractItem->id;
        $po1->component_id = $this->component->id;
        $po1->material_quantity_request_id = $this->mqr->id;
        $po1->supplier_id = $this->supplier->id;
        $po1->payment_term_id = $this->paymentTerm->id;
        $po1->status = 'APRV';
        $po1->received_status = 'PART';
        $po1->created_by = $this->user->id;
        $po1->created_at = '2026-10-20 10:00:00';
        $po1->save();

        $po1Item = new PurchaseOrderItem();
        $po1Item->purchase_order_id = $po1->id;
        $po1Item->component_item_id = 1;
        $po1Item->material_quantity_request_item_id = 1;
        $po1Item->material_canvass_id = 1;
        $po1Item->material_item_id = $this->materialItem1->id;
        $po1Item->quantity = 100;
        $po1Item->price = 250.00;
        $po1Item->save();

        // PO 2
        $po2 = new PurchaseOrder();
        $po2->project_id = $this->project->id;
        $po2->section_id = $this->section->id;
        $po2->contract_item_id = $this->contractItem->id;
        $po2->component_id = $this->component->id;
        $po2->material_quantity_request_id = $this->mqr->id;
        $po2->supplier_id = $this->supplier->id;
        $po2->payment_term_id = $this->paymentTerm->id;
        $po2->status = 'PEND';
        $po2->received_status = 'PEND';
        $po2->created_by = $this->user->id;
        $po2->created_at = '2026-10-25 15:00:00';
        $po2->save();

        $po2Item = new PurchaseOrderItem();
        $po2Item->purchase_order_id = $po2->id;
        $po2Item->component_item_id = 1;
        $po2Item->material_quantity_request_item_id = 2;
        $po2Item->material_canvass_id = 2;
        $po2Item->material_item_id = $this->materialItem2->id;
        $po2Item->quantity = 50;
        $po2Item->price = 500.00;
        $po2Item->save();

        $path = 'api/call/purchase_orders';
        $headers = $this->makeAuthHeaders('GET', $path);

        // 1. Fetch all
        $response = $this->withHeaders($headers)->get('/' . $path);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'message' => 'Success',
        ]);
        $this->assertCount(2, $response->json('data'));

        // 2. Filter by status APRV
        $response = $this->withHeaders($headers)->get('/' . $path . '?status=APRV');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($po1->id, $data[0]['id']);

        // 3. Filter by contains specific material item (materialItem2)
        $response = $this->withHeaders($headers)->get('/' . $path . '?material_item_id=' . $this->materialItem2->id);
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($po2->id, $data[0]['id']);

        // 4. Filter by date_needed
        $response = $this->withHeaders($headers)->get('/' . $path . '?date_needed=2026-11-15');
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        // 5. Filter by contract_item_id
        $response = $this->withHeaders($headers)->get('/' . $path . '?contract_item_id=' . $this->contractItem->id);
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        // 6. Filter by received_status
        $response = $this->withHeaders($headers)->get('/' . $path . '?received_status=PART');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($po1->id, $data[0]['id']);

        // 7. Filter by date_created
        $response = $this->withHeaders($headers)->get('/' . $path . '?date_created=2026-10-20');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($po1->id, $data[0]['id']);

        // 8. Filter by exact ID
        $response = $this->withHeaders($headers)->get('/' . $path . '?id=' . $po1->id);
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($po1->id, $data[0]['id']);

        // 9. Filter by project_id and section_id
        $response = $this->withHeaders($headers)->get('/' . $path . '?project_id=' . $this->project->id . '&section_id=' . $this->section->id);
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        // 10. Filter by component_id
        $response = $this->withHeaders($headers)->get('/' . $path . '?component_id=' . $this->component->id);
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        // 11. Filter by supplier_id and payment_term_id
        $response = $this->withHeaders($headers)->get('/' . $path . '?supplier_id=' . $this->supplier->id . '&payment_term_id=' . $this->paymentTerm->id);
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        // 12. Filter by created_by
        $response = $this->withHeaders($headers)->get('/' . $path . '?created_by=' . $this->user->id);
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        // 13. Filter by material_quantity_request_id
        $response = $this->withHeaders($headers)->get('/' . $path . '?material_quantity_request_id=' . $this->mqr->id);
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        // 14. Filter by date range (date_from & date_to)
        $response = $this->withHeaders($headers)->get('/' . $path . '?date_from=2026-10-24&date_to=2026-10-26');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($po2->id, $data[0]['id']);
    }

    /** @test */
    public function it_can_list_direct_purchase_orders_and_filter_by_attributes()
    {
        // DPO 1
        $dpo1 = new DirectPurchaseOrder();
        $dpo1->project_id = $this->project->id;
        $dpo1->section_id = $this->section->id;
        $dpo1->direct_material_quantity_request_id = $this->dmqr->id;
        $dpo1->supplier_id = $this->supplier->id;
        $dpo1->payment_term_id = $this->paymentTerm->id;
        $dpo1->status = 'APRV';
        $dpo1->received_status = 'COMP';
        $dpo1->date_needed = '2026-11-20';
        $dpo1->created_by = $this->user->id;
        $dpo1->created_at = '2026-10-21 11:00:00';
        $dpo1->save();

        $dpo1Item = new DirectPurchaseOrderItem();
        $dpo1Item->direct_purchase_order_id = $dpo1->id;
        $dpo1Item->material_item_id = $this->materialItem1->id;
        $dpo1Item->quantity = 40;
        $dpo1Item->price = 260.00;
        $dpo1Item->save();

        // DPO 2
        $dpo2 = new DirectPurchaseOrder();
        $dpo2->project_id = $this->project->id;
        $dpo2->section_id = $this->section->id;
        $dpo2->direct_material_quantity_request_id = $this->dmqr->id;
        $dpo2->supplier_id = $this->supplier->id;
        $dpo2->payment_term_id = $this->paymentTerm->id;
        $dpo2->status = 'DRFT';
        $dpo2->received_status = 'PEND';
        $dpo2->date_needed = '2026-12-01';
        $dpo2->created_by = $this->user->id;
        $dpo2->created_at = '2026-10-25 14:00:00';
        $dpo2->save();

        $dpo2Item = new DirectPurchaseOrderItem();
        $dpo2Item->direct_purchase_order_id = $dpo2->id;
        $dpo2Item->material_item_id = $this->materialItem2->id;
        $dpo2Item->quantity = 15;
        $dpo2Item->price = 480.00;
        $dpo2Item->save();

        $path = 'api/call/direct_purchase_orders';
        $headers = $this->makeAuthHeaders('GET', $path);

        // 1. Fetch all
        $response = $this->withHeaders($headers)->get('/' . $path);
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        // 2. Filter by status
        $response = $this->withHeaders($headers)->get('/' . $path . '?status=DRFT');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($dpo2->id, $data[0]['id']);

        // 3. Filter by contains material_item_id (materialItem1)
        $response = $this->withHeaders($headers)->get('/' . $path . '?material_item_id=' . $this->materialItem1->id);
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($dpo1->id, $data[0]['id']);

        // 4. Filter by date_needed
        $response = $this->withHeaders($headers)->get('/' . $path . '?date_needed=2026-12-01');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($dpo2->id, $data[0]['id']);

        // 5. Filter by direct_material_quantity_request_id
        $response = $this->withHeaders($headers)->get('/' . $path . '?direct_material_quantity_request_id=' . $this->dmqr->id);
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        // 6. Filter by received_status
        $response = $this->withHeaders($headers)->get('/' . $path . '?received_status=COMP');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($dpo1->id, $data[0]['id']);

        // 7. Filter by date_created
        $response = $this->withHeaders($headers)->get('/' . $path . '?date_created=2026-10-21');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($dpo1->id, $data[0]['id']);

        // 8. Filter by exact ID
        $response = $this->withHeaders($headers)->get('/' . $path . '?id=' . $dpo2->id);
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($dpo2->id, $data[0]['id']);

        // 9. Filter by project_id and section_id
        $response = $this->withHeaders($headers)->get('/' . $path . '?project_id=' . $this->project->id . '&section_id=' . $this->section->id);
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        // 10. Filter by supplier_id and payment_term_id
        $response = $this->withHeaders($headers)->get('/' . $path . '?supplier_id=' . $this->supplier->id . '&payment_term_id=' . $this->paymentTerm->id);
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        // 11. Filter by created_by
        $response = $this->withHeaders($headers)->get('/' . $path . '?created_by=' . $this->user->id);
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        // 12. Filter by date range (date_from & date_to)
        $response = $this->withHeaders($headers)->get('/' . $path . '?date_from=2026-10-24&date_to=2026-10-26');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($dpo2->id, $data[0]['id']);
    }

    /** @test */
    public function it_rejects_unauthenticated_requests_to_purchase_order_apis()
    {
        $response = $this->getJson('/api/call/purchase_orders');
        $response->assertStatus(401);

        $response = $this->getJson('/api/call/direct_purchase_orders');
        $response->assertStatus(401);
    }
}
