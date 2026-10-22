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

class MaterialItemTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $group1;
    protected $group2;
    protected $item1;
    protected $item2;
    protected $item3;

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

        $this->group1 = new MaterialGroup();
        $this->group1->name = 'Cementitious Materials';
        $this->group1->created_by = $this->user->id;
        $this->group1->save();

        $this->group2 = new MaterialGroup();
        $this->group2->name = 'Aggregates';
        $this->group2->created_by = $this->user->id;
        $this->group2->save();

        $this->item1 = new MaterialItem();
        $this->item1->material_group_id = $this->group1->id;
        $this->item1->name = 'Portland Cement Type 1';
        $this->item1->specification_unit_packaging = '40kg bag';
        $this->item1->brand = 'Holcim';
        $this->item1->created_by = $this->user->id;
        $this->item1->save();

        $this->item2 = new MaterialItem();
        $this->item2->material_group_id = $this->group1->id;
        $this->item2->name = 'Pozzolan Cement';
        $this->item2->specification_unit_packaging = '40kg bag';
        $this->item2->brand = 'Republic';
        $this->item2->created_by = $this->user->id;
        $this->item2->save();

        $this->item3 = new MaterialItem();
        $this->item3->material_group_id = $this->group2->id;
        $this->item3->name = 'Crushed Gravel G1';
        $this->item3->specification_unit_packaging = 'cu.m.';
        $this->item3->brand = 'Local Quarry';
        $this->item3->created_by = $this->user->id;
        $this->item3->save();
    }

    /** @test */
    public function it_can_render_the_material_items_list_page()
    {
        $response = $this->actingAs($this->user)->get('/master_data/material/items');
        $response->assertStatus(200);
        $response->assertSee('Material Items');
        $response->assertSee('id="materialGroupSelect"', false);
        $response->assertSee('Cementitious Materials');
        $response->assertSee('Aggregates');
        $response->assertSee('id="printBtn"', false);
        $response->assertSee('id="searchBtn"', false);
        $response->assertSee('id="resetBtn"', false);
        $response->assertSee('id="createBtn"', false);
        $response->assertSee('master_data/material/items/print', false);
    }

    /** @test */
    public function it_preselects_material_group_filter_if_provided_in_query()
    {
        $response = $this->actingAs($this->user)->get('/master_data/material/items?material_group_id=' . $this->group2->id);
        $response->assertStatus(200);
        $response->assertSee('value="' . $this->group2->id . '" selected', false);
    }

    /** @test */
    public function it_can_filter_material_items_by_material_group_via_api()
    {
        $response = $this->actingAs($this->user)->getJson('/api/material/item/list?material_group_id=' . $this->group1->id);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
        ]);
        
        $data = $response->json('data');
        $this->assertCount(2, $data);
        $names = array_column($data, 'name');
        $this->assertContains('Portland Cement Type 1', $names);
        $this->assertContains('Pozzolan Cement', $names);
        $this->assertNotContains('Crushed Gravel G1', $names);

        // Verify group relation is present in JSON
        $this->assertEquals('Cementitious Materials', $data[0]['group']['name']);

        // Check group 2
        $response2 = $this->actingAs($this->user)->getJson('/api/material/item/list?material_group_id=' . $this->group2->id);
        $response2->assertStatus(200);
        $data2 = $response2->json('data');
        $this->assertCount(1, $data2);
        $this->assertEquals('Crushed Gravel G1', $data2[0]['name']);
        $this->assertEquals('Aggregates', $data2[0]['group']['name']);
    }

    /** @test */
    public function it_can_filter_material_items_by_group_in_print_view()
    {
        $response = $this->actingAs($this->user)->get('/master_data/material/items/print?material_group_id=' . $this->group1->id);
        $response->assertStatus(200);
        $response->assertSee('Master Data: Material Items');
        $response->assertSee('Material Group: Cementitious Materials');
        $response->assertSee('Portland Cement Type 1');
        $response->assertSee('Pozzolan Cement');
        $response->assertDontSee('Crushed Gravel G1');
    }

    /** @test */
    public function it_can_render_the_printable_material_items_page()
    {
        $response = $this->actingAs($this->user)->get('/master_data/material/items/print');
        $response->assertStatus(200);
        $response->assertSee('Master Data: Material Items');
        $response->assertSee('Cementitious Materials');
        $response->assertSee('Portland Cement Type 1');
        $response->assertSee('Holcim');
        $response->assertSee('40kg bag');
        $response->assertSee('Aggregates');
        $response->assertSee('Crushed Gravel G1');
        $response->assertSee('window.print()', false);
    }

    /** @test */
    public function it_can_render_the_printable_page_via_alias_singular_route()
    {
        $response = $this->actingAs($this->user)->get('/master_data/material/item/print');
        $response->assertStatus(200);
        $response->assertSee('Master Data: Material Items');
        $response->assertSee('Portland Cement Type 1');
    }

    /** @test */
    public function it_can_filter_material_items_by_query_in_print_view()
    {
        $response = $this->actingAs($this->user)->get('/master_data/material/items/print?query=Portland');
        $response->assertStatus(200);
        $response->assertSee('Portland Cement Type 1');
        $response->assertDontSee('Crushed Gravel G1');
        $response->assertSee('Search Filter: "Portland"', false);
    }

    /** @test */
    public function it_can_sort_material_items_in_print_view()
    {
        $response = $this->actingAs($this->user)->get('/master_data/material/items/print?order_by=name&order=asc');
        $response->assertStatus(200);
        $content = $response->getContent();
        
        $posGravel = strpos($content, 'Crushed Gravel G1');
        $posPortland = strpos($content, 'Portland Cement Type 1');
        $posPozzolan = strpos($content, 'Pozzolan Cement');

        $this->assertTrue($posGravel < $posPortland);
        $this->assertTrue($posPortland < $posPozzolan);
    }

    /** @test */
    public function it_loads_group_relationship_correctly()
    {
        $item = MaterialItem::with('Group')->find($this->item1->id);
        $this->assertNotNull($item->Group);
        $this->assertEquals('Cementitious Materials', $item->Group->name);
    }

    /** @test */
    public function it_groups_material_items_by_material_group_in_print_view()
    {
        $response = $this->actingAs($this->user)->get('/master_data/material/items/print');
        $response->assertStatus(200);

        $groupedItems = $response->viewData('groupedMaterialItems');
        $this->assertNotNull($groupedItems);
        $this->assertCount(2, $groupedItems);

        $content = $response->getContent();

        // Check group headers with item counts
        $this->assertStringContainsString('Aggregates', $content);
        $this->assertStringContainsString('(1 item)', $content);
        $this->assertStringContainsString('Cementitious Materials', $content);
        $this->assertStringContainsString('(2 items)', $content);
        $this->assertStringContainsString('Total Groups: 2', $content);
        $this->assertStringContainsString('Total Items: 3', $content);

        // Verify group header container and dedicated tables
        $this->assertStringContainsString('class="group-header"', $content);
        $this->assertStringContainsString('class="group-table"', $content);

        // Verify Material Group column is removed from table headers
        $this->assertStringNotContainsString('Material Group</th>', $content);

        // Verify group order: Aggregates precedes Cementitious Materials
        $posAggregates = strpos($content, 'Aggregates');
        $posCementitious = strpos($content, 'Cementitious Materials');
        $this->assertTrue($posAggregates < $posCementitious);

        // Verify items appear under their respective group sections
        $posGravel = strpos($content, 'Crushed Gravel G1');
        $posPortland = strpos($content, 'Portland Cement Type 1');
        $posPozzolan = strpos($content, 'Pozzolan Cement');

        $this->assertTrue($posAggregates < $posGravel);
        $this->assertTrue($posGravel < $posCementitious);
        $this->assertTrue($posCementitious < $posPortland);
        $this->assertTrue($posCementitious < $posPozzolan);
    }

    /** @test */
    public function it_renders_items_in_alphabetical_order_under_each_material_group_header()
    {
        // Add more items with out-of-order names to group 1
        $itemZ = new MaterialItem();
        $itemZ->material_group_id = $this->group1->id;
        $itemZ->name = 'White Portland Cement';
        $itemZ->specification_unit_packaging = '40kg bag';
        $itemZ->brand = 'Federal';
        $itemZ->created_by = $this->user->id;
        $itemZ->save();

        $itemA = new MaterialItem();
        $itemA->material_group_id = $this->group1->id;
        $itemA->name = 'Aluminous Cement';
        $itemA->specification_unit_packaging = '40kg bag';
        $itemA->brand = 'Kerneos';
        $itemA->created_by = $this->user->id;
        $itemA->save();

        $response = $this->actingAs($this->user)->get('/master_data/material/items/print');
        $response->assertStatus(200);

        $content = $response->getContent();

        // Under Cementitious Materials, items must appear in alphabetical order:
        // 1. Aluminous Cement
        // 2. Portland Cement Type 1
        // 3. Pozzolan Cement
        // 4. White Portland Cement
        $posAluminous = strpos($content, 'Aluminous Cement');
        $posPortland = strpos($content, 'Portland Cement Type 1');
        $posPozzolan = strpos($content, 'Pozzolan Cement');
        $posWhite = strpos($content, 'White Portland Cement');

        $this->assertTrue($posAluminous < $posPortland, 'Aluminous should precede Portland');
        $this->assertTrue($posPortland < $posPozzolan, 'Portland should precede Pozzolan');
        $this->assertTrue($posPozzolan < $posWhite, 'Pozzolan should precede White Portland');
    }

    /** @test */
    public function it_handles_ungrouped_material_items_in_print_view()
    {
        $ungroupedItem = new MaterialItem();
        $ungroupedItem->material_group_id = 0;
        $ungroupedItem->name = 'Loose Rebar Tie Wire';
        $ungroupedItem->specification_unit_packaging = 'kg';
        $ungroupedItem->brand = 'Generic';
        $ungroupedItem->created_by = $this->user->id;
        $ungroupedItem->save();

        $response = $this->actingAs($this->user)->get('/master_data/material/items/print');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('Ungrouped', $content);
        $this->assertStringContainsString('Loose Rebar Tie Wire', $content);

        // Ungrouped section should be at the end
        $posCementitious = strpos($content, 'Cementitious Materials');
        $posUngrouped = strpos($content, 'Ungrouped');
        $this->assertTrue($posCementitious < $posUngrouped);
    }

    /** @test */
    public function it_can_filter_by_material_group_in_print_view()
    {
        $response = $this->actingAs($this->user)->get('/master_data/material/items/print?material_group_id=' . $this->group1->id);
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('Cementitious Materials', $content);
        $this->assertStringContainsString('Material Group: Cementitious Materials', $content);
        $this->assertStringContainsString('Portland Cement Type 1', $content);
        $this->assertStringContainsString('Pozzolan Cement', $content);
        $this->assertStringNotContainsString('Aggregates', $content);
        $this->assertStringNotContainsString('Crushed Gravel G1', $content);
        $this->assertStringContainsString('Total Groups: 1', $content);
        $this->assertStringContainsString('Total Items: 2', $content);
    }

    /** @test */
    public function it_renders_empty_state_when_no_material_items_found()
    {
        $response = $this->actingAs($this->user)->get('/master_data/material/items/print?query=NonExistentQuery');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('No material items found.', $content);
        $this->assertStringContainsString('Total Items: 0', $content);
        $this->assertStringContainsString('Total Groups: 0', $content);
    }

    /** @test */
    public function it_can_render_the_material_items_maintenance_page()
    {
        $response = $this->actingAs($this->user)->get('/master_data/material/items/maintenance');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('Material Items Maintenance', $content);
        $this->assertStringContainsString('Aggregates', $content);
        $this->assertStringContainsString('Cementitious Materials', $content);
        $this->assertStringContainsString('Material Group</th>', $content);
        $this->assertStringContainsString('btn-edit', $content);
        $this->assertStringContainsString('edit-group', $content);
        $this->assertStringContainsString('edit-name', $content);
        $this->assertStringContainsString('Portland Cement Type 1', $content);
        $this->assertStringContainsString('Crushed Gravel G1', $content);

        $allGroups = $response->viewData('allMaterialGroups');
        $this->assertNotNull($allGroups);
        $this->assertCount(2, $allGroups);
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

        $role = $user->Roles->first();
        if (!$role) {
            $role = new Role();
            $role->name = 'Test Role ' . uniqid();
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

    /** @test */
    public function it_can_update_material_item_via_api()
    {
        $this->grantAccessCode($this->user, 'material_item:own:update');

        $response = $this->actingAs($this->user->fresh())->postJson('/api/material/item/update', [
            'id' => $this->item1->id,
            'material_group_id' => $this->group2->id,
            'name' => 'Updated Portland Cement',
            'specification_unit_packaging' => '50kg bag',
            'brand' => 'Holcim Premium',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $this->assertDatabaseHas('material_items', [
            'id' => $this->item1->id,
            'material_group_id' => $this->group2->id,
            'name' => 'Updated Portland Cement',
            'specification_unit_packaging' => '50kg bag',
            'brand' => 'Holcim Premium',
        ]);
    }

    /** @test */
    public function it_paginates_material_items_maintenance_view()
    {
        // Create 60 items to exceed the 50 per page limit
        for ($i = 4; $i <= 65; $i++) {
            $item = new MaterialItem();
            $item->material_group_id = $this->group1->id;
            $item->name = 'Bulk Cement Item ' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $item->specification_unit_packaging = '40kg bag';
            $item->brand = 'Test Brand';
            $item->created_by = $this->user->id;
            $item->save();
        }

        $response = $this->actingAs($this->user)->get('/master_data/material/items/maintenance');
        $response->assertStatus(200);

        $paginator = $response->viewData('paginator');
        $this->assertNotNull($paginator);
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $paginator);
        $this->assertTrue($paginator->hasPages());

        $content = $response->getContent();
        // Assert Bootstrap 5 pagination is used
        $this->assertStringContainsString('pagination', $content);
        $this->assertStringContainsString('page-item', $content);
        $this->assertStringContainsString('page-link', $content);
        // Assert Tailwind SVG w-5 h-5 is NOT present
        $this->assertStringNotContainsString('w-5 h-5', $content);
    }
}
