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

        // 3. Test Artisan Command execution
        $this->artisan('protocol:run', [
            'protocol'  => 'level-1-budget-diagnostic',
            '--project' => $project->id,
        ])->assertExitCode(2);

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
}
