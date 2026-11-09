<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\AccessCode;
use App\Models\RoleAccessCode;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleAccessCodesTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

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
    }

    /** @test */
    public function it_returns_role_access_codes_ordered_alphabetically_asc()
    {
        $role = new Role();
        $role->name = 'Tester Role';
        $role->description = 'Role for testing access codes order';
        $role->save();

        // Create access codes intentionally out of alphabetical order
        $codeZ = new AccessCode();
        $codeZ->code = 'Z:CODE';
        $codeZ->description = 'Z code description';
        $codeZ->save();

        $codeA = new AccessCode();
        $codeA->code = 'A:CODE';
        $codeA->description = 'A code description';
        $codeA->save();

        $codeM = new AccessCode();
        $codeM->code = 'M:CODE';
        $codeM->description = 'M code description';
        $codeM->save();

        // Attach them to the role in non-alphabetical insertion order (Z, A, M)
        $racZ = new RoleAccessCode();
        $racZ->role_id = $role->id;
        $racZ->access_code_id = $codeZ->id;
        $racZ->save();

        $racA = new RoleAccessCode();
        $racA->role_id = $role->id;
        $racA->access_code_id = $codeA->id;
        $racA->save();

        $racM = new RoleAccessCode();
        $racM->role_id = $role->id;
        $racM->access_code_id = $codeM->id;
        $racM->save();

        $response = $this->actingAs($this->user)->getJson('/api/role/access_codes/' . $role->id);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
        ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);

        // Verify the codes are strictly ordered alphabetically ASC: A:CODE, M:CODE, Z:CODE
        $codes = array_column($data, 'code');
        $this->assertEquals(['A:CODE', 'M:CODE', 'Z:CODE'], $codes);
    }

    /** @test */
    public function it_can_filter_role_access_codes_by_query_on_code_and_description()
    {
        $role = new Role();
        $role->name = 'Security Role';
        $role->description = 'Security role for testing search';
        $role->save();

        $codeAuth = new AccessCode();
        $codeAuth->code = 'auth:login';
        $codeAuth->description = 'Allow user authentication';
        $codeAuth->save();

        $codeUser = new AccessCode();
        $codeUser->code = 'user:view';
        $codeUser->description = 'Allow viewing users';
        $codeUser->save();

        $codeAdmin = new AccessCode();
        $codeAdmin->code = 'system:manage';
        $codeAdmin->description = 'System administration auth rights';
        $codeAdmin->save();

        $rac1 = new RoleAccessCode();
        $rac1->role_id = $role->id;
        $rac1->access_code_id = $codeAuth->id;
        $rac1->save();

        $rac2 = new RoleAccessCode();
        $rac2->role_id = $role->id;
        $rac2->access_code_id = $codeUser->id;
        $rac2->save();

        $rac3 = new RoleAccessCode();
        $rac3->role_id = $role->id;
        $rac3->access_code_id = $codeAdmin->id;
        $rac3->save();

        // 1. Search matching code (only codeUser has 'view')
        $responseCode = $this->actingAs($this->user)->getJson('/api/role/access_codes/' . $role->id . '?query=view');
        $responseCode->assertStatus(200);
        $dataCode = $responseCode->json('data');
        $this->assertCount(1, $dataCode);
        $this->assertEquals('user:view', $dataCode[0]['code']);

        // 2. Search matching description (matches codeAuth 'authentication' and codeAdmin 'auth')
        $responseDesc = $this->actingAs($this->user)->getJson('/api/role/access_codes/' . $role->id . '?query=auth');
        $responseDesc->assertStatus(200);
        $dataDesc = $responseDesc->json('data');
        $this->assertCount(2, $dataDesc);
        $codes = array_column($dataDesc, 'code');
        $this->assertEquals(['auth:login', 'system:manage'], $codes);

        // 3. Search matching code 'manage' (only codeAdmin)
        $responseManage = $this->actingAs($this->user)->getJson('/api/role/access_codes/' . $role->id . '?query=manage');
        $responseManage->assertStatus(200);
        $dataManage = $responseManage->json('data');
        $this->assertCount(1, $dataManage);
        $this->assertEquals('system:manage', $dataManage[0]['code']);

        // 4. Search with no matches
        $responseNone = $this->actingAs($this->user)->getJson('/api/role/access_codes/' . $role->id . '?query=nonexistent');
        $responseNone->assertStatus(200);
        $this->assertCount(0, $responseNone->json('data'));
    }

    /** @test */
    public function it_can_render_role_display_page_with_alphabetical_access_codes_datalist()
    {
        $role = new Role();
        $role->name = 'Developer Role';
        $role->description = 'Developer role description';
        $role->save();

        $codeZ = new AccessCode();
        $codeZ->code = 'Z:TEST';
        $codeZ->description = 'Z test';
        $codeZ->save();

        $codeB = new AccessCode();
        $codeB->code = 'B:TEST';
        $codeB->description = 'B test';
        $codeB->save();

        $codeA = new AccessCode();
        $codeA->code = 'A:TEST';
        $codeA->description = 'A test';
        $codeA->save();

        $response = $this->actingAs($this->user)->get('/role/' . $role->id);

        $response->assertStatus(200);
        $response->assertSee('Access Codes');
        $response->assertSee('id="searchAccessCode"', false);
        $response->assertSee('id="clearSearchBtn"', false);
        $response->assertSee('id="accessCodeCount"', false);
        $response->assertSee('function debounce', false);
        $response->assertSee('debouncedSearch', false);
        $response->assertSee('access-code-group', false);
        $response->assertSee(".split(':')", false);
        $response->assertSee('id="list"', false);
    }
}
