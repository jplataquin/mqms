<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\Role;
use App\Models\AccessCode;
use App\Models\ApiCredential;
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
        $payload = [
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'tools/list',
            'params' => (object) [],
        ];

        $response = $this->postJson('/api/mcp', $payload);

        $response->assertStatus(200);
        $tools = $response->json('result.tools');
        $toolNames = collect($tools)->pluck('name')->all();

        $this->assertContains('get_record', $toolNames);
        $this->assertContains('list_records', $toolNames);
        $this->assertContains('search_records', $toolNames);
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
}
