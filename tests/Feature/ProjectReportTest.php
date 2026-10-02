<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\ComponentItem;
use App\Models\Unit;
use App\Models\MaterialGroup;
use App\Models\MaterialItem;
use App\Models\MaterialQuantity;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialQuantityRequestItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\PaymentTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectReportTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;
    protected $section;
    protected $contractItem;
    protected $component;
    protected $unitPcs;
    protected $unitBags;
    protected $materialGroupConstruction;
    protected $materialGroupAggregates;
    protected $materialCement;
    protected $materialSand;

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

        $this->user = User::factory()->create();

        $this->unitPcs = new Unit();
        $this->unitPcs->text = 'Pcs';
        $this->unitPcs->created_by = $this->user->id;
        $this->unitPcs->save();

        $this->unitBags = new Unit();
        $this->unitBags->text = 'Bags';
        $this->unitBags->created_by = $this->user->id;
        $this->unitBags->save();

        $this->materialGroupConstruction = new MaterialGroup();
        $this->materialGroupConstruction->name = 'Construction Materials';
        $this->materialGroupConstruction->created_by = $this->user->id;
        $this->materialGroupConstruction->save();

        $this->materialGroupAggregates = new MaterialGroup();
        $this->materialGroupAggregates->name = 'Aggregates';
        $this->materialGroupAggregates->created_by = $this->user->id;
        $this->materialGroupAggregates->save();

        $this->materialCement = new MaterialItem();
        $this->materialCement->material_group_id = $this->materialGroupConstruction->id;
        $this->materialCement->name = 'Portland Cement';
        $this->materialCement->specification_unit_packaging = '40kg Bag';
        $this->materialCement->brand = 'Holcim';
        $this->materialCement->created_by = $this->user->id;
        $this->materialCement->save();

        $this->materialSand = new MaterialItem();
        $this->materialSand->material_group_id = $this->materialGroupAggregates->id;
        $this->materialSand->name = 'Washed Sand';
        $this->materialSand->specification_unit_packaging = 'cu.m';
        $this->materialSand->brand = 'Local';
        $this->materialSand->created_by = $this->user->id;
        $this->materialSand->save();

        $this->project = new Project();
        $this->project->name = 'Test Project Report';
        $this->project->status = 'ACTV';
        $this->project->created_by = $this->user->id;
        $this->project->save();

        $this->section = new Section();
        $this->section->project_id = $this->project->id;
        $this->section->name = 'Main Building';
        $this->section->gross_total_amount = 1000000.00;
        $this->section->created_by = $this->user->id;
        $this->section->save();

        $this->contractItem = new ContractItem();
        $this->contractItem->section_id = $this->section->id;
        $this->contractItem->item_type = 'MAT';
        $this->contractItem->item_code = '100';
        $this->contractItem->description = 'Concrete Works';
        $this->contractItem->contract_quantity = 100;
        $this->contractItem->unit_id = $this->unitBags->id;
        $this->contractItem->contract_unit_price = 1000.00;
        $this->contractItem->created_by = $this->user->id;
        $this->contractItem->save();

        $this->component = new Component();
        $this->component->name = 'Footings';
        $this->component->contract_item_id = $this->contractItem->id;
        $this->component->quantity = 10;
        $this->component->unit_id = $this->unitBags->id;
        $this->component->use_count = 1;
        $this->component->status = 'APRV';
        $this->component->section_id = $this->section->id;
        $this->component->created_by = $this->user->id;
        $this->component->save();
    }

    /** @test */
    public function it_can_generate_project_report_with_materials_summary_grouped_by_material_group()
    {
        // Component Item 1: Bags unit, Cement material (quantity = 50)
        $compItem1 = new ComponentItem();
        $compItem1->component_id = $this->component->id;
        $compItem1->name = 'Footing Concreting Part A';
        $compItem1->unit_id = $this->unitBags->id;
        $compItem1->quantity = 10;
        $compItem1->budget_price = 200.00;
        $compItem1->function_type_id = 3;
        $compItem1->function_variable = 10;
        $compItem1->created_by = $this->user->id;
        $compItem1->save();

        $mq1 = new MaterialQuantity();
        $mq1->component_item_id = $compItem1->id;
        $mq1->material_item_id = $this->materialCement->id;
        $mq1->quantity = 50;
        $mq1->equivalent = 1;
        $mq1->created_by = $this->user->id;
        $mq1->save();

        // Component Item 2: Same unit (Bags), same Cement material (quantity = 30) -> Should group with Item 1 under Construction Materials
        $compItem2 = new ComponentItem();
        $compItem2->component_id = $this->component->id;
        $compItem2->name = 'Footing Concreting Part B';
        $compItem2->unit_id = $this->unitBags->id;
        $compItem2->quantity = 10;
        $compItem2->budget_price = 200.00;
        $compItem2->function_type_id = 3;
        $compItem2->function_variable = 10;
        $compItem2->created_by = $this->user->id;
        $compItem2->save();

        $mq2 = new MaterialQuantity();
        $mq2->component_item_id = $compItem2->id;
        $mq2->material_item_id = $this->materialCement->id;
        $mq2->quantity = 30;
        $mq2->equivalent = 1;
        $mq2->created_by = $this->user->id;
        $mq2->save();

        // Component Item 3: Different unit (Pcs), same Cement material (quantity = 20) -> Distinct item under Construction Materials
        $compItem3 = new ComponentItem();
        $compItem3->component_id = $this->component->id;
        $compItem3->name = 'Precast blocks';
        $compItem3->unit_id = $this->unitPcs->id;
        $compItem3->quantity = 20;
        $compItem3->budget_price = 150.00;
        $compItem3->function_type_id = 3;
        $compItem3->function_variable = 20;
        $compItem3->created_by = $this->user->id;
        $compItem3->save();

        $mq3 = new MaterialQuantity();
        $mq3->component_item_id = $compItem3->id;
        $mq3->material_item_id = $this->materialCement->id;
        $mq3->quantity = 20;
        $mq3->equivalent = 1;
        $mq3->created_by = $this->user->id;
        $mq3->save();

        // Component Item 4: Sand material (quantity = 15), Bags unit -> under Aggregates group
        $compItem4 = new ComponentItem();
        $compItem4->component_id = $this->component->id;
        $compItem4->name = 'Backfill';
        $compItem4->unit_id = $this->unitBags->id;
        $compItem4->quantity = 15;
        $compItem4->budget_price = 80.00;
        $compItem4->function_type_id = 3;
        $compItem4->function_variable = 15;
        $compItem4->created_by = $this->user->id;
        $compItem4->save();

        $mq4 = new MaterialQuantity();
        $mq4->component_item_id = $compItem4->id;
        $mq4->material_item_id = $this->materialSand->id;
        $mq4->quantity = 15;
        $mq4->equivalent = 1;
        $mq4->created_by = $this->user->id;
        $mq4->save();

        // Approved Material Quantity Request
        $mqr = new MaterialQuantityRequest();
        $mqr->project_id = $this->project->id;
        $mqr->section_id = $this->section->id;
        $mqr->component_id = $this->component->id;
        $mqr->status = 'APRV';
        $mqr->description = 'Initial cement request';
        $mqr->created_by = $this->user->id;
        $mqr->save();

        $mqrItem = new MaterialQuantityRequestItem();
        $mqrItem->material_quantity_request_id = $mqr->id;
        $mqrItem->component_item_id = $compItem1->id;
        $mqrItem->material_item_id = $this->materialCement->id;
        $mqrItem->requested_quantity = 25;
        $mqrItem->status = 'APRV';
        $mqrItem->save();

        // Supplier & PO
        $supplier = new Supplier();
        $supplier->name = 'ABC Supply';
        $supplier->address = '123 Main St';
        $supplier->primary_contact_no = '09123456789';
        $supplier->created_by = $this->user->id;
        $supplier->save();

        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'COD';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $po = new PurchaseOrder();
        $po->project_id = $this->project->id;
        $po->section_id = $this->section->id;
        $po->contract_item_id = $this->contractItem->id;
        $po->component_id = $this->component->id;
        $po->material_quantity_request_id = $mqr->id;
        $po->supplier_id = $supplier->id;
        $po->payment_term_id = $paymentTerm->id;
        $po->status = 'APRV';
        $po->created_by = $this->user->id;
        $po->save();

        $poItem = new PurchaseOrderItem();
        $poItem->purchase_order_id = $po->id;
        $poItem->component_item_id = $compItem1->id;
        $poItem->material_quantity_request_item_id = $mqrItem->id;
        $poItem->material_canvass_id = 1;
        $poItem->material_item_id = $this->materialCement->id;
        $poItem->quantity = 20;
        $poItem->price = 210.00;
        $poItem->status = 'APRV';
        $poItem->save();

        $response = $this->actingAs($this->user)->get('/report/project/generate?project_id=' . $this->project->id . '&section_id=' . $this->section->id);

        $response->assertStatus(200);

        // Check view data
        $materialSummary = $response->viewData('material_summary');
        $this->assertNotNull($materialSummary);
        $this->assertIsArray($materialSummary);

        // 2 Material Groups expected: Aggregates and Construction Materials
        $this->assertCount(2, $materialSummary);

        // Group 1: Aggregates
        $aggregatesGroup = collect($materialSummary)->firstWhere('material_group_name', 'Aggregates');
        $this->assertNotNull($aggregatesGroup);
        $this->assertEquals($this->materialGroupAggregates->id, $aggregatesGroup['material_group_id']);
        $this->assertCount(1, $aggregatesGroup['items']);
        $this->assertEquals(1, $aggregatesGroup['total_count']);
        $this->assertEquals(15, $aggregatesGroup['items'][0]['total_budget_quantity']);
        $this->assertEquals('Bags', $aggregatesGroup['items'][0]['unit']);

        // Group 2: Construction Materials
        $constructionGroup = collect($materialSummary)->firstWhere('material_group_name', 'Construction Materials');
        $this->assertNotNull($constructionGroup);
        $this->assertEquals($this->materialGroupConstruction->id, $constructionGroup['material_group_id']);
        $this->assertCount(2, $constructionGroup['items']); // Cement Bags and Cement Pcs
        $this->assertEquals(3, $constructionGroup['total_count']);
        $this->assertEquals(4200, $constructionGroup['total_po_amount']);

        $cementBags = collect($constructionGroup['items'])->firstWhere('unit', 'Bags');
        $this->assertNotNull($cementBags);
        $this->assertEquals(2, $cementBags['count']);
        $this->assertEquals(80, $cementBags['total_budget_quantity']);
        $this->assertEquals(25, $cementBags['total_request_quantity']);
        $this->assertEquals(20, $cementBags['total_po_quantity']);
        $this->assertEquals(4200, $cementBags['total_po_amount']);

        $cementPcs = collect($constructionGroup['items'])->firstWhere('unit', 'Pcs');
        $this->assertNotNull($cementPcs);
        $this->assertEquals(1, $cementPcs['count']);
        $this->assertEquals(20, $cementPcs['total_budget_quantity']);

        // Check HTML content
        $response->assertSee('Summary of Materials Quantity');
        $response->assertSee('Construction Materials');
        $response->assertSee('Aggregates');
        $response->assertSee('Holcim Portland Cement 40kg Bag');
        $response->assertSee('Local Washed Sand cu.m');

        // Check print page renders with materials summary grouped by material group
        $printResponse = $this->actingAs($this->user)->get('/report/project/print?project_id=' . $this->project->id . '&section_id=' . $this->section->id);
        $printResponse->assertStatus(200);
        $printResponse->assertSee('Summary of Materials Quantity');
        $printResponse->assertSee('Construction Materials');
        $printResponse->assertSee('Aggregates');
        $printResponse->assertSee('Holcim Portland Cement 40kg Bag');
        $printResponse->assertSee('Local Washed Sand cu.m');
    }

    /** @test */
    public function it_renders_empty_state_when_no_materials_exist()
    {
        $response = $this->actingAs($this->user)->get('/report/project/generate?project_id=' . $this->project->id . '&section_id=' . $this->section->id);
        $response->assertStatus(200);

        $materialSummary = $response->viewData('material_summary');
        $this->assertIsArray($materialSummary);
        $this->assertCount(0, $materialSummary);
        $response->assertSee('No material quantities found for the selected scope.');
    }

    /** @test */
    public function it_filters_materials_summary_when_filtering_by_contract_item()
    {
        // Create second contract item and component
        $contractItem2 = new ContractItem();
        $contractItem2->section_id = $this->section->id;
        $contractItem2->item_type = 'MAT';
        $contractItem2->item_code = '200';
        $contractItem2->description = 'Masonry Works';
        $contractItem2->contract_quantity = 50;
        $contractItem2->unit_id = $this->unitBags->id;
        $contractItem2->contract_unit_price = 500.00;
        $contractItem2->created_by = $this->user->id;
        $contractItem2->save();

        $comp2 = new Component();
        $comp2->name = 'Masonry Walls';
        $comp2->contract_item_id = $contractItem2->id;
        $comp2->quantity = 5;
        $comp2->unit_id = $this->unitBags->id;
        $comp2->use_count = 1;
        $comp2->status = 'APRV';
        $comp2->section_id = $this->section->id;
        $comp2->created_by = $this->user->id;
        $comp2->save();

        $ci = new ComponentItem();
        $ci->component_id = $comp2->id;
        $ci->name = 'CHB Laying';
        $ci->unit_id = $this->unitBags->id;
        $ci->quantity = 5;
        $ci->budget_price = 100.00;
        $ci->function_type_id = 3;
        $ci->function_variable = 5;
        $ci->created_by = $this->user->id;
        $ci->save();

        $mq = new MaterialQuantity();
        $mq->component_item_id = $ci->id;
        $mq->material_item_id = $this->materialCement->id;
        $mq->quantity = 40;
        $mq->equivalent = 1;
        $mq->created_by = $this->user->id;
        $mq->save();

        // Query filtering by contractItem2 only
        $response = $this->actingAs($this->user)->get('/report/project/generate?project_id=' . $this->project->id . '&section_id=' . $this->section->id . '&contract_item_id=' . $contractItem2->id);
        $response->assertStatus(200);

        $materialSummary = $response->viewData('material_summary');
        $this->assertCount(1, $materialSummary);
        $this->assertEquals('Construction Materials', $materialSummary[0]['material_group_name']);
        $this->assertCount(1, $materialSummary[0]['items']);
        $this->assertEquals(40, $materialSummary[0]['items'][0]['total_budget_quantity']);
        $this->assertEquals($this->materialCement->id, $materialSummary[0]['items'][0]['material_item_id']);
    }
}
