<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\Role;
use App\Models\AccessCode;
use App\Models\ApiCredential;
use App\Models\MaterialItem;
use App\Models\MaterialCanvass;
use App\Models\MaterialQuantityRequestItem;
use App\Models\DirectMaterialCanvass;
use App\Models\DirectMaterialQuantityRequestItem;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\ComponentItem;
use App\Models\MaterialQuantity;
use App\Models\MaterialGroup;
use App\Models\Unit;
use App\Models\MaterialQuantityRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderReceived;
use App\Models\PurchaseOrderReceivedItem;
use App\Models\PaymentTerm;
use App\Models\ManpowerRegistry;
use App\Models\Coupon;
use App\Models\Accomplishment;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class McpServerTest extends TestCase
{
    use RefreshDatabase;

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

    protected function createProject(string $name = 'Test Project'): Project
    {
        $project = new Project();
        $project->name = $name;
        $project->status = 'ACTV';
        $project->created_by = 1;
        $project->save();

        return $project;
    }

    /** @test */
    public function it_can_initialize_mcp_server_via_http()
    {
        $payload = [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => '2024-11-05',
                'capabilities' => (object) [],
                'clientInfo' => [
                    'name' => 'test-mcp-client',
                    'version' => '1.0.0',
                ],
            ],
        ];

        $response = $this->postJson('/mcp', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'jsonrpc' => '2.0',
            'id' => 1,
            'result' => [
                'serverInfo' => [
                    'name' => 'MQMS MCP Server',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_lists_allowed_tools()
    {
        $user = User::factory()->create();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'tools/list',
            'params' => (object) [],
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/mcp', $payload);

        $response->assertStatus(200);
        $tools = $response->json('result.tools');
        $toolNames = collect($tools)->pluck('name')->all();

        $this->assertContains('get_record', $toolNames);
        $this->assertContains('list_records', $toolNames);
        $this->assertContains('search_records', $toolNames);
        $this->assertContains('get_material_price_stats', $toolNames);
        $this->assertContains('list_projects', $toolNames);
        $this->assertContains('list_sections', $toolNames);
        $this->assertContains('list_contract_items', $toolNames);
        $this->assertContains('list_components', $toolNames);
        $this->assertContains('list_component_items', $toolNames);
        $this->assertContains('list_material_quantities', $toolNames);
        $this->assertContains('list_material_items', $toolNames);
        $this->assertContains('list_material_groups', $toolNames);
        $this->assertContains('list_units', $toolNames);
        $this->assertContains('list_material_quantity_requests', $toolNames);
        $this->assertContains('list_material_quantity_request_items', $toolNames);
        $this->assertContains('list_material_canvasses', $toolNames);
        $this->assertContains('list_purchase_orders', $toolNames);
        $this->assertContains('list_purchase_order_items', $toolNames);
        $this->assertContains('list_purchase_order_received', $toolNames);
        $this->assertContains('list_purchase_order_received_items', $toolNames);
        $this->assertContains('list_suppliers', $toolNames);
        $this->assertContains('list_payment_terms', $toolNames);
        $this->assertContains('list_manpower_registry', $toolNames);
        $this->assertContains('list_coupons', $toolNames);
        $this->assertContains('list_accomplishments', $toolNames);
        $this->assertContains('list_users', $toolNames);
        $this->assertContains('list_comments', $toolNames);
    }

    /** @test */
    public function it_lists_allowed_resources_and_no_forbidden_resources()
    {
        $payload = [
            'jsonrpc' => '2.0',
            'id' => 3,
            'method' => 'resources/list',
            'params' => (object) [],
        ];

        $response = $this->postJson('/mcp', $payload);

        $response->assertStatus(200);
        $resources = $response->json('result.resources');
        $uris = collect($resources)->pluck('uri')->all();

        $this->assertContains('mqms://models/project', $uris);
        $this->assertContains('mqms://models/supplier', $uris);
        $this->assertContains('mqms://models/direct_purchase_order', $uris);

        // Security check: ensure excluded models are not in resources
        $this->assertNotContains('mqms://models/role', $uris);
        $this->assertNotContains('mqms://models/access_code', $uris);
        $this->assertNotContains('mqms://models/api_credential', $uris);
    }

    /** @test */
    public function it_can_call_get_record_tool_for_allowed_model()
    {
        $project = $this->createProject('Alpha Bridge');

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 4,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_record',
                'arguments' => [
                    'model' => 'project',
                    'id' => $project->id,
                ],
            ],
        ];

        $response = $this->postJson('/mcp', $payload);

        $response->assertStatus(200);
        $response->assertSee('Alpha Bridge');
        $this->assertFalse($response->json('result.isError', false));
    }

    /** @test */
    public function it_refuses_access_to_forbidden_models()
    {
        $payload = [
            'jsonrpc' => '2.0',
            'id' => 5,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_record',
                'arguments' => [
                    'model' => 'role',
                    'id' => 1,
                ],
            ],
        ];

        $response = $this->postJson('/mcp', $payload);

        $response->assertStatus(200);
        $this->assertTrue($response->json('result.isError'));
        $this->assertStringContainsString('not allowed', $response->json('result.content.0.text'));

        // Test ApiCredential
        $payload['params']['arguments']['model'] = 'api_credential';
        $response2 = $this->postJson('/mcp', $payload);
        $this->assertTrue($response2->json('result.isError'));
        $this->assertStringContainsString('not allowed', $response2->json('result.content.0.text'));
    }

    /** @test */
    public function it_strips_passwords_when_accessing_user_records()
    {
        $user = User::factory()->create([
            'name' => 'Secret Officer',
            'email' => 'officer@example.com',
            'password' => bcrypt('super-secret-password-123'),
        ]);

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 6,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_record',
                'arguments' => [
                    'model' => 'user',
                    'id' => $user->id,
                ],
            ],
        ];

        $response = $this->postJson('/mcp', $payload);

        $response->assertStatus(200);
        $response->assertSee('Secret Officer');
        $response->assertDontSee('super-secret-password-123');
        $response->assertDontSee('"password"');
    }

    /** @test */
    public function it_can_list_records_with_pagination()
    {
        for ($i = 1; $i <= 5; $i++) {
            $supplier = new Supplier();
            $supplier->name = "Supplier {$i}";
            $supplier->address = "Address {$i}";
            $supplier->primary_contact_no = "0912345678{$i}";
            $supplier->created_by = 1;
            $supplier->save();
        }

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 7,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_records',
                'arguments' => [
                    'model' => 'supplier',
                    'page' => 1,
                    'limit' => 3,
                ],
            ],
        ];

        $response = $this->postJson('/mcp', $payload);

        $response->assertStatus(200);
        $response->assertSee('Supplier');
        $this->assertFalse($response->json('result.isError', false));
    }

    /** @test */
    public function it_can_search_records()
    {
        $p1 = $this->createProject('Metro Subway Phase 1');
        $p2 = $this->createProject('Coastal Boulevard');

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 8,
            'method' => 'tools/call',
            'params' => [
                'name' => 'search_records',
                'arguments' => [
                    'model' => 'project',
                    'query' => 'Subway',
                ],
            ],
        ];

        $response = $this->postJson('/mcp', $payload);

        $response->assertStatus(200);
        $response->assertSee('Metro Subway Phase 1');
        $response->assertDontSee('Coastal Boulevard');
    }

    /** @test */
    public function it_can_read_resource_uri_for_allowed_record()
    {
        $project = $this->createProject('Seaside Promenade');

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 9,
            'method' => 'resources/read',
            'params' => [
                'uri' => "mqms://models/project/{$project->id}",
            ],
        ];

        $response = $this->postJson('/mcp', $payload);

        $response->assertStatus(200);
        $response->assertSee('Seaside Promenade');
    }

    /** @test */
    public function it_can_calculate_material_price_stats_via_mcp_tool()
    {
        $materialItem = new MaterialItem();
        $materialItem->name = 'Type 1P Portland Cement';
        $materialItem->specification_unit_packaging = '40kg [ Bag ]';
        $materialItem->brand = 'Holcim';
        $materialItem->material_group_id = 1;
        $materialItem->created_by = 1;
        $materialItem->save();

        $supplierA = new Supplier();
        $supplierA->name = 'Apex Building Supplies';
        $supplierA->address = 'Iloilo City';
        $supplierA->primary_contact_no = '09111111111';
        $supplierA->created_by = 1;
        $supplierA->save();

        $supplierB = new Supplier();
        $supplierB->name = 'Beacon Trade Depot';
        $supplierB->address = 'Pavia';
        $supplierB->primary_contact_no = '09222222222';
        $supplierB->created_by = 1;
        $supplierB->save();

        // 1. Standard Material Quantity Request Item & Canvasses
        $mqri = new MaterialQuantityRequestItem();
        $mqri->material_quantity_request_id = 1;
        $mqri->component_item_id = 1;
        $mqri->material_item_id = $materialItem->id;
        $mqri->requested_quantity = 100;
        $mqri->save();

        // Approved standard canvass 1 (Supplier A, 200.00, 2026-05-10)
        $mc1 = new MaterialCanvass();
        $mc1->material_quantity_request_id = 1;
        $mc1->material_quantity_request_item_id = $mqri->id;
        $mc1->supplier_id = $supplierA->id;
        $mc1->price = 200.00;
        $mc1->status = 'APRV';
        $mc1->approved_at = '2026-05-10 10:00:00';
        $mc1->created_by = 1;
        $mc1->save();

        // Approved standard canvass 2 (Supplier B, 260.00, 2026-05-20)
        $mc2 = new MaterialCanvass();
        $mc2->material_quantity_request_id = 1;
        $mc2->material_quantity_request_item_id = $mqri->id;
        $mc2->supplier_id = $supplierB->id;
        $mc2->price = 260.00;
        $mc2->status = 'APRV';
        $mc2->approved_at = '2026-05-20 10:00:00';
        $mc2->created_by = 1;
        $mc2->save();

        // Pending standard canvass (Should be ignored)
        $mcPending = new MaterialCanvass();
        $mcPending->material_quantity_request_id = 1;
        $mcPending->material_quantity_request_item_id = $mqri->id;
        $mcPending->supplier_id = $supplierA->id;
        $mcPending->price = 100.00;
        $mcPending->status = 'PEND';
        $mcPending->created_by = 1;
        $mcPending->save();

        // 2. Direct Material Quantity Request Item & Canvasses
        $dmqri = new DirectMaterialQuantityRequestItem();
        $dmqri->direct_material_quantity_request_id = 1;
        $dmqri->material_item_id = $materialItem->id;
        $dmqri->quantity = 50;
        $dmqri->save();

        // Approved direct canvass 1 (Supplier A, 230.00, 2026-06-01)
        $dmc1 = new DirectMaterialCanvass();
        $dmc1->direct_material_quantity_request_id = 1;
        $dmc1->direct_material_quantity_request_item_id = $dmqri->id;
        $dmc1->supplier_id = $supplierA->id;
        $dmc1->price = 230.00;
        $dmc1->status = 'APRV';
        $dmc1->approved_at = '2026-06-01 14:00:00';
        $dmc1->created_by = 1;
        $dmc1->save();

        // A. Call tool with no extra filters (all 3 approved canvasses: 200, 260, 230)
        $payloadAll = [
            'jsonrpc' => '2.0',
            'id' => 10,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_material_price_stats',
                'arguments' => [
                    'material_item_id' => $materialItem->id,
                ],
            ],
        ];

        $responseAll = $this->postJson('/mcp', $payloadAll);
        $responseAll->assertStatus(200);
        $resultAll = json_decode($responseAll->json('result.content.0.text'), true);

        $this->assertEquals(3, $resultAll['total_approved_canvasses']);
        $this->assertEquals(200.00, $resultAll['stats']['min_price']);
        $this->assertEquals(260.00, $resultAll['stats']['max_price']);
        $this->assertEquals(230.00, $resultAll['stats']['average_price']); // (200 + 260 + 230) / 3 = 230.00
        $this->assertEquals(230.00, $resultAll['stats']['latest_price']);
        $this->assertEquals('direct', $resultAll['stats']['latest_source']);
        $this->assertCount(2, $resultAll['supplier_breakdown']);

        // B. Call tool filtered by Supplier A only (2 canvasses: 200, 230)
        $payloadSupplier = [
            'jsonrpc' => '2.0',
            'id' => 11,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_material_price_stats',
                'arguments' => [
                    'material_item_id' => $materialItem->id,
                    'supplier_id' => $supplierA->id,
                ],
            ],
        ];

        $responseSupplier = $this->postJson('/mcp', $payloadSupplier);
        $responseSupplier->assertStatus(200);
        $resultSupplier = json_decode($responseSupplier->json('result.content.0.text'), true);

        $this->assertEquals(2, $resultSupplier['total_approved_canvasses']);
        $this->assertEquals(200.00, $resultSupplier['stats']['min_price']);
        $this->assertEquals(230.00, $resultSupplier['stats']['max_price']);
        $this->assertEquals(215.00, $resultSupplier['stats']['average_price']); // (200 + 230) / 2 = 215.00
        $this->assertCount(1, $resultSupplier['supplier_breakdown']);
        $this->assertEquals('Apex Building Supplies', $resultSupplier['supplier_breakdown'][0]['supplier_name']);

        // C. Call tool filtered by Date Range: 2026-05-01 to 2026-05-15 (only mc1 at 200.00)
        $payloadDate = [
            'jsonrpc' => '2.0',
            'id' => 12,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_material_price_stats',
                'arguments' => [
                    'material_item_id' => $materialItem->id,
                    'date_from' => '2026-05-01',
                    'date_to' => '2026-05-15',
                ],
            ],
        ];

        $responseDate = $this->postJson('/mcp', $payloadDate);
        $responseDate->assertStatus(200);
        $resultDate = json_decode($responseDate->json('result.content.0.text'), true);

        $this->assertEquals(1, $resultDate['total_approved_canvasses']);
        $this->assertEquals(200.00, $resultDate['stats']['min_price']);
        $this->assertEquals(200.00, $resultDate['stats']['max_price']);
        $this->assertEquals(200.00, $resultDate['stats']['average_price']);

        // D. Error when material_item_id is missing
        $payloadMissing = [
            'jsonrpc' => '2.0',
            'id' => 13,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_material_price_stats',
                'arguments' => [],
            ],
        ];
        $responseMissing = $this->postJson('/mcp', $payloadMissing);
        $responseMissing->assertStatus(200);
        $this->assertTrue($responseMissing->json('result.isError'));

        // E. When item has no approved canvasses
        $otherItem = new MaterialItem();
        $otherItem->name = 'Empty Item';
        $otherItem->brand = 'Generic';
        $otherItem->specification_unit_packaging = 'N/A';
        $otherItem->material_group_id = 1;
        $otherItem->created_by = 1;
        $otherItem->save();

        $payloadEmpty = [
            'jsonrpc' => '2.0',
            'id' => 14,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_material_price_stats',
                'arguments' => [
                    'material_item_id' => $otherItem->id,
                ],
            ],
        ];
        $responseEmpty = $this->postJson('/mcp', $payloadEmpty);
        $responseEmpty->assertStatus(200);
        $resultEmpty = json_decode($responseEmpty->json('result.content.0.text'), true);
        $this->assertEquals(0, $resultEmpty['total_approved_canvasses']);
        $this->assertNull($resultEmpty['stats']);
    }

    /** @test */
    public function it_can_call_list_projects_tool_with_filters()
    {
        $p1 = new Project();
        $p1->name = 'Highway 101 Expansion';
        $p1->status = 'ACTV';
        $p1->created_by = 1;
        $p1->created_at = '2026-01-10 10:00:00';
        $p1->save();

        $p2 = new Project();
        $p2->name = 'Metro Subway Tunnel';
        $p2->status = 'ARCH';
        $p2->created_by = 2;
        $p2->created_at = '2026-06-15 12:00:00';
        $p2->save();

        // Filter by name
        $payloadName = [
            'jsonrpc' => '2.0',
            'id' => 15,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_projects',
                'arguments' => ['name' => 'Tunnel'],
            ],
        ];
        $response = $this->postJson('/mcp', $payloadName);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('Metro Subway Tunnel', $result['records'][0]['name']);

        // Filter by status
        $payloadStatus = [
            'jsonrpc' => '2.0',
            'id' => 16,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_projects',
                'arguments' => ['status' => 'ACTV'],
            ],
        ];
        $response = $this->postJson('/mcp', $payloadStatus);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('Highway 101 Expansion', $result['records'][0]['name']);

        // Filter by date_from
        $payloadDate = [
            'jsonrpc' => '2.0',
            'id' => 17,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_projects',
                'arguments' => ['date_from' => '2026-06-01'],
            ],
        ];
        $response = $this->postJson('/mcp', $payloadDate);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('Metro Subway Tunnel', $result['records'][0]['name']);
    }

    /** @test */
    public function it_can_call_list_sections_tool_with_filters()
    {
        $project = $this->createProject('Bridge Project');

        $s1 = new Section();
        $s1->project_id = $project->id;
        $s1->name = 'North Span';
        $s1->created_by = 1;
        $s1->save();

        $s2 = new Section();
        $s2->project_id = 999;
        $s2->name = 'South Span';
        $s2->created_by = 1;
        $s2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 18,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_sections',
                'arguments' => [
                    'project_id' => $project->id,
                    'name' => 'North',
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('North Span', $result['records'][0]['name']);
        $this->assertEquals($project->id, $result['records'][0]['project_id']);
    }

    /** @test */
    public function it_can_call_list_contract_items_tool_with_filters()
    {
        $ci1 = new ContractItem();
        $ci1->section_id = 10;
        $ci1->item_code = 'CI-101';
        $ci1->description = 'Reinforced Concrete Piles';
        $ci1->contract_quantity = 50;
        $ci1->contract_unit_price = 1200.50;
        $ci1->unit_id = 1;
        $ci1->created_by = 1;
        $ci1->save();

        $ci2 = new ContractItem();
        $ci2->section_id = 20;
        $ci2->item_code = 'CI-202';
        $ci2->description = 'Steel Decking';
        $ci2->contract_quantity = 30;
        $ci2->contract_unit_price = 5000.00;
        $ci2->unit_id = 1;
        $ci2->created_by = 1;
        $ci2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 19,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_contract_items',
                'arguments' => [
                    'section_id' => 10,
                    'item_code' => '101',
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('CI-101', $result['records'][0]['item_code']);
    }

    /** @test */
    public function it_can_call_list_components_tool_with_filters()
    {
        $comp1 = new Component();
        $comp1->contract_item_id = 100;
        $comp1->section_id = 10;
        $comp1->unit_id = 1;
        $comp1->quantity = 10.0;
        $comp1->name = 'Pier Footing';
        $comp1->status = 'ACTV';
        $comp1->created_by = 1;
        $comp1->save();

        $comp2 = new Component();
        $comp2->contract_item_id = 200;
        $comp2->section_id = 20;
        $comp2->unit_id = 1;
        $comp2->quantity = 5.0;
        $comp2->name = 'Abutment Wall';
        $comp2->status = 'PEND';
        $comp2->created_by = 1;
        $comp2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 20,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_components',
                'arguments' => [
                    'contract_item_id' => 100,
                    'status' => 'ACTV',
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('Pier Footing', $result['records'][0]['name']);
    }

    /** @test */
    public function it_can_call_list_component_items_tool_with_filters()
    {
        $item1 = new ComponentItem();
        $item1->component_id = 50;
        $item1->unit_id = 1;
        $item1->function_type_id = 1;
        $item1->function_variable = 1.0;
        $item1->name = 'Deformed Reinforcing Bars 16mm';
        $item1->quantity = 100;
        $item1->budget_price = 350.00;
        $item1->created_by = 1;
        $item1->save();

        $item2 = new ComponentItem();
        $item2->component_id = 60;
        $item2->unit_id = 1;
        $item2->function_type_id = 1;
        $item2->function_variable = 1.0;
        $item2->name = 'Ready Mix Concrete 3000 PSI';
        $item2->quantity = 25;
        $item2->budget_price = 4200.00;
        $item2->created_by = 1;
        $item2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 21,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_component_items',
                'arguments' => [
                    'component_id' => 50,
                    'name' => 'Bars',
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('Deformed Reinforcing Bars 16mm', $result['records'][0]['name']);
    }

    /** @test */
    public function it_can_call_list_material_quantities_tool_with_filters()
    {
        $mq1 = new MaterialQuantity();
        $mq1->component_item_id = 10;
        $mq1->material_item_id = 1;
        $mq1->quantity = 50.0;
        $mq1->equivalent = 1.0;
        $mq1->created_by = 1;
        $mq1->save();

        $mq2 = new MaterialQuantity();
        $mq2->component_item_id = 20;
        $mq2->material_item_id = 2;
        $mq2->quantity = 100.0;
        $mq2->equivalent = 1.0;
        $mq2->created_by = 1;
        $mq2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 22,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_material_quantities',
                'arguments' => [
                    'component_item_id' => 10,
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals(50.0, $result['records'][0]['quantity']);
        $this->assertEquals(1, $result['records'][0]['material_item_id']);
    }

    /** @test */
    public function it_can_call_list_material_items_tool_with_filters()
    {
        $item1 = new MaterialItem();
        $item1->material_group_id = 1;
        $item1->name = 'Portland Cement Type 1';
        $item1->brand = 'Holcim';
        $item1->specification_unit_packaging = '40kg bag';
        $item1->created_by = 1;
        $item1->save();

        $item2 = new MaterialItem();
        $item2->material_group_id = 2;
        $item2->name = 'Deformed Bar 12mm';
        $item2->brand = 'SteelAsia';
        $item2->specification_unit_packaging = '6m length';
        $item2->created_by = 1;
        $item2->save();

        // Filter by brand
        $payload = [
            'jsonrpc' => '2.0',
            'id' => 23,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_material_items',
                'arguments' => [
                    'brand' => 'Steel',
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('Deformed Bar 12mm', $result['records'][0]['name']);

        // Filter by specification packaging
        $payloadSpec = [
            'jsonrpc' => '2.0',
            'id' => 24,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_material_items',
                'arguments' => [
                    'specification_unit_packaging' => 'bag',
                ],
            ],
        ];
        $responseSpec = $this->postJson('/mcp', $payloadSpec);
        $responseSpec->assertStatus(200);
        $resultSpec = json_decode($responseSpec->json('result.content.0.text'), true);
        $this->assertEquals(1, $resultSpec['total']);
        $this->assertEquals('Portland Cement Type 1', $resultSpec['records'][0]['name']);
    }

    /** @test */
    public function it_can_call_list_material_groups_tool_with_filters()
    {
        $g1 = new MaterialGroup();
        $g1->name = 'Cement and Aggregates';
        $g1->created_by = 1;
        $g1->save();

        $g2 = new MaterialGroup();
        $g2->name = 'Metals and Structural Steel';
        $g2->created_by = 1;
        $g2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 25,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_material_groups',
                'arguments' => [
                    'name' => 'Steel',
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('Metals and Structural Steel', $result['records'][0]['name']);
    }

    /** @test */
    public function it_can_call_list_units_tool_with_filters()
    {
        $u1 = new Unit();
        $u1->text = 'Bags';
        $u1->created_by = 1;
        $u1->save();

        $u2 = new Unit();
        $u2->text = 'Kilograms';
        $u2->created_by = 1;
        $u2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 26,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_units',
                'arguments' => [
                    'text' => 'Kilo',
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('Kilograms', $result['records'][0]['text']);
    }

    /** @test */
    public function it_can_call_list_material_quantity_requests_tool_with_filters()
    {
        $mqr1 = new MaterialQuantityRequest();
        $mqr1->project_id = 1;
        $mqr1->section_id = 10;
        $mqr1->component_id = 100;
        $mqr1->status = 'APRV';
        $mqr1->description = 'Batch 1 materials';
        $mqr1->created_by = 1;
        $mqr1->save();

        $mqr2 = new MaterialQuantityRequest();
        $mqr2->project_id = 2;
        $mqr2->section_id = 20;
        $mqr2->component_id = 200;
        $mqr2->status = 'PEND';
        $mqr2->description = 'Batch 2 materials';
        $mqr2->created_by = 1;
        $mqr2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 27,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_material_quantity_requests',
                'arguments' => [
                    'project_id' => 1,
                    'status' => 'APRV',
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('Batch 1 materials', $result['records'][0]['description']);
    }

    /** @test */
    public function it_can_call_list_material_quantity_request_items_tool_with_filters()
    {
        $item1 = new MaterialQuantityRequestItem();
        $item1->material_quantity_request_id = 10;
        $item1->component_item_id = 100;
        $item1->material_item_id = 5;
        $item1->requested_quantity = 25.5;
        $item1->status = 'APRV';
        $item1->save();

        $item2 = new MaterialQuantityRequestItem();
        $item2->material_quantity_request_id = 20;
        $item2->component_item_id = 200;
        $item2->material_item_id = 6;
        $item2->requested_quantity = 50.0;
        $item2->status = 'PEND';
        $item2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 28,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_material_quantity_request_items',
                'arguments' => [
                    'material_quantity_request_id' => 10,
                    'material_item_id' => 5,
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals(25.5, $result['records'][0]['requested_quantity']);
    }

    /** @test */
    public function it_can_call_list_material_canvasses_tool_with_filters()
    {
        $mc1 = new MaterialCanvass();
        $mc1->material_quantity_request_id = 10;
        $mc1->material_quantity_request_item_id = 1;
        $mc1->supplier_id = 100;
        $mc1->payment_term_id = 1;
        $mc1->status = 'APRV';
        $mc1->price = 150.00;
        $mc1->created_by = 1;
        $mc1->save();

        $mc2 = new MaterialCanvass();
        $mc2->material_quantity_request_id = 20;
        $mc2->material_quantity_request_item_id = 2;
        $mc2->supplier_id = 200;
        $mc2->payment_term_id = 2;
        $mc2->status = 'PEND';
        $mc2->price = 180.00;
        $mc2->created_by = 1;
        $mc2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 29,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_material_canvasses',
                'arguments' => [
                    'supplier_id' => 100,
                    'status' => 'APRV',
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals(150.00, $result['records'][0]['price']);
    }

    /** @test */
    public function it_can_call_list_purchase_orders_tool_with_filters()
    {
        $po1 = new PurchaseOrder();
        $po1->project_id = 1;
        $po1->section_id = 10;
        $po1->contract_item_id = 50;
        $po1->component_id = 100;
        $po1->material_quantity_request_id = 5;
        $po1->supplier_id = 15;
        $po1->payment_term_id = 1;
        $po1->status = 'APRV';
        $po1->created_by = 1;
        $po1->save();

        $po2 = new PurchaseOrder();
        $po2->project_id = 2;
        $po2->section_id = 20;
        $po2->contract_item_id = 60;
        $po2->component_id = 200;
        $po2->material_quantity_request_id = 6;
        $po2->supplier_id = 25;
        $po2->payment_term_id = 2;
        $po2->status = 'PEND';
        $po2->created_by = 1;
        $po2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 30,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_purchase_orders',
                'arguments' => [
                    'supplier_id' => 15,
                    'status' => 'APRV',
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals(15, $result['records'][0]['supplier_id']);
    }

    /** @test */
    public function it_can_call_list_purchase_order_items_tool_with_filters()
    {
        $item1 = new PurchaseOrderItem();
        $item1->purchase_order_id = 10;
        $item1->component_item_id = 100;
        $item1->material_quantity_request_item_id = 1;
        $item1->material_canvass_id = 5;
        $item1->material_item_id = 20;
        $item1->status = 'APRV';
        $item1->quantity = 30.0;
        $item1->price = 250.00;
        $item1->save();

        $item2 = new PurchaseOrderItem();
        $item2->purchase_order_id = 20;
        $item2->component_item_id = 200;
        $item2->material_quantity_request_item_id = 2;
        $item2->material_canvass_id = 6;
        $item2->material_item_id = 30;
        $item2->status = 'PEND';
        $item2->quantity = 60.0;
        $item2->price = 300.00;
        $item2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 31,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_purchase_order_items',
                'arguments' => [
                    'purchase_order_id' => 10,
                    'material_item_id' => 20,
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals(30.0, $result['records'][0]['quantity']);
    }

    /** @test */
    public function it_can_call_list_purchase_order_received_tool_with_filters()
    {
        $rec1 = new PurchaseOrderReceived();
        $rec1->purchase_order_id = 10;
        $rec1->receipt_no = 'DR-9901';
        $rec1->received_date = '2026-10-15 10:00:00';
        $rec1->remarks = 'Partial delivery';
        $rec1->created_by = 1;
        $rec1->save();

        $rec2 = new PurchaseOrderReceived();
        $rec2->purchase_order_id = 20;
        $rec2->receipt_no = 'DR-8802';
        $rec2->received_date = '2026-10-20 14:00:00';
        $rec2->remarks = 'Final delivery';
        $rec2->created_by = 1;
        $rec2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 32,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_purchase_order_received',
                'arguments' => [
                    'purchase_order_id' => 10,
                    'receipt_no' => '9901',
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('DR-9901', $result['records'][0]['receipt_no']);
    }

    /** @test */
    public function it_can_call_list_purchase_order_received_items_tool_with_filters()
    {
        $item1 = new PurchaseOrderReceivedItem();
        $item1->purchase_order_received_id = 10;
        $item1->purchase_order_item_id = 50;
        $item1->quantity_received = 15.0;
        $item1->save();

        $item2 = new PurchaseOrderReceivedItem();
        $item2->purchase_order_received_id = 20;
        $item2->purchase_order_item_id = 60;
        $item2->quantity_received = 30.0;
        $item2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 33,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_purchase_order_received_items',
                'arguments' => [
                    'purchase_order_received_id' => 10,
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals(15.0, $result['records'][0]['quantity_received']);
    }

    /** @test */
    public function it_can_call_list_suppliers_tool_with_filters()
    {
        $sup1 = new Supplier();
        $sup1->name = 'Apex Hardware Supply';
        $sup1->address = 'Iloilo City Central';
        $sup1->primary_contact_person = 'Juan Dela Cruz';
        $sup1->primary_contact_no = '09171234567';
        $sup1->primary_email = 'juan@apex.com';
        $sup1->created_by = 1;
        $sup1->save();

        $sup2 = new Supplier();
        $sup2->name = 'Beacon Heavy Metals';
        $sup2->address = 'Bacolod City';
        $sup2->primary_contact_person = 'Maria Santos';
        $sup2->primary_contact_no = '09187654321';
        $sup2->primary_email = 'maria@beacon.com';
        $sup2->created_by = 2;
        $sup2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 34,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_suppliers',
                'arguments' => [
                    'name' => 'Apex',
                    'contact_person' => 'Juan',
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('Apex Hardware Supply', $result['records'][0]['name']);
    }

    /** @test */
    public function it_can_call_list_payment_terms_tool_with_filters()
    {
        $term1 = new PaymentTerm();
        $term1->text = '30 Days Net Credit';
        $term1->created_by = 1;
        $term1->save();

        $term2 = new PaymentTerm();
        $term2->text = 'Cash on Delivery (COD)';
        $term2->created_by = 2;
        $term2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 35,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_payment_terms',
                'arguments' => [
                    'text' => '30 Days',
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('30 Days Net Credit', $result['records'][0]['text']);
    }

    /** @test */
    public function it_can_call_list_manpower_registry_tool_with_filters()
    {
        $worker1 = new ManpowerRegistry();
        $worker1->firstname = 'Pedro';
        $worker1->lastname = 'Penduko';
        $worker1->gender = 'M';
        $worker1->birthdate = '1990-05-15';
        $worker1->mobile_no = '09191112233';
        $worker1->type = 'Skilled';
        $worker1->status = 'ACTV';
        $worker1->structure_category = 'VERT';
        $worker1->region = 'VI____';
        $worker1->province = 'ILOILO';
        $worker1->city_municipality = 'ILOILO';
        $worker1->welder = 1;
        $worker1->mason = 0;
        $worker1->created_by = 1;
        $worker1->save();

        $worker2 = new ManpowerRegistry();
        $worker2->firstname = 'Ana';
        $worker2->lastname = 'Reyes';
        $worker2->gender = 'F';
        $worker2->birthdate = '1995-08-20';
        $worker2->mobile_no = '09192223344';
        $worker2->type = 'Labor';
        $worker2->status = 'PEND';
        $worker2->structure_category = 'HORI';
        $worker2->region = 'VI____';
        $worker2->province = 'CAPIZ_';
        $worker2->city_municipality = 'ROXAS_';
        $worker2->welder = 0;
        $worker2->mason = 1;
        $worker2->created_by = 1;
        $worker2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 36,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_manpower_registry',
                'arguments' => [
                    'type' => 'Skilled',
                    'welder' => true,
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('Pedro', $result['records'][0]['firstname']);
        $this->assertEquals('Penduko', $result['records'][0]['lastname']);
    }

    /** @test */
    public function it_can_call_list_coupons_tool_with_filters()
    {
        $coupon1 = new Coupon();
        $coupon1->status = 'UNCL';
        $coupon1->salt = 'randomsalt123456';
        $coupon1->code = 'hashcode_diesel_voucher_01';
        $coupon1->amount = 1500.00;
        $coupon1->plate_no = 'ABC-1234';
        $coupon1->remarks = 'Fuel for Dump Truck 1';
        $coupon1->created_by = 1;
        $coupon1->save();

        $coupon2 = new Coupon();
        $coupon2->status = 'CLMD';
        $coupon2->salt = 'randomsalt654321';
        $coupon2->code = 'hashcode_diesel_voucher_02';
        $coupon2->amount = 2500.00;
        $coupon2->plate_no = 'XYZ-7890';
        $coupon2->claimed_by_name = 'Roberto Cruz';
        $coupon2->remarks = 'Fuel for Excavator 2';
        $coupon2->created_by = 1;
        $coupon2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 37,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_coupons',
                'arguments' => [
                    'status' => 'UNCL',
                    'plate_no' => 'ABC',
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('ABC-1234', $result['records'][0]['plate_no']);
    }

    /** @test */
    public function it_can_call_list_accomplishments_tool_with_filters()
    {
        $acc1 = new Accomplishment();
        $acc1->contract_item_id = 45;
        $acc1->type = 'ACTUAL';
        $acc1->entry_data = '2026-10-15';
        $acc1->quantity = 75.5;
        $acc1->remarks = 'Completed southern embankment pour';
        $acc1->created_by = 1;
        $acc1->save();

        $acc2 = new Accomplishment();
        $acc2->contract_item_id = 45;
        $acc2->type = 'TARGET';
        $acc2->entry_data = '2026-10-20';
        $acc2->quantity = 100.0;
        $acc2->remarks = 'Planned northern foundation pour';
        $acc2->created_by = 1;
        $acc2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 38,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_accomplishments',
                'arguments' => [
                    'contract_item_id' => 45,
                    'type' => 'ACTUAL',
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals(75.5, $result['records'][0]['quantity']);
        $this->assertEquals('Completed southern embankment pour', $result['records'][0]['remarks']);
    }

    /** @test */
    public function it_can_call_list_users_tool_with_filters_and_scrubs_passwords()
    {
        $u1 = new User();
        $u1->name = 'Engineer Roberto Santos';
        $u1->email = 'roberto.santos@mqms.local';
        $u1->password = bcrypt('secret123');
        $u1->status = 'ACTV';
        $u1->created_by = 1;
        $u1->save();

        $u2 = new User();
        $u2->name = 'Auditor Teresa Reyes';
        $u2->email = 'teresa.reyes@mqms.local';
        $u2->password = bcrypt('secret456');
        $u2->status = 'DCTV';
        $u2->created_by = 1;
        $u2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 39,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_users',
                'arguments' => [
                    'name' => 'Roberto',
                    'status' => 'ACTV',
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('Engineer Roberto Santos', $result['records'][0]['name']);
        $this->assertEquals('roberto.santos@mqms.local', $result['records'][0]['email']);

        // Rigorous security check: ensure password and remember_token are not exposed
        $this->assertArrayNotHasKey('password', $result['records'][0]);
        $this->assertArrayNotHasKey('remember_token', $result['records'][0]);
    }

    /** @test */
    public function it_can_call_list_comments_tool_with_filters()
    {
        $c1 = new Comment();
        $c1->comment_type = 'NOTE';
        $c1->record_type = 'PROJEC';
        $c1->record_id = 99;
        $c1->content = 'Site inspection approved by quality officer';
        $c1->created_by = 1;
        $c1->save();

        $c2 = new Comment();
        $c2->comment_type = 'WARN';
        $c2->record_type = 'PRORDR';
        $c2->record_id = 50;
        $c2->content = 'Delivery delayed due to heavy rainfall';
        $c2->created_by = 2;
        $c2->save();

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 40,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_comments',
                'arguments' => [
                    'record_type' => 'PROJEC',
                    'record_id' => 99,
                    'content' => 'inspection',
                ],
            ],
        ];
        $response = $this->postJson('/mcp', $payload);
        $response->assertStatus(200);
        $result = json_decode($response->json('result.content.0.text'), true);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('Site inspection approved by quality officer', $result['records'][0]['content']);
        $this->assertEquals('PROJEC', $result['records'][0]['record_type']);
        $this->assertEquals(99, $result['records'][0]['record_id']);
    }
}
