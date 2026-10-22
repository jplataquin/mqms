<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MaterialGroup;
use App\Models\MaterialItem;
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
}
