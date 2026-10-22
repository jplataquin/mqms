<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalAccessTokenTest extends TestCase
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
    public function authenticated_user_can_view_tokens_list_page()
    {
        $response = $this->actingAs($this->user)->get('/personal_access_tokens');

        $response->assertStatus(200);
        $response->assertSee('AI / MCP');
        $response->assertSee('Personal Access Tokens');
        $response->assertSee('/api/mcp');
    }

    /** @test */
    public function authenticated_user_can_view_create_token_page()
    {
        $response = $this->actingAs($this->user)->get('/personal_access_tokens/create');

        $response->assertStatus(200);
        $response->assertSee('AI / MCP');
        $response->assertSee('Generate Personal Access Token');
    }

    /** @test */
    public function user_can_create_list_and_delete_personal_access_tokens()
    {
        // 1. Create a token via API
        $createResponse = $this->actingAs($this->user)->postJson('/api/personal_access_tokens/create', [
            'name' => 'Claude Desktop Test',
        ]);

        $createResponse->assertStatus(200);
        $createResponse->assertJson([
            'status' => 1,
            'message' => 'Token created successfully',
        ]);

        $plainTextToken = $createResponse->json('data.plain_text');
        $tokenId = $createResponse->json('data.token.id');
        $this->assertNotEmpty($plainTextToken);
        $this->assertNotNull($tokenId);

        // 2. List tokens
        $listResponse = $this->actingAs($this->user)->getJson('/api/personal_access_tokens/list');
        $listResponse->assertStatus(200);
        $listResponse->assertJson(['status' => 1]);

        $tokens = $listResponse->json('data');
        $this->assertCount(1, $tokens);
        $this->assertEquals('Claude Desktop Test', $tokens[0]['name']);

        // 3. Connect to MCP server using Bearer token
        $mcpPayload = [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => '2024-11-05',
                'capabilities' => (object) [],
                'clientInfo' => [
                    'name' => 'test-agent',
                    'version' => '1.0.0',
                ],
            ],
        ];

        $mcpResponse = $this->withHeader('Authorization', 'Bearer ' . $plainTextToken)
            ->postJson('/api/mcp', $mcpPayload);

        $mcpResponse->assertStatus(200);
        $mcpResponse->assertJson([
            'jsonrpc' => '2.0',
            'id' => 1,
            'result' => [
                'serverInfo' => [
                    'name' => 'MQMS MCP Server',
                ],
            ],
        ]);

        // 4. Delete the token
        $deleteResponse = $this->actingAs($this->user)->postJson('/api/personal_access_tokens/delete', [
            'id' => $tokenId,
        ]);

        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson([
            'status' => 1,
            'message' => 'Token revoked successfully',
        ]);

        // 5. Verify the token is gone
        $listAfterDelete = $this->actingAs($this->user)->getJson('/api/personal_access_tokens/list');
        $this->assertCount(0, $listAfterDelete->json('data'));
    }
}
