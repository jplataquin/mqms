<?php

namespace Tests\Feature;

use App\Models\Component;
use App\Models\ComponentItem;
use App\Models\ContractItem;
use App\Models\MaterialGroup;
use App\Models\MaterialItem;
use App\Models\MaterialQuantity;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialQuantityRequestItem;
use App\Models\Project;
use App\Models\Section;
use App\Models\Unit;
use App\Models\User;
use App\Protocols\LevelOneBudgetDiagnostic;
use App\Protocols\ProtocolRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LevelOneBudgetDiagnosticTest extends TestCase
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

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Database\Eloquent\Model::unguard();
    }

    /** @test */
    public function it_registers_level_one_budget_diagnostic_protocol()
    {
        $class = ProtocolRegistry::get('level-1-budget-diagnostic');
        $this->assertEquals(LevelOneBudgetDiagnostic::class, $class);

        $classByName = ProtocolRegistry::get('Level 1 Budget Diagnostic');
        $this->assertEquals(LevelOneBudgetDiagnostic::class, $classByName);
    }

    /** @test */
    public function it_returns_error_when_project_is_missing_or_not_found()
    {
        $result = ProtocolRegistry::run('level-1-budget-diagnostic', []);
        $this->assertFalse($result['passed']);
        $this->assertEquals('error', $result['status']);

        $resultNotFound = ProtocolRegistry::run('level-1-budget-diagnostic', ['project_id' => 9999999]);
        $this->assertFalse($resultNotFound['passed']);
        $this->assertEquals('error', $resultNotFound['status']);
    }

    /** @test */
    public function it_passes_when_approved_request_quantity_multiplied_by_equivalent_is_within_budget()
    {
        $unit = Unit::create([
            'text'       => 'Pcs',
            'created_by' => 1,
        ]);

        // 1. Setup project hierarchy
        $project = Project::create([
            'name'       => 'Test Diagnostic Project Within Budget',
            'status'     => 'ACTV',
            'created_by' => 1,
        ]);

        $section = Section::create([
            'project_id' => $project->id,
            'name'       => 'Test Section',
            'created_by' => 1,
        ]);

        $contractItem = ContractItem::create([
            'section_id'          => $section->id,
            'item_code'           => '101',
            'description'         => 'Test Contract Item',
            'unit_id'             => $unit->id,
            'contract_quantity'   => 10,
            'contract_unit_price' => 100,
            'created_by'          => 1,
        ]);

        $component = Component::create([
            'section_id'       => $section->id,
            'contract_item_id' => $contractItem->id,
            'name'             => 'Test Component',
            'unit_id'          => $unit->id,
            'quantity'         => 1,
            'status'           => 'APRV',
            'created_by'       => 1,
        ]);

        // Component item budget = 100
        $componentItem = ComponentItem::create([
            'component_id'      => $component->id,
            'name'              => 'Test Component Item',
            'unit_id'           => $unit->id,
            'quantity'          => 100,
            'budget_price'      => 50.00,
            'function_type_id'  => 3,
            'function_variable' => 1,
            'created_by'        => 1,
        ]);

        $group = MaterialGroup::create([
            'name'       => 'Test Group',
            'created_by' => 1,
        ]);

        $materialItem = MaterialItem::create([
            'material_group_id'             => $group->id,
            'name'                          => 'Test Material',
            'brand'                         => 'Brand X',
            'specification_unit_packaging'  => '100 pcs/box',
            'created_by'                    => 1,
        ]);

        // Equivalent = 2.5
        MaterialQuantity::create([
            'component_item_id' => $componentItem->id,
            'material_item_id'  => $materialItem->id,
            'quantity'          => 40,
            'equivalent'        => 2.5,
            'created_by'        => 1,
        ]);

        // Create MQR with requested_quantity = 30 (Actual: 30 * 2.5 = 75 <= 100)
        $mqr = MaterialQuantityRequest::create([
            'project_id'   => $project->id,
            'section_id'   => $section->id,
            'component_id' => $component->id,
            'status'       => 'APRV',
            'description'  => 'Test MQR Within Budget',
            'created_by'   => 1,
        ]);

        MaterialQuantityRequestItem::create([
            'material_quantity_request_id' => $mqr->id,
            'component_item_id'            => $componentItem->id,
            'material_item_id'             => $materialItem->id,
            'requested_quantity'           => 30,
            'status'                       => 'APRV',
        ]);

        // 2. Run protocol
        $result = LevelOneBudgetDiagnostic::check($project->id);

        $this->assertEquals('success', $result['status']);
        $this->assertTrue($result['passed']);
        $this->assertEquals(0, $result['violations_count']);
        $this->assertCount(0, $result['violations']);
    }

    /** @test */
    public function it_fails_and_reports_violation_when_equivalent_scaled_quantity_exceeds_budget()
    {
        $unit = Unit::create([
            'text'       => 'Pcs',
            'created_by' => 1,
        ]);

        // 1. Setup project hierarchy
        $project = Project::create([
            'name'       => 'Test Diagnostic Project Exceeded',
            'status'     => 'ACTV',
            'created_by' => 1,
        ]);

        $section = Section::create([
            'project_id' => $project->id,
            'name'       => 'Test Section Over Budget',
            'created_by' => 1,
        ]);

        $contractItem = ContractItem::create([
            'section_id'          => $section->id,
            'item_code'           => '102',
            'description'         => 'Test Contract Item 2',
            'unit_id'             => $unit->id,
            'contract_quantity'   => 10,
            'contract_unit_price' => 100,
            'created_by'          => 1,
        ]);

        $component = Component::create([
            'section_id'       => $section->id,
            'contract_item_id' => $contractItem->id,
            'name'             => 'Test Component Over Budget',
            'unit_id'          => $unit->id,
            'quantity'         => 1,
            'status'           => 'APRV',
            'created_by'       => 1,
        ]);

        // Component item budget = 50
        $componentItem = ComponentItem::create([
            'component_id'      => $component->id,
            'name'              => 'Test Component Item Exceeded',
            'unit_id'           => $unit->id,
            'quantity'          => 50,
            'budget_price'      => 20.00,
            'function_type_id'  => 3,
            'function_variable' => 1,
            'created_by'        => 1,
        ]);

        $group = MaterialGroup::create([
            'name'       => 'Test Group 2',
            'created_by' => 1,
        ]);

        $materialItem = MaterialItem::create([
            'material_group_id'             => $group->id,
            'name'                          => 'Test Over Material',
            'brand'                         => 'Brand Y',
            'specification_unit_packaging'  => '50 pcs/box',
            'created_by'                    => 1,
        ]);

        // Equivalent = 2.0
        MaterialQuantity::create([
            'component_item_id' => $componentItem->id,
            'material_item_id'  => $materialItem->id,
            'quantity'          => 25,
            'equivalent'        => 2.0,
            'created_by'        => 1,
        ]);

        // Create MQR with requested_quantity = 30 (Actual: 30 * 2.0 = 60 > 50)
        $mqr = MaterialQuantityRequest::create([
            'project_id'   => $project->id,
            'section_id'   => $section->id,
            'component_id' => $component->id,
            'status'       => 'APRV',
            'description'  => 'Test MQR Exceeded',
            'created_by'   => 1,
        ]);

        MaterialQuantityRequestItem::create([
            'material_quantity_request_id' => $mqr->id,
            'component_item_id'            => $componentItem->id,
            'material_item_id'             => $materialItem->id,
            'requested_quantity'           => 30,
            'status'                       => 'APRV',
        ]);

        // 2. Run protocol
        $result = LevelOneBudgetDiagnostic::check($project->id);

        $this->assertEquals('success', $result['status']);
        $this->assertFalse($result['passed']);
        $this->assertEquals(1, $result['violations_count']);
        $this->assertCount(1, $result['violations']);

        $violation = $result['violations'][0];
        $this->assertEquals($componentItem->id, $violation['component_item_id']);
        $this->assertEquals(50.0, $violation['allowed_budget']);
        $this->assertEquals(60.0, $violation['total_requested']);
        $this->assertEquals(10.0, $violation['over_budget_quantity']);
        $this->assertEquals($contractItem->id, $violation['contract_item_id']);
        $this->assertEquals('102', $violation['contract_item_code']);
        $this->assertEquals('Component Item', $violation['record_type']);
        $this->assertEquals('/project/section/contract_item/component/' . $component->id, $violation['component_url']);
        $this->assertEquals('/project/section/contract_item/' . $contractItem->id, $violation['contract_item_url']);

        // 3. Test Artisan Command execution
        $this->artisan('protocol:run', [
            'protocol'  => 'level-1-budget-diagnostic',
            '--project' => $project->id,
        ])
            ->expectsOutputToContain('Contract Item')
            ->doesntExpectOutputToContain('Component Item')
            ->assertExitCode(2);

        // 4. Test Artisan Command with --json option
        $this->artisan('protocol:run', [
            'protocol'  => 'level-1-budget-diagnostic',
            '--project' => $project->id,
            '--json'    => true,
        ])->assertExitCode(2);
    }

    /** @test */
    public function it_ignores_soft_deleted_material_quantity_requests()
    {
        $unit = Unit::create([
            'text'       => 'Pcs',
            'created_by' => 1,
        ]);

        $project = Project::create([
            'name'       => 'Test Project Soft Delete',
            'status'     => 'ACTV',
            'created_by' => 1,
        ]);

        $section = Section::create([
            'project_id' => $project->id,
            'name'       => 'Section 1',
            'created_by' => 1,
        ]);

        $contractItem = ContractItem::create([
            'section_id'          => $section->id,
            'item_code'           => '103',
            'description'         => 'Contract Item 3',
            'unit_id'             => $unit->id,
            'contract_quantity'   => 10,
            'contract_unit_price' => 100,
            'created_by'          => 1,
        ]);

        $component = Component::create([
            'section_id'       => $section->id,
            'contract_item_id' => $contractItem->id,
            'name'             => 'Component 3',
            'unit_id'          => $unit->id,
            'quantity'         => 1,
            'status'           => 'APRV',
            'created_by'       => 1,
        ]);

        $componentItem = ComponentItem::create([
            'component_id'      => $component->id,
            'name'              => 'Component Item 3',
            'unit_id'           => $unit->id,
            'quantity'          => 50,
            'budget_price'      => 20.00,
            'function_type_id'  => 3,
            'function_variable' => 1,
            'created_by'        => 1,
        ]);

        $group = MaterialGroup::create([
            'name'       => 'Group 3',
            'created_by' => 1,
        ]);

        $materialItem = MaterialItem::create([
            'material_group_id'             => $group->id,
            'name'                          => 'Material 3',
            'brand'                         => 'Brand Z',
            'specification_unit_packaging'  => 'Box',
            'created_by'                    => 1,
        ]);

        MaterialQuantity::create([
            'component_item_id' => $componentItem->id,
            'material_item_id'  => $materialItem->id,
            'quantity'          => 50,
            'equivalent'        => 1.0,
            'created_by'        => 1,
        ]);

        // Create MQR with requested_quantity = 100, but soft delete it
        $mqr = MaterialQuantityRequest::create([
            'project_id'   => $project->id,
            'section_id'   => $section->id,
            'component_id' => $component->id,
            'status'       => 'APRV',
            'description'  => 'Deleted MQR',
            'created_by'   => 1,
        ]);

        MaterialQuantityRequestItem::create([
            'material_quantity_request_id' => $mqr->id,
            'component_item_id'            => $componentItem->id,
            'material_item_id'             => $materialItem->id,
            'requested_quantity'           => 100,
            'status'                       => 'APRV',
        ]);

        // Soft delete the MQR
        $mqr->delete();

        $result = LevelOneBudgetDiagnostic::check($project->id);
        $this->assertTrue($result['passed']);
        $this->assertEquals(0, $result['violations_count']);
    }

    /** @test */
    public function it_seeds_project_data_and_verifies_level_one_budget_diagnostic()
    {
        $this->seed(\Database\Seeders\LevelOneBudgetDiagnosticSeeder::class);

        $project = Project::latest('id')->first();
        $this->assertNotNull($project);

        $result = LevelOneBudgetDiagnostic::check($project->id);

        $this->assertEquals('success', $result['status']);
        $this->assertFalse($result['passed']);
        $this->assertEquals(1, $result['violations_count']);
        $this->assertEquals(2, $result['total_items']);

        $violation = $result['violations'][0];
        $this->assertEquals('16mm Deformed Bar Budget Limit', $violation['component_item_name']);
        $this->assertEquals(100.0, $violation['allowed_budget']);
        $this->assertEquals(120.0, $violation['total_requested']); // 60 * 2.0 = 120
        $this->assertEquals(20.0, $violation['over_budget_quantity']);
    }

    /** @test */
    public function it_can_render_the_protocols_dashboard_page()
    {
        $user = User::create([
            'name'     => 'Tester',
            'email'    => 'tester@mqms.local',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->get('/protocols');
        $response->assertStatus(200);
        $response->assertSee('Protocols Diagnostic Dashboard');
        $response->assertSee('Contract Item');
        $response->assertSee('id="protocolSelect"', false);
        $response->assertSee('id="projectSelect"', false);
        $response->assertSee('id="runBtn"', false);
        $response->assertSee('id="topPrintBtn"', false);
        $response->assertSee('id="printBtn"', false);
        $response->assertDontSee('id="manualProjectIdInput"', false);
    }

    /** @test */
    public function it_renders_combined_card_header_with_contract_item_and_budget_metrics_without_record_type_in_result_card()
    {
        $user = User::create([
            'name'     => 'Card Header Tester',
            'email'    => 'header_tester@mqms.local',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->get('/protocols');
        $response->assertStatus(200);

        // Result cards in violationsList should render the combined card header without Record Type
        $content = $response->getContent();
        $this->assertStringContainsString('// Combined Card Header', $content);
        $this->assertStringContainsString("t.div({ class: 'card-header border-secondary p-3' }", $content);
        $this->assertStringContainsString('Contract Item:', $content);
        $this->assertStringNotContainsString('Record Type:', $content);
        $this->assertStringContainsString('Allowed Budget', $content);
        $this->assertStringContainsString('Total Requested (Actual)', $content);
        $this->assertStringContainsString('Over Budget Variance', $content);
        $this->assertStringContainsString('Variance Status', $content);
        $this->assertStringContainsString("'Contract Item:'", $content);
        $this->assertStringContainsString("'Component:'", $content);
    }

    /** @test */
    public function it_renders_equivalent_column_and_omits_date_needed_and_factor_in_protocols_view()
    {
        $user = User::create([
            'name'     => 'Table Header Tester',
            'email'    => 'table_tester@mqms.local',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->get('/protocols');
        $response->assertStatus(200);

        $content = $response->getContent();
        // Equivalent column is present
        $this->assertStringContainsString("'Equivalent'", $content);
        // Factor column is removed
        $this->assertStringNotContainsString("'Factor'", $content);
        // Date needed column is removed
        $this->assertStringNotContainsString("'Date Needed'", $content);
        // Component field is present below contract item in card header
        $contractItemPos = strpos($content, "'Contract Item:'");
        $componentPos = strpos($content, "'Component:'");
        $this->assertNotFalse($contractItemPos);
        $this->assertNotFalse($componentPos);
        $this->assertTrue($componentPos > $contractItemPos, 'Component field should appear after Contract Item field in header');
    }

    /** @test */
    public function it_renders_text_searchable_project_field_with_datalist_options_on_protocols_page()
    {
        $user = User::create([
            'name'     => 'Search Tester',
            'email'    => 'search_tester@mqms.local',
            'password' => bcrypt('password'),
        ]);

        $project = Project::create([
            'name'       => 'Searchable Coastal Highway Project',
            'status'     => 'ACTV',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/protocols');
        $response->assertStatus(200);

        // Searchable Project field components
        $response->assertSee('id="projectInput"', false);
        $response->assertSee('list="projectList"', false);
        $response->assertSee('id="projectList"', false);
        $response->assertSee('id="clearProjectBtn"', false);
        $response->assertSee('id="projectSelect"', false);
        $response->assertSee('Searchable Coastal Highway Project');
        $response->assertSee("#{$project->id} - {$project->name}");
    }

    /** @test */
    public function it_can_render_the_dedicated_protocol_print_page()
    {
        $user = User::create([
            'name'     => 'Print Tester',
            'email'    => 'print_tester@mqms.local',
            'password' => bcrypt('password'),
        ]);

        $this->seed(\Database\Seeders\LevelOneBudgetDiagnosticSeeder::class);
        $project = Project::latest('id')->first();

        // 1. Level 1 Diagnostic Print Page
        $response = $this->actingAs($user)->get("/protocols/print?protocol=level-1-budget-diagnostic&project_id={$project->id}");
        $response->assertStatus(200);
        $response->assertSee('PROTOCOL');
        $response->assertSee('Level 1 Budget Diagnostic');
        $response->assertSee('NON-CONFORMING');
        $response->assertSee('Print Report');
        $response->assertSee('Non-Conforming Items (Exceeding Budget)');
        $response->assertSee('MQR ID');
        $response->assertSee('Equivalent');
        $response->assertSee('Charged Qty');

        $content = $response->getContent();
        $this->assertStringNotContainsString('Component Item</th>', $content);
        $this->assertStringContainsString('Contract Item</th>', $content);
        $this->assertStringContainsString('Component</th>', $content);
        $contractItemPos = strpos($content, 'Contract Item</th>');
        $componentPos = strpos($content, 'Component</th>');
        $this->assertTrue($contractItemPos !== false && $componentPos !== false && $contractItemPos < $componentPos);

        // 2. Level 2 Diagnostic Print Page
        $responseL2 = $this->actingAs($user)->get("/protocols/print?protocol=level-2-budget-diagnostic&project_id={$project->id}");
        $responseL2->assertStatus(200);
        $responseL2->assertSee('PROTOCOL');
        $responseL2->assertSee('Level 2 Budget Diagnostic');
        $responseL2->assertSee('Part 1: Purchase Order Quantities vs MQR Requisitions');
        $responseL2->assertSee('Part 2: Contract Item Financial Utilization');
    }

    /** @test */
    public function it_can_list_protocols_via_api()
    {
        $user = User::create([
            'name'     => 'Tester 2',
            'email'    => 'tester2@mqms.local',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->getJson('/api/protocol/list');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'data'   => [
                [
                    'slug' => 'level-1-budget-diagnostic',
                    'name' => 'Level 1 Budget Diagnostic',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_run_protocol_via_api()
    {
        $this->seed(\Database\Seeders\LevelOneBudgetDiagnosticSeeder::class);
        $project = Project::latest('id')->first();

        $user = User::first();

        $response = $this->actingAs($user)->postJson('/api/protocol/run', [
            'protocol'   => 'level-1-budget-diagnostic',
            'project_id' => $project->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'data'   => [
                'passed'           => false,
                'violations_count' => 1,
            ],
        ]);
    }

    /** @test */
    public function it_provides_traceability_to_material_quantity_requests_in_non_conforming_results()
    {
        $this->seed(\Database\Seeders\LevelOneBudgetDiagnosticSeeder::class);
        $project = Project::latest('id')->first();
        $user = User::first();

        $response = $this->actingAs($user)->postJson('/api/protocol/run', [
            'protocol'   => 'level-1-budget-diagnostic',
            'project_id' => $project->id,
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertFalse($data['passed']);
        $this->assertNotEmpty($data['violations']);

        $firstViolation = $data['violations'][0];
        $this->assertArrayHasKey('component_url', $firstViolation);
        $this->assertStringContainsString('/project/section/contract_item/component/', $firstViolation['component_url']);
        $this->assertNotEmpty($firstViolation['breakdown']);

        $firstBreakdown = $firstViolation['breakdown'][0];
        $this->assertArrayHasKey('material_quantity_request_id', $firstBreakdown);
        $this->assertArrayHasKey('material_quantity_request_url', $firstBreakdown);
        $this->assertStringContainsString('/material_quantity_request/', $firstBreakdown['material_quantity_request_url']);
        $this->assertArrayHasKey('material_item_name', $firstBreakdown);
        $this->assertNotEmpty($firstBreakdown['material_item_name']);
    }
}
