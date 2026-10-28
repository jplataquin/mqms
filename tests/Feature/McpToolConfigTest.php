<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\McpToolConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McpToolConfigTest extends TestCase
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

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function authenticated_user_can_view_tool_configs_list_page()
    {
        $response = $this->actingAs($this->user)->get('/mcp_tool_configs');

        $response->assertStatus(200);
        $response->assertSee('AI / MCP');
        $response->assertSee('Tool Configs');
        $response->assertSee('MCP Tool Descriptions');
    }

    /** @test */
    public function authenticated_user_can_view_edit_tool_config_page()
    {
        $response = $this->actingAs($this->user)->get('/mcp_tool_configs/overall_project_accomplishment/edit');

        $response->assertStatus(200);
        $response->assertSee('Configure Tool Description');
        $response->assertSee('overall_project_accomplishment');
        $response->assertSee('Hardcoded Default Description');
    }

    /** @test */
    public function it_returns_404_for_unknown_tool_edit()
    {
        $response = $this->actingAs($this->user)->get('/mcp_tool_configs/unknown_non_existent_tool/edit');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_fetch_tools_list_via_api()
    {
        $response = $this->actingAs($this->user)->getJson('/api/mcp_tool_configs/list');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 1);
        $data = $response->json('data');

        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(30, count($data));

        $toolNames = collect($data)->pluck('tool_name')->all();
        $this->assertContains('overall_project_accomplishment', $toolNames);
        $this->assertContains('get_record', $toolNames);

        $accomplishmentTool = collect($data)->firstWhere('tool_name', 'overall_project_accomplishment');
        $this->assertFalse($accomplishmentTool['is_customized']);
        $this->assertNotEmpty($accomplishmentTool['default_description']);
    }

    /** @test */
    public function user_can_update_and_reset_mcp_tool_description_and_it_reflects_in_mcp_server()
    {
        $toolName = 'overall_project_accomplishment';
        $customText = 'Custom AI prompt for Overall Project Accomplishment: Returns progress percentage, slippage, and milestones.';

        // 1. Initial tools/list via MCP endpoint should return the default description
        $listPayload = [
            'jsonrpc' => '2.0',
            'id'      => 1,
            'method'  => 'tools/list',
            'params'  => (object) [],
        ];

        $mcpResInitial = $this->actingAs($this->user, 'sanctum')->postJson('/api/mcp', $listPayload);
        $mcpResInitial->assertStatus(200);
        $toolsInitial = $mcpResInitial->json('result.tools');
        $toolItemInitial = collect($toolsInitial)->firstWhere('name', $toolName);
        $this->assertNotNull($toolItemInitial);
        $this->assertStringNotContainsString('Custom AI prompt', $toolItemInitial['description']);

        // 2. Update description via web API
        $updateRes = $this->actingAs($this->user)->postJson('/api/mcp_tool_configs/update', [
            'tool_name'          => $toolName,
            'custom_description' => $customText,
        ]);

        $updateRes->assertStatus(200);
        $updateRes->assertJsonPath('status', 1);

        // Check database
        $this->assertDatabaseHas('mcp_tool_configs', [
            'tool_name'          => $toolName,
            'custom_description' => $customText,
            'updated_by'         => $this->user->id,
        ]);

        // 3. MCP tools/list should now dynamically return the custom description!
        $mcpResUpdated = $this->actingAs($this->user, 'sanctum')->postJson('/api/mcp', $listPayload);
        $mcpResUpdated->assertStatus(200);
        $toolsUpdated = $mcpResUpdated->json('result.tools');
        $toolItemUpdated = collect($toolsUpdated)->firstWhere('name', $toolName);
        $this->assertEquals($customText, $toolItemUpdated['description']);

        // 4. Reset tool description
        $resetRes = $this->actingAs($this->user)->postJson('/api/mcp_tool_configs/reset', [
            'tool_name' => $toolName,
        ]);

        $resetRes->assertStatus(200);
        $resetRes->assertJsonPath('status', 1);

        // 5. MCP tools/list should revert back to original default description
        $mcpResReverted = $this->actingAs($this->user, 'sanctum')->postJson('/api/mcp', $listPayload);
        $mcpResReverted->assertStatus(200);
        $toolsReverted = $mcpResReverted->json('result.tools');
        $toolItemReverted = collect($toolsReverted)->firstWhere('name', $toolName);
        $this->assertEquals($toolItemInitial['description'], $toolItemReverted['description']);
    }
}
