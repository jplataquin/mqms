<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\AccessCode;
use App\Models\UserRole;
use App\Models\RoleAccessCode;
use App\Models\MaterialGroup;
use App\Models\MaterialItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MaterialGroupTemplateFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

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

    protected function grantAccessCode(User $user, string $codeString): void
    {
        $accessCode = AccessCode::where('code', $codeString)->first();
        if (!$accessCode) {
            $accessCode = new AccessCode();
            $accessCode->code = $codeString;
            $accessCode->description = 'Test code ' . $codeString;
            $accessCode->save();
        }

        $role = new Role();
        $role->name = 'Test Role ' . uniqid();
        $role->save();

        $userRole = new UserRole();
        $userRole->user_id = $user->id;
        $userRole->role_id = $role->id;
        $userRole->save();

        $roleAccessCode = new RoleAccessCode();
        $roleAccessCode->role_id = $role->id;
        $roleAccessCode->access_code_id = $accessCode->id;
        $roleAccessCode->save();
    }

    /** @test */
    public function it_can_create_material_group_with_specification_template_via_api()
    {
        $this->grantAccessCode($this->user, 'material_group:all:create');

        $template = '[Thickness:number] mm x [Width:number] mm x [Length:number] mm - [Grade:text]';

        $response = $this->actingAs($this->user->fresh())->postJson('/api/material/group/create', [
            'name' => 'Steel Plates',
            'specification_template' => $template,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $this->assertDatabaseHas('material_groups', [
            'name' => 'Steel Plates',
            'specification_template' => $template,
            'created_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_can_update_material_group_specification_template_via_api()
    {
        $this->grantAccessCode($this->user, 'material_group:all:update');

        $group = new MaterialGroup();
        $group->name = 'Pipes';
        $group->specification_template = '[Diameter:number] in';
        $group->created_by = $this->user->id;
        $group->save();

        $newTemplate = '[Diameter:number] in x [Length:number] m - [Schedule:text]';

        $response = $this->actingAs($this->user->fresh())->postJson('/api/material/group/update', [
            'id' => $group->id,
            'name' => 'Pipes Updated',
            'specification_template' => $newTemplate,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $group->refresh();
        $this->assertEquals('Pipes Updated', $group->name);
        $this->assertEquals($newTemplate, $group->specification_template);
    }

    /** @test */
    public function it_can_fetch_material_group_with_parsed_template_via_api()
    {
        $group = new MaterialGroup();
        $group->name = 'Reinforcing Steel Bars';
        $group->specification_template = '[Diameter:number] mm x [Length:number] m (Grade: [Grade:text])';
        $group->created_by = $this->user->id;
        $group->save();

        $response = $this->actingAs($this->user->fresh())->getJson('/api/material/group/get/' . $group->id);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'data' => [
                'id' => $group->id,
                'name' => 'Reinforcing Steel Bars',
                'has_template' => true,
                'specification_template' => $group->specification_template,
            ],
        ]);

        $data = $response->json('data');
        $this->assertIsArray($data['parsed_template']);
        $this->assertCount(6, $data['parsed_template']);
    }

    /** @test */
    public function it_strictly_blocks_material_item_creation_if_material_group_lacks_template()
    {
        $this->grantAccessCode($this->user, 'material_item:own:create');

        $groupNoTemplate = new MaterialGroup();
        $groupNoTemplate->name = 'Group Without Template';
        $groupNoTemplate->specification_template = null;
        $groupNoTemplate->created_by = $this->user->id;
        $groupNoTemplate->save();

        $response = $this->actingAs($this->user->fresh())->postJson('/api/material/item/create', [
            'material_group_id' => $groupNoTemplate->id,
            'name' => 'Item Attempt',
            'specification_unit_packaging' => '100 pcs',
            'brand' => 'Brand X',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => -2,
            'message' => 'Failed Validation',
        ]);

        $this->assertArrayHasKey('specification_unit_packaging', $response->json('data'));
        $this->assertDatabaseMissing('material_items', [
            'name' => 'Item Attempt',
            'material_group_id' => $groupNoTemplate->id,
        ]);
    }

    /** @test */
    public function it_strictly_blocks_material_item_creation_if_specification_does_not_match_template()
    {
        $this->grantAccessCode($this->user, 'material_item:own:create');

        $group = new MaterialGroup();
        $group->name = 'Lumber';
        $group->specification_template = '[Thickness:number]" x [Width:number]" x [Length:number]\' [WoodType:text]';
        $group->created_by = $this->user->id;
        $group->save();

        // Invalid: missing quotes and format
        $response = $this->actingAs($this->user->fresh())->postJson('/api/material/item/create', [
            'material_group_id' => $group->id,
            'name' => 'Good Lumber',
            'specification_unit_packaging' => '2 x 4 x 8 Pine',
            'brand' => 'Timberland',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => -2,
            'message' => 'Failed Validation',
        ]);

        $this->assertArrayHasKey('specification_unit_packaging', $response->json('data'));
    }

    /** @test */
    public function it_successfully_creates_material_item_when_specification_matches_template()
    {
        $this->grantAccessCode($this->user, 'material_item:own:create');

        $group = new MaterialGroup();
        $group->name = 'Lumber Valid';
        $group->specification_template = '[Thickness:number]" x [Width:number]" x [Length:number]\' [WoodType:text]';
        $group->created_by = $this->user->id;
        $group->save();

        $response = $this->actingAs($this->user->fresh())->postJson('/api/material/item/create', [
            'material_group_id' => $group->id,
            'name' => 'S4S Lumber',
            'specification_unit_packaging' => '2" x 4" x 8\' Pine Wood',
            'brand' => 'Timberland',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $this->assertDatabaseHas('material_items', [
            'name' => 'S4S Lumber',
            'material_group_id' => $group->id,
            'specification_unit_packaging' => '2" x 4" x 8\' Pine Wood',
        ]);
    }

    /** @test */
    public function it_strictly_blocks_material_item_update_if_group_lacks_template()
    {
        $this->grantAccessCode($this->user, 'material_item:own:update');

        $groupWithTemplate = new MaterialGroup();
        $groupWithTemplate->name = 'Old Group';
        $groupWithTemplate->specification_template = '[Weight:number] kg';
        $groupWithTemplate->created_by = $this->user->id;
        $groupWithTemplate->save();

        $item = new MaterialItem();
        $item->material_group_id = $groupWithTemplate->id;
        $item->name = 'Existing Item';
        $item->specification_unit_packaging = '25 kg';
        $item->brand = 'Old Brand';
        $item->created_by = $this->user->id;
        $item->save();

        $groupWithoutTemplate = new MaterialGroup();
        $groupWithoutTemplate->name = 'No Template Group';
        $groupWithoutTemplate->specification_template = null;
        $groupWithoutTemplate->created_by = $this->user->id;
        $groupWithoutTemplate->save();

        // Attempt to move item to a group without template
        $response = $this->actingAs($this->user->fresh())->postJson('/api/material/item/update', [
            'id' => $item->id,
            'material_group_id' => $groupWithoutTemplate->id,
            'name' => 'Existing Item',
            'specification_unit_packaging' => '25 kg',
            'brand' => 'Old Brand',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => -2,
            'message' => 'Failed Validation',
        ]);
        $this->assertArrayHasKey('specification_unit_packaging', $response->json('data'));
    }

    /** @test */
    public function it_strictly_blocks_material_item_update_if_specification_does_not_match_template()
    {
        $this->grantAccessCode($this->user, 'material_item:own:update');

        $group = new MaterialGroup();
        $group->name = 'Paints';
        $group->specification_template = '[Volume:number] Liter [Finish:text] Finish';
        $group->created_by = $this->user->id;
        $group->save();

        $item = new MaterialItem();
        $item->material_group_id = $group->id;
        $item->name = 'Acrylic Latex';
        $item->specification_unit_packaging = '4 Liter Gloss Finish';
        $item->brand = 'Boysen';
        $item->created_by = $this->user->id;
        $item->save();

        $response = $this->actingAs($this->user->fresh())->postJson('/api/material/item/update', [
            'id' => $item->id,
            'material_group_id' => $group->id,
            'name' => 'Acrylic Latex Updated',
            'specification_unit_packaging' => '4 Gal Gloss Finish', // Wrong volume unit!
            'brand' => 'Boysen',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => -2,
            'message' => 'Failed Validation',
        ]);
        $this->assertArrayHasKey('specification_unit_packaging', $response->json('data'));
    }

    /** @test */
    public function it_successfully_updates_material_item_when_specification_matches_template()
    {
        $this->grantAccessCode($this->user, 'material_item:own:update');

        $group = new MaterialGroup();
        $group->name = 'Paints 2';
        $group->specification_template = '[Volume:number] Liter [Finish:text] Finish';
        $group->created_by = $this->user->id;
        $group->save();

        $item = new MaterialItem();
        $item->material_group_id = $group->id;
        $item->name = 'Enamel Paint';
        $item->specification_unit_packaging = '1 Liter Matte Finish';
        $item->brand = 'Davies';
        $item->created_by = $this->user->id;
        $item->save();

        $response = $this->actingAs($this->user->fresh())->postJson('/api/material/item/update', [
            'id' => $item->id,
            'material_group_id' => $group->id,
            'name' => 'Enamel Paint Quick Dry',
            'specification_unit_packaging' => '3.78 Liter Semi-Gloss Finish',
            'brand' => 'Davies',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $item->refresh();
        $this->assertEquals('Enamel Paint Quick Dry', $item->name);
        $this->assertEquals('3.78 Liter Semi-Gloss Finish', $item->specification_unit_packaging);
    }
}
