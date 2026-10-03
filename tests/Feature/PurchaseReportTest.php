<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\Unit;
use App\Models\MaterialGroup;
use App\Models\MaterialItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\PaymentTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseReportTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;
    protected $section;
    protected $contractItem;
    protected $component;
    protected $supplier;
    protected $paymentTerm;
    protected $materialGroup;
    protected $materialCement;
    protected $materialSand;
    protected $po1;
    protected $po2;

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

        $unit = new Unit();
        $unit->text = 'Bags';
        $unit->created_by = $this->user->id;
        $unit->save();

        $this->supplier = new Supplier();
        $this->supplier->name = 'Apex Hardware';
        $this->supplier->address = '123 Main St';
        $this->supplier->primary_contact_no = '09123456789';
        $this->supplier->created_by = $this->user->id;
        $this->supplier->save();

        $this->paymentTerm = new PaymentTerm();
        $this->paymentTerm->text = 'Cash on Delivery';
        $this->paymentTerm->created_by = $this->user->id;
        $this->paymentTerm->save();

        $this->materialGroup = new MaterialGroup();
        $this->materialGroup->name = 'Masonry';
        $this->materialGroup->created_by = $this->user->id;
        $this->materialGroup->save();

        $this->materialCement = new MaterialItem();
        $this->materialCement->material_group_id = $this->materialGroup->id;
        $this->materialCement->name = 'Portland Cement';
        $this->materialCement->specification_unit_packaging = '40kg Bag';
        $this->materialCement->brand = 'Holcim';
        $this->materialCement->created_by = $this->user->id;
        $this->materialCement->save();

        $this->materialSand = new MaterialItem();
        $this->materialSand->material_group_id = $this->materialGroup->id;
        $this->materialSand->name = 'Washed Sand';
        $this->materialSand->specification_unit_packaging = 'cu.m';
        $this->materialSand->brand = 'Local';
        $this->materialSand->created_by = $this->user->id;
        $this->materialSand->save();

        $this->project = new Project();
        $this->project->name = 'Commercial Plaza';
        $this->project->status = 'ACTV';
        $this->project->created_by = $this->user->id;
        $this->project->save();

        $this->section = new Section();
        $this->section->project_id = $this->project->id;
        $this->section->name = 'Foundation Phase';
        $this->section->created_by = $this->user->id;
        $this->section->save();

        $this->contractItem = new ContractItem();
        $this->contractItem->section_id = $this->section->id;
        $this->contractItem->item_type = 'MAT';
        $this->contractItem->item_code = '100';
        $this->contractItem->description = 'Civil Works';
        $this->contractItem->contract_quantity = 500;
        $this->contractItem->unit_id = $unit->id;
        $this->contractItem->contract_unit_price = 250.00;
        $this->contractItem->created_by = $this->user->id;
        $this->contractItem->save();

        $this->component = new Component();
        $this->component->name = 'Footing Columns';
        $this->component->contract_item_id = $this->contractItem->id;
        $this->component->quantity = 100;
        $this->component->unit_id = $unit->id;
        $this->component->status = 'APRV';
        $this->component->section_id = $this->section->id;
        $this->component->created_by = $this->user->id;
        $this->component->save();

        // PO 1
        $this->po1 = new PurchaseOrder();
        $this->po1->project_id = $this->project->id;
        $this->po1->section_id = $this->section->id;
        $this->po1->component_id = $this->component->id;
        $this->po1->contract_item_id = $this->contractItem->id;
        $this->po1->material_quantity_request_id = 1;
        $this->po1->supplier_id = $this->supplier->id;
        $this->po1->payment_term_id = $this->paymentTerm->id;
        $this->po1->status = 'APRV';
        $this->po1->created_by = $this->user->id;
        $this->po1->save();

        $poi1 = new PurchaseOrderItem();
        $poi1->purchase_order_id = $this->po1->id;
        $poi1->component_item_id = 1;
        $poi1->material_quantity_request_item_id = 1;
        $poi1->material_canvass_id = 1;
        $poi1->material_item_id = $this->materialCement->id;
        $poi1->quantity = 50;
        $poi1->price = 220.00;
        $poi1->status = 'APRV';
        $poi1->save();

        $poi2 = new PurchaseOrderItem();
        $poi2->purchase_order_id = $this->po1->id;
        $poi2->component_item_id = 1;
        $poi2->material_quantity_request_item_id = 1;
        $poi2->material_canvass_id = 1;
        $poi2->material_item_id = $this->materialSand->id;
        $poi2->quantity = 20;
        $poi2->price = 800.00;
        $poi2->status = 'APRV';
        $poi2->save();

        // PO 2
        $this->po2 = new PurchaseOrder();
        $this->po2->project_id = $this->project->id;
        $this->po2->section_id = $this->section->id;
        $this->po2->component_id = $this->component->id;
        $this->po2->contract_item_id = $this->contractItem->id;
        $this->po2->material_quantity_request_id = 1;
        $this->po2->supplier_id = $this->supplier->id;
        $this->po2->payment_term_id = $this->paymentTerm->id;
        $this->po2->status = 'APRV';
        $this->po2->created_by = $this->user->id;
        $this->po2->save();

        $poi3 = new PurchaseOrderItem();
        $poi3->purchase_order_id = $this->po2->id;
        $poi3->component_item_id = 1;
        $poi3->material_quantity_request_item_id = 1;
        $poi3->material_canvass_id = 1;
        $poi3->material_item_id = $this->materialCement->id;
        $poi3->quantity = 30;
        $poi3->price = 220.00;
        $poi3->status = 'APRV';
        $poi3->save();
    }

    /** @test */
    public function it_can_generate_purchase_report_with_per_po_section()
    {
        $response = $this->actingAs($this->user)->get('/report/purchase/generate?' . http_build_query([
            'project_id' => $this->project->id,
            'section_id' => $this->section->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('-- Per Purchase Order --');

        $po1Number = str_pad($this->po1->id, 6, '0', STR_PAD_LEFT);
        $po2Number = str_pad($this->po2->id, 6, '0', STR_PAD_LEFT);

        $response->assertSee('PO # ' . $po1Number);
        $response->assertSee('PO # ' . $po2Number);
        $response->assertSee('colspan="2"', false);

        // Check quantities
        $response->assertSee('50.00');
        $response->assertSee('20.00');
        $response->assertSee('30.00');

        // Check print button at the top
        $response->assertSee('id="printBtn"', false);
        $response->assertSee("window.open('/report/purchase/print?", false);
        $response->assertSee("'_blank'", false);
    }

    /** @test */
    public function it_filters_per_po_section_by_material_item()
    {
        // Filter by cement only
        $response = $this->actingAs($this->user)->get('/report/purchase/generate?' . http_build_query([
            'project_id' => $this->project->id,
            'section_id' => $this->section->id,
            'material_group_id' => $this->materialGroup->id,
            'material_items' => $this->materialCement->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('-- Per Purchase Order --');

        $po1Number = str_pad($this->po1->id, 6, '0', STR_PAD_LEFT);
        $response->assertSee('PO # ' . $po1Number);

        // Cement should be present
        $response->assertSee('50.00');
        // Sand was 20.00, should not appear in this filtered report
        $response->assertDontSee('20.00');
    }

    /** @test */
    public function it_renders_per_po_section_in_print_view()
    {
        $response = $this->actingAs($this->user)->get('/report/purchase/print?' . http_build_query([
            'project_id' => $this->project->id,
            'section_id' => $this->section->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('-- Per Purchase Order --');

        $po1Number = str_pad($this->po1->id, 6, '0', STR_PAD_LEFT);
        $response->assertSee('PO # ' . $po1Number);
        $response->assertSee('50.00');
        $response->assertSee('font-size: 11px', false);
        $response->assertSee('<title>Purchase Report - ' . $this->project->name . ' - ' . date('Y-m-d') . '</title>', false);
    }
}
