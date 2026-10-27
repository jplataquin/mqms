<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MaterialGroup;
use App\Models\MaterialItem;
use App\Models\AccessCode;
use App\Models\Role;
use App\Models\RoleAccessCode;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MaterialGroupSpecificationTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $accessCodes = [
        'material_group:all:view',
        'material_group:all:create',
        'material_group:all:update',
        'material_group:own:create',
        'material_group:own:update',
        'material_item:all:view',
        'material_item:all:create',
        'material_item:all:update',
        'material_item:own:create',
        'material_item:own:update',
    ];

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

    protected function grantAccessCode($user, $codeString)
    {
        $accessCode = AccessCode::where('code', $codeString)->first();
        if (!$accessCode) {
            $accessCode = new AccessCode();
            $accessCode->code = $codeString;
            $accessCode->description = 'Test code ' . $codeString;
            $accessCode->save();
        }

        $role = Role::first();
        if (!$role) {
            $role = new Role();
            $role->name = 'Test Role';
            $role->save();

            $userRole = new UserRole();
            $userRole->user_id = $user->id;
            $userRole->role_id = $role->id;
            $userRole->save();
        }

        $roleAccessCode = new RoleAccessCode();
        $roleAccessCode->role_id = $role->id;
        $roleAccessCode->access_code_id = $accessCode->id;
        $roleAccessCode->save();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'status' => 'ACTV',
        ]);

        foreach ($this->accessCodes as $code) {
            $this->grantAccessCode($this->user, $code);
        }
    }

    protected function authenticatedRequest($method, $uri, $data = [])
    {
        return $this->actingAs($this->user->fresh())
            ->json($method, $uri, $data);
    }

    public function test_can_create_material_group_with_specification_template()
    {
        $response = $this->authenticatedRequest('POST', '/api/material/group/create', [
            'name' => 'Steel Pipes',
            'specification_template' => '[Diameter:number]mm x [Length:number]m (Schedule: [Sched:text])',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $this->assertDatabaseHas('material_groups', [
            'name' => 'Steel Pipes',
            'specification_template' => '[Diameter:number]mm x [Length:number]m (Schedule: [Sched:text])',
        ]);
    }

    public function test_can_update_material_group_specification_template()
    {
        $group = new MaterialGroup();
        $group->name = 'Electrical Conduit';
        $group->specification_template = '[Size:number]mm';
        $group->created_by = $this->user->id;
        $group->save();

        $response = $this->authenticatedRequest('POST', '/api/material/group/update', [
            'id' => $group->id,
            'name' => 'Electrical Conduit',
            'specification_template' => '[Size:number]mm - [Type:text]',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $group->refresh();
        $this->assertEquals('[Size:number]mm - [Type:text]', $group->specification_template);
    }

    public function test_cannot_create_material_item_if_group_has_no_template()
    {
        $group = new MaterialGroup();
        $group->name = 'Template-less Group';
        $group->specification_template = null;
        $group->created_by = $this->user->id;
        $group->save();

        $response = $this->authenticatedRequest('POST', '/api/material/item/create', [
            'material_group_id' => $group->id,
            'name' => 'Some Item',
            'specification_unit_packaging' => 'Any Spec',
            'brand' => 'Brand X',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => -2,
            'message' => 'Failed Validation',
            'data' => [
                'specification_unit_packaging' => [
                    'The selected material group does not have a specification template configured. Material items cannot be created or edited without a specification template.'
                ]
            ]
        ]);
    }

    public function test_cannot_create_material_item_if_specification_does_not_match_template()
    {
        $group = new MaterialGroup();
        $group->name = 'Deformed Bars';
        $group->specification_template = '[Diameter:number]mm x [Length:number]m Grade [Grade:text]';
        $group->created_by = $this->user->id;
        $group->save();

        // Wrong numeric format (e.g. diameter is text 'ten' instead of number)
        $response = $this->authenticatedRequest('POST', '/api/material/item/create', [
            'material_group_id' => $group->id,
            'name' => 'Bar Item',
            'specification_unit_packaging' => 'ten mm x 6m Grade 40',
            'brand' => 'SteelCorp',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => -2,
            'message' => 'Failed Validation',
            'data' => [
                'specification_unit_packaging' => [
                    'Specification / Unit Packaging must strictly match the template: ' . $group->specification_template
                ]
            ]
        ]);

        // Wrong static delimiter
        $response = $this->authenticatedRequest('POST', '/api/material/item/create', [
            'material_group_id' => $group->id,
            'name' => 'Bar Item',
            'specification_unit_packaging' => '12mm by 6m Grade 40',
            'brand' => 'SteelCorp',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => -2,
            'message' => 'Failed Validation',
        ]);
    }

    public function test_can_create_material_item_when_specification_strictly_matches_template()
    {
        $group = new MaterialGroup();
        $group->name = 'Deformed Bars';
        $group->specification_template = '[Diameter:number]mm x [Length:number]m Grade [Grade:text]';
        $group->created_by = $this->user->id;
        $group->save();

        $response = $this->authenticatedRequest('POST', '/api/material/item/create', [
            'material_group_id' => $group->id,
            'name' => 'Deformed Steel Bar 16mm',
            'specification_unit_packaging' => '16mm x 6m Grade 40',
            'brand' => 'SteelCorp',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $this->assertDatabaseHas('material_items', [
            'name' => 'Deformed Steel Bar 16mm',
            'specification_unit_packaging' => '16mm x 6m Grade 40',
        ]);
    }

    public function test_cannot_update_material_item_if_group_has_no_template()
    {
        $groupWithTemplate = new MaterialGroup();
        $groupWithTemplate->name = 'Initial Group';
        $groupWithTemplate->specification_template = '[Size:number]mm';
        $groupWithTemplate->created_by = $this->user->id;
        $groupWithTemplate->save();

        $groupWithoutTemplate = new MaterialGroup();
        $groupWithoutTemplate->name = 'Legacy Group';
        $groupWithoutTemplate->specification_template = null;
        $groupWithoutTemplate->created_by = $this->user->id;
        $groupWithoutTemplate->save();

        $item = new MaterialItem();
        $item->material_group_id = $groupWithTemplate->id;
        $item->name = 'Test Item';
        $item->specification_unit_packaging = '25mm';
        $item->brand = 'Test Brand';
        $item->created_by = $this->user->id;
        $item->save();

        $response = $this->authenticatedRequest('POST', '/api/material/item/update', [
            'id' => $item->id,
            'material_group_id' => $groupWithoutTemplate->id,
            'name' => 'Test Item',
            'specification_unit_packaging' => '25mm',
            'brand' => 'Test Brand',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => -2,
            'data' => [
                'specification_unit_packaging' => [
                    'The selected material group does not have a specification template configured. Material items cannot be created or edited without a specification template.'
                ]
            ]
        ]);
    }

    public function test_cannot_update_material_item_if_specification_does_not_match_template()
    {
        $group = new MaterialGroup();
        $group->name = 'Pipes';
        $group->specification_template = '[Diameter:number] inch';
        $group->created_by = $this->user->id;
        $group->save();

        $item = new MaterialItem();
        $item->material_group_id = $group->id;
        $item->name = 'PVC Pipe';
        $item->specification_unit_packaging = '2 inch';
        $item->brand = 'Atlanta';
        $item->created_by = $this->user->id;
        $item->save();

        $response = $this->authenticatedRequest('POST', '/api/material/item/update', [
            'id' => $item->id,
            'material_group_id' => $group->id,
            'name' => 'PVC Pipe',
            'specification_unit_packaging' => 'Invalid Specification',
            'brand' => 'Atlanta',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => -2,
            'data' => [
                'specification_unit_packaging' => [
                    'Specification / Unit Packaging must strictly match the template: ' . $group->specification_template
                ]
            ]
        ]);
    }

    public function test_can_update_material_item_when_specification_strictly_matches_template()
    {
        $group = new MaterialGroup();
        $group->name = 'Pipes';
        $group->specification_template = '[Diameter:number] inch';
        $group->created_by = $this->user->id;
        $group->save();

        $item = new MaterialItem();
        $item->material_group_id = $group->id;
        $item->name = 'PVC Pipe';
        $item->specification_unit_packaging = '2 inch';
        $item->brand = 'Atlanta';
        $item->created_by = $this->user->id;
        $item->save();

        $response = $this->authenticatedRequest('POST', '/api/material/item/update', [
            'id' => $item->id,
            'material_group_id' => $group->id,
            'name' => 'PVC Pipe Blue',
            'specification_unit_packaging' => '3.5 inch',
            'brand' => 'Neltex',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $item->refresh();
        $this->assertEquals('PVC Pipe Blue', $item->name);
        $this->assertEquals('3.5 inch', $item->specification_unit_packaging);
        $this->assertEquals('Neltex', $item->brand);
    }

    public function test_renders_material_group_create_view_with_specification_template_field()
    {
        $response = $this->actingAs($this->user)
            ->withSession(['access_codes' => $this->accessCodes])
            ->get('/master_data/material/group/create');

        $response->assertStatus(200);
        $response->assertSee('Specification Template');
        $response->assertSee('id="specificationTemplate"', false);
    }

    public function test_renders_material_group_display_view_with_specification_template_field()
    {
        $group = new MaterialGroup();
        $group->name = 'Structural Steel';
        $group->specification_template = '[Flange:number]mm x [Web:number]mm';
        $group->created_by = $this->user->id;
        $group->save();

        $response = $this->actingAs($this->user)
            ->withSession(['access_codes' => $this->accessCodes])
            ->get('/master_data/material/group/' . $group->id);

        $response->assertStatus(200);
        $response->assertSee('Specification Template');
        $response->assertSee('id="specificationTemplate"', false);
        $response->assertSee('[Flange:number]mm x [Web:number]mm');
    }

    public function test_renders_material_item_create_view_with_template_data_attributes()
    {
        $group = new MaterialGroup();
        $group->name = 'Paints';
        $group->specification_template = '[Volume:number]L Can - [Finish:text]';
        $group->created_by = $this->user->id;
        $group->save();

        $response = $this->actingAs($this->user)
            ->withSession(['access_codes' => $this->accessCodes])
            ->get('/master_data/material/item/create');

        $response->assertStatus(200);
        $response->assertSee('data-template="[Volume:number]L Can - [Finish:text]"', false);
        $response->assertSee('id="noTemplateAlert"', false);
        $response->assertSee('id="templateCard"', false);
        $response->assertSee('id="dynamicInputsArea"', false);
    }

    public function test_renders_material_item_display_view_with_template_data_attributes()
    {
        $group = new MaterialGroup();
        $group->name = 'Paints';
        $group->specification_template = '[Volume:number]L Can - [Finish:text]';
        $group->created_by = $this->user->id;
        $group->save();

        $item = new MaterialItem();
        $item->material_group_id = $group->id;
        $item->name = 'Gloss Latex';
        $item->specification_unit_packaging = '4L Can - Semi-Gloss';
        $item->brand = 'Boysen';
        $item->created_by = $this->user->id;
        $item->save();

        $response = $this->actingAs($this->user)
            ->withSession(['access_codes' => $this->accessCodes])
            ->get('/master_data/material/item/' . $item->id);

        $response->assertStatus(200);
        $response->assertSee('data-template="[Volume:number]L Can - [Finish:text]"', false);
        $response->assertSee('id="noTemplateAlert"', false);
        $response->assertSee('id="templateCard"', false);
        $response->assertSee('id="dynamicInputsArea"', false);
        $response->assertSee('4L Can - Semi-Gloss');
    }

    public function test_can_create_and_validate_item_with_escaped_brackets_in_group_template()
    {
        $group = new MaterialGroup();
        $group->name = 'Rebars with Bracket';
        $group->specification_template = '\[Grade\] [Size:number]mm';
        $group->created_by = $this->user->id;
        $group->save();

        // Valid specification with literal brackets
        $responseValid = $this->authenticatedRequest('POST', '/api/material/item/create', [
            'name' => 'Deformed Bar',
            'material_group_id' => $group->id,
            'specification_unit_packaging' => '[Grade] 16mm',
            'brand' => 'SteelCorp',
        ]);

        $responseValid->assertStatus(200);
        $responseValid->assertJson([
            'status' => 1,
            'message' => '',
        ]);

        $itemId = $responseValid->json('data.id');

        // Invalid specification missing literal brackets
        $responseInvalid = $this->authenticatedRequest('POST', '/api/material/item/create', [
            'name' => 'Deformed Bar Invalid',
            'material_group_id' => $group->id,
            'specification_unit_packaging' => 'Grade 16mm',
            'brand' => 'SteelCorp',
        ]);

        $responseInvalid->assertStatus(200);
        $responseInvalid->assertJson([
            'status' => -2,
        ]);

        // Valid update
        $responseUpdateValid = $this->authenticatedRequest('POST', '/api/material/item/update', [
            'id' => $itemId,
            'name' => 'Deformed Bar Updated',
            'material_group_id' => $group->id,
            'specification_unit_packaging' => '[Grade] 20mm',
            'brand' => 'SteelCorp',
        ]);

        $responseUpdateValid->assertStatus(200);
        $responseUpdateValid->assertJson([
            'status' => 1,
        ]);

        // Invalid update
        $responseUpdateInvalid = $this->authenticatedRequest('POST', '/api/material/item/update', [
            'id' => $itemId,
            'name' => 'Deformed Bar Updated',
            'material_group_id' => $group->id,
            'specification_unit_packaging' => '20mm',
            'brand' => 'SteelCorp',
        ]);

        $responseUpdateInvalid->assertStatus(200);
        $responseUpdateInvalid->assertJson([
            'status' => -2,
        ]);
    }

    public function test_can_create_and_render_material_item_with_fractional_diameter()
    {
        $group = new MaterialGroup();
        $group->name = 'Pipes with Fractional Diameter';
        $group->specification_template = '[Diameter:number]" \[ [Unit:text] \]';
        $group->created_by = $this->user->id;
        $group->save();

        // Create item with fraction 1/2
        $response = $this->authenticatedRequest('POST', '/api/material/item/create', [
            'material_group_id' => $group->id,
            'name' => 'PVC Pipe',
            'specification_unit_packaging' => '1/2" [ pc ]',
            'brand' => 'Neltex',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
        ]);

        $itemId = $response->json('data.id');

        // View display page and ensure specification is rendered
        $viewResponse = $this->actingAs($this->user)
            ->withSession(['access_codes' => $this->accessCodes])
            ->get('/master_data/material/item/' . $itemId);

        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('1/2" [ pc ]');

        // Update with another fraction 3/4
        $updateResponse = $this->authenticatedRequest('POST', '/api/material/item/update', [
            'id' => $itemId,
            'material_group_id' => $group->id,
            'name' => 'PVC Pipe 3/4',
            'specification_unit_packaging' => '3/4" [ pc ]',
            'brand' => 'Neltex',
        ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJson([
            'status' => 1,
        ]);
    }

    public function test_material_group_list_api_returns_items_count_and_supports_sorting()
    {
        $groupA = new MaterialGroup();
        $groupA->name = 'Group With Items';
        $groupA->specification_template = '[Spec:text]';
        $groupA->created_by = $this->user->id;
        $groupA->save();

        $item1 = new MaterialItem();
        $item1->material_group_id = $groupA->id;
        $item1->name = 'Item One';
        $item1->specification_unit_packaging = 'Spec 1';
        $item1->brand = 'Brand A';
        $item1->created_by = $this->user->id;
        $item1->save();

        $item2 = new MaterialItem();
        $item2->material_group_id = $groupA->id;
        $item2->name = 'Item Two';
        $item2->specification_unit_packaging = 'Spec 2';
        $item2->brand = 'Brand B';
        $item2->created_by = $this->user->id;
        $item2->save();

        $groupB = new MaterialGroup();
        $groupB->name = 'Empty Group';
        $groupB->specification_template = '[Spec:text]';
        $groupB->created_by = $this->user->id;
        $groupB->save();

        // Query material group list
        $response = $this->authenticatedRequest('GET', '/api/material/group/list');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
        ]);

        $data = collect($response->json('data'));
        $foundGroupA = $data->firstWhere('id', $groupA->id);
        $foundGroupB = $data->firstWhere('id', $groupB->id);

        $this->assertNotNull($foundGroupA);
        $this->assertNotNull($foundGroupB);
        $this->assertEquals(2, $foundGroupA['items_count']);
        $this->assertEquals(0, $foundGroupB['items_count']);

        // Test sorting by items_count DESC
        $responseDesc = $this->authenticatedRequest('GET', '/api/material/group/list?order_by=items_count&order=DESC');
        $responseDesc->assertStatus(200);
        $descData = $responseDesc->json('data');
        $this->assertEquals($groupA->id, $descData[0]['id']);

        // Test sorting by items_count ASC
        $responseAsc = $this->authenticatedRequest('GET', '/api/material/group/list?order_by=items_count&order=ASC');
        $responseAsc->assertStatus(200);
        $ascData = $responseAsc->json('data');
        $this->assertEquals($groupB->id, $ascData[0]['id']);
    }

    public function test_renders_material_groups_list_view_with_item_count_elements()
    {
        $response = $this->actingAs($this->user)
            ->withSession(['access_codes' => $this->accessCodes])
            ->get('/master_data/material/groups');

        $response->assertStatus(200);
        $response->assertSee('Material Groups');
        $response->assertSee('Most Items');
        $response->assertSee('Least Items');
    }
}
