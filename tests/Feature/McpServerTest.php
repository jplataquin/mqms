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
}
