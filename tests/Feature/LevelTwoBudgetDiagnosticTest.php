<?php

namespace Tests\Feature;

use App\Models\Component;
use App\Models\ComponentItem;
use App\Models\ContractItem;
use App\Models\MaterialGroup;
use App\Models\MaterialItem;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialQuantityRequestItem;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Section;
use App\Models\Unit;
use App\Models\User;
use App\Protocols\LevelTwoBudgetDiagnostic;
use App\Protocols\ProtocolRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LevelTwoBudgetDiagnosticTest extends TestCase
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
    public function it_registers_level_two_budget_diagnostic_protocol()
    {
        $class = ProtocolRegistry::get('level-2-budget-diagnostic');
        $this->assertEquals(LevelTwoBudgetDiagnostic::class, $class);

        $classByName = ProtocolRegistry::get('Level 2 Budget Diagnostic');
        $this->assertEquals(LevelTwoBudgetDiagnostic::class, $classByName);
    }

    /** @test */
    public function it_returns_error_when_project_is_missing_or_not_found()
    {
        $result = ProtocolRegistry::run('level-2-budget-diagnostic', []);
        $this->assertFalse($result['passed']);
        $this->assertEquals('error', $result['status']);

        $resultNotFound = ProtocolRegistry::run('level-2-budget-diagnostic', ['project_id' => 9999999]);
        $this->assertFalse($resultNotFound['passed']);
        $this->assertEquals('error', $resultNotFound['status']);
    }

    /** @test */
    public function it_passes_when_po_quantities_and_amounts_are_within_budget()
    {
        $unit = Unit::create(['text' => 'Pcs', 'created_by' => 1]);
        $project = Project::create(['name' => 'Compliant Project', 'status' => 'ACTV', 'created_by' => 1]);
        $section = Section::create(['project_id' => $project->id, 'name' => 'Section A', 'created_by' => 1]);

        // Contract Item: 100 units @ 1,000 = 100,000 contract amount
        $contractItem = ContractItem::create([
            'section_id'          => $section->id,
            'item_code'           => '101',
            'description'         => 'Road Base',
            'unit_id'             => $unit->id,
            'contract_quantity'   => 100,
            'contract_unit_price' => 1000,
            'created_by'          => 1,
        ]);

        $component = Component::create([
            'section_id'       => $section->id,
            'contract_item_id' => $contractItem->id,
            'name'             => 'Pavement Layer',
            'unit_id'          => $unit->id,
            'quantity'         => 1,
            'status'           => 'APRV',
            'created_by'       => 1,
        ]);

        $componentItem = ComponentItem::create([
            'component_id'      => $component->id,
            'name'              => 'Aggregate Subbase',
            'unit_id'           => $unit->id,
            'quantity'          => 100,
            'budget_price'      => 500,
            'function_type_id'  => 3,
            'function_variable' => 1,
            'created_by'        => 1,
        ]);

        $group = MaterialGroup::create(['name' => 'Aggregates', 'created_by' => 1]);
        $materialItem = MaterialItem::create([
            'material_group_id' => $group->id,
            'name'              => 'Gravel G1',
            'brand'                         => 'Generic',
            'specification_unit_packaging'  => 'standard',
            'created_by'                    => 1,
        ]);

        // Approved MQR for 50 units
        $mqr = MaterialQuantityRequest::create([
            'project_id'       => $project->id,
            'section_id'       => $section->id,
            'contract_item_id' => $contractItem->id,
            'component_id'     => $component->id,
            'status'           => 'APRV',
            'description'      => 'Test MQR',
            'created_by'       => 1,
        ]);

        $mqrItem = MaterialQuantityRequestItem::create([
            'material_quantity_request_id' => $mqr->id,
            'component_item_id'            => $componentItem->id,
            'material_item_id'             => $materialItem->id,
            'requested_quantity'           => 50,
            'status'                       => 'APRV',
        ]);

        // Approved PO for 40 units @ 500 = 20,000 amount
        $po = PurchaseOrder::create([
            'project_id'                   => $project->id,
            'section_id'                   => $section->id,
            'contract_item_id'             => $contractItem->id,
            'component_id'                 => $component->id,
            'material_quantity_request_id' => $mqr->id,
            'supplier_id'                  => 1,
            'payment_term_id'              => 1,
            'status'                       => 'APRV',
            'created_by'                   => 1,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id'                 => $po->id,
            'component_item_id'                 => $componentItem->id,
            'material_quantity_request_item_id' => $mqrItem->id,
            'material_canvass_id'               => 1,
            'material_item_id'                  => $materialItem->id,
            'quantity'                          => 40,
            'price'                             => 500,
            'status'                            => 'APRV',
        ]);

        $result = LevelTwoBudgetDiagnostic::check($project->id);

        $this->assertEquals('success', $result['status']);
        $this->assertTrue($result['passed']);
        $this->assertEquals(0, $result['violations_count']);
        $this->assertEquals(1, count($result['contract_items_summary']));
        $this->assertEquals(20.0, $result['contract_items_summary'][0]['percentage_used']); // 20,000 / 100,000 = 20%
    }

    /** @test */
    public function it_fails_when_po_quantity_exceeds_mqr_quantity()
    {
        $unit = Unit::create(['text' => 'Pcs', 'created_by' => 1]);
        $project = Project::create(['name' => 'Quantity Violation Project', 'status' => 'ACTV', 'created_by' => 1]);
        $section = Section::create(['project_id' => $project->id, 'name' => 'Section A', 'created_by' => 1]);

        $contractItem = ContractItem::create([
            'section_id'          => $section->id,
            'item_code'           => '101',
            'description'         => 'Excavation',
            'unit_id'             => $unit->id,
            'contract_quantity'   => 1000,
            'contract_unit_price' => 500,
            'created_by'          => 1,
        ]);

        $component = Component::create([
            'section_id'       => $section->id,
            'contract_item_id' => $contractItem->id,
            'name'             => 'Earthworks',
            'unit_id'          => $unit->id,
            'quantity'         => 1,
            'status'           => 'APRV',
            'created_by'       => 1,
        ]);

        $componentItem = ComponentItem::create([
            'component_id'      => $component->id,
            'name'              => 'Sand Bedding',
            'unit_id'           => $unit->id,
            'quantity'          => 100,
            'budget_price'      => 100,
            'function_type_id'  => 3,
            'function_variable' => 1,
            'created_by'        => 1,
        ]);

        $group = MaterialGroup::create(['name' => 'Sand', 'created_by' => 1]);
        $materialItem = MaterialItem::create([
            'material_group_id' => $group->id,
            'name'              => 'Fine Sand',
            'brand'                         => 'Generic',
            'specification_unit_packaging'  => 'standard',
            'created_by'                    => 1,
        ]);

        // Approved MQR for 50 units
        $mqr = MaterialQuantityRequest::create([
            'project_id'       => $project->id,
            'section_id'       => $section->id,
            'contract_item_id' => $contractItem->id,
            'component_id'     => $component->id,
            'status'           => 'APRV',
            'description'      => 'Test MQR',
            'created_by'       => 1,
        ]);

        $mqrItem = MaterialQuantityRequestItem::create([
            'material_quantity_request_id' => $mqr->id,
            'component_item_id'            => $componentItem->id,
            'material_item_id'             => $materialItem->id,
            'requested_quantity'           => 50,
            'status'                       => 'APRV',
        ]);

        // PO 1 for 30 units (APRV)
        $po1 = PurchaseOrder::create([
            'project_id'                   => $project->id,
            'section_id'                   => $section->id,
            'contract_item_id'             => $contractItem->id,
            'component_id'                 => $component->id,
            'material_quantity_request_id' => $mqr->id,
            'supplier_id'                  => 1,
            'payment_term_id'              => 1,
            'status'                       => 'APRV',
            'created_by'                   => 1,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id'                 => $po1->id,
            'component_item_id'                 => $componentItem->id,
            'material_quantity_request_item_id' => $mqrItem->id,
            'material_canvass_id'               => 1,
            'material_item_id'                  => $materialItem->id,
            'quantity'                          => 30,
            'price'                             => 100,
            'status'                            => 'APRV',
        ]);

        // PO 2 for 25 units (APRV) -> Total = 55 (exceeds MQR of 50 by 5)
        $po2 = PurchaseOrder::create([
            'project_id'                   => $project->id,
            'section_id'                   => $section->id,
            'contract_item_id'             => $contractItem->id,
            'component_id'                 => $component->id,
            'material_quantity_request_id' => $mqr->id,
            'supplier_id'                  => 1,
            'payment_term_id'              => 1,
            'status'                       => 'APRV',
            'created_by'                   => 1,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id'                 => $po2->id,
            'component_item_id'                 => $componentItem->id,
            'material_quantity_request_item_id' => $mqrItem->id,
            'material_canvass_id'               => 1,
            'material_item_id'                  => $materialItem->id,
            'quantity'                          => 25,
            'price'                             => 100,
            'status'                            => 'APRV',
        ]);

        $result = LevelTwoBudgetDiagnostic::check($project->id);

        $this->assertEquals('success', $result['status']);
        $this->assertFalse($result['passed']);
        $this->assertEquals(1, $result['quantity_violations_count']);
        $this->assertEquals(0, $result['amount_violations_count']);

        $violation = $result['quantity_violations'][0];
        $this->assertEquals(50.0, $violation['allowed_mqr_quantity']);
        $this->assertEquals(55.0, $violation['total_po_quantity']);
        $this->assertEquals(5.0, $violation['over_ordered_quantity']);
        $this->assertEquals(2, $violation['purchase_orders_count']);
    }

    /** @test */
    public function it_fails_when_po_total_amount_exceeds_contract_item_amount()
    {
        $unit = Unit::create(['text' => 'Pcs', 'created_by' => 1]);
        $project = Project::create(['name' => 'Amount Violation Project', 'status' => 'ACTV', 'created_by' => 1]);
        $section = Section::create(['project_id' => $project->id, 'name' => 'Section A', 'created_by' => 1]);

        // Contract Item: 10 units @ 1,000 = 10,000 contract amount
        $contractItem = ContractItem::create([
            'section_id'          => $section->id,
            'item_code'           => '102',
            'description'         => 'Concrete Works',
            'unit_id'             => $unit->id,
            'contract_quantity'   => 10,
            'contract_unit_price' => 1000,
            'created_by'          => 1,
        ]);

        $component = Component::create([
            'section_id'       => $section->id,
            'contract_item_id' => $contractItem->id,
            'name'             => 'Column Concrete',
            'unit_id'          => $unit->id,
            'quantity'         => 1,
            'status'           => 'APRV',
            'created_by'       => 1,
        ]);

        $componentItem = ComponentItem::create([
            'component_id'      => $component->id,
            'name'              => 'Cement Bag',
            'unit_id'           => $unit->id,
            'quantity'          => 100,
            'budget_price'      => 300,
            'function_type_id'  => 3,
            'function_variable' => 1,
            'created_by'        => 1,
        ]);

        $group = MaterialGroup::create(['name' => 'Cement', 'created_by' => 1]);
        $materialItem = MaterialItem::create([
            'material_group_id' => $group->id,
            'name'              => 'Portland Cement',
            'brand'                         => 'Generic',
            'specification_unit_packaging'  => 'standard',
            'created_by'                    => 1,
        ]);

        // MQR for 50 units
        $mqr = MaterialQuantityRequest::create([
            'project_id'       => $project->id,
            'section_id'       => $section->id,
            'contract_item_id' => $contractItem->id,
            'component_id'     => $component->id,
            'status'           => 'APRV',
            'description'      => 'Test MQR',
            'created_by'       => 1,
        ]);

        $mqrItem = MaterialQuantityRequestItem::create([
            'material_quantity_request_id' => $mqr->id,
            'component_item_id'            => $componentItem->id,
            'material_item_id'             => $materialItem->id,
            'requested_quantity'           => 50,
            'status'                       => 'APRV',
        ]);

        // PO for 40 units @ 300 = 12,000 amount -> Exceeds Contract Item amount of 10,000!
        $po = PurchaseOrder::create([
            'project_id'                   => $project->id,
            'section_id'                   => $section->id,
            'contract_item_id'             => $contractItem->id,
            'component_id'                 => $component->id,
            'material_quantity_request_id' => $mqr->id,
            'supplier_id'                  => 1,
            'payment_term_id'              => 1,
            'status'                       => 'APRV',
            'created_by'                   => 1,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id'                 => $po->id,
            'component_item_id'                 => $componentItem->id,
            'material_quantity_request_item_id' => $mqrItem->id,
            'material_canvass_id'               => 1,
            'material_item_id'                  => $materialItem->id,
            'quantity'                          => 40,
            'price'                             => 300,
            'status'                            => 'APRV',
        ]);

        $result = LevelTwoBudgetDiagnostic::check($project->id);

        $this->assertEquals('success', $result['status']);
        $this->assertFalse($result['passed']);
        $this->assertEquals(0, $result['quantity_violations_count']);
        $this->assertEquals(1, $result['amount_violations_count']);

        $violation = $result['amount_violations'][0];
        $this->assertEquals(10000.0, $violation['contract_amount']);
        $this->assertEquals(12000.0, $violation['total_po_amount']);
        $this->assertEquals(120.0, $violation['percentage_used']); // 12,000 / 10,000 = 120%
        $this->assertEquals(2000.0, $violation['over_budget_amount']);
    }

    /** @test */
    public function it_ignores_non_approved_purchase_orders()
    {
        $unit = Unit::create(['text' => 'Pcs', 'created_by' => 1]);
        $project = Project::create(['name' => 'Pending PO Project', 'status' => 'ACTV', 'created_by' => 1]);
        $section = Section::create(['project_id' => $project->id, 'name' => 'Section A', 'created_by' => 1]);

        $contractItem = ContractItem::create([
            'section_id'          => $section->id,
            'item_code'           => '103',
            'description'         => 'Structural Steel',
            'unit_id'             => $unit->id,
            'contract_quantity'   => 10,
            'contract_unit_price' => 1000,
            'created_by'          => 1,
        ]);

        $component = Component::create([
            'section_id'       => $section->id,
            'contract_item_id' => $contractItem->id,
            'name'             => 'Steel Trusses',
            'unit_id'          => $unit->id,
            'quantity'         => 1,
            'status'           => 'APRV',
            'created_by'       => 1,
        ]);

        $componentItem = ComponentItem::create([
            'component_id'      => $component->id,
            'name'              => 'Steel Beam',
            'unit_id'           => $unit->id,
            'quantity'          => 10,
            'budget_price'      => 1000,
            'function_type_id'  => 3,
            'function_variable' => 1,
            'created_by'        => 1,
        ]);

        $group = MaterialGroup::create(['name' => 'Steel', 'created_by' => 1]);
        $materialItem = MaterialItem::create([
            'material_group_id' => $group->id,
            'name'              => 'Wide Flange Beam',
            'brand'                         => 'Generic',
            'specification_unit_packaging'  => 'standard',
            'created_by'                    => 1,
        ]);

        $mqr = MaterialQuantityRequest::create([
            'project_id'       => $project->id,
            'section_id'       => $section->id,
            'contract_item_id' => $contractItem->id,
            'component_id'     => $component->id,
            'status'           => 'APRV',
            'description'      => 'Test MQR',
            'created_by'       => 1,
        ]);

        $mqrItem = MaterialQuantityRequestItem::create([
            'material_quantity_request_id' => $mqr->id,
            'component_item_id'            => $componentItem->id,
            'material_item_id'             => $materialItem->id,
            'requested_quantity'           => 10,
            'status'                       => 'APRV',
        ]);

        // PO is PEND (Draft/Pending), so it should not be counted
        $po = PurchaseOrder::create([
            'project_id'                   => $project->id,
            'section_id'                   => $section->id,
            'contract_item_id'             => $contractItem->id,
            'component_id'                 => $component->id,
            'material_quantity_request_id' => $mqr->id,
            'supplier_id'                  => 1,
            'payment_term_id'              => 1,
            'status'                       => 'PEND',
            'created_by'                   => 1,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id'                 => $po->id,
            'component_item_id'                 => $componentItem->id,
            'material_quantity_request_item_id' => $mqrItem->id,
            'material_canvass_id'               => 1,
            'material_item_id'                  => $materialItem->id,
            'quantity'                          => 100, // huge over-order if it were counted!
            'price'                             => 5000,
            'status'                            => 'PEND',
        ]);

        $result = LevelTwoBudgetDiagnostic::check($project->id);

        $this->assertEquals('success', $result['status']);
        $this->assertTrue($result['passed']);
        $this->assertEquals(0, $result['violations_count']);
    }
}
