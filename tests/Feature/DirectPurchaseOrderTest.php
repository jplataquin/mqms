<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\AccessCode;
use App\Models\RoleAccessCode;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\PaymentTerm;
use App\Models\MaterialItem;
use App\Models\DirectPurchaseOrder;
use App\Models\DirectPurchaseOrderItem;
use App\Models\DirectMaterialQuantityRequest;
use App\Models\DirectMaterialQuantityRequestItem;
use App\Models\DirectMaterialCanvass;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DirectPurchaseOrderTest extends TestCase
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

        $this->grantAccessCode($this->user, 'direct_purchase_order:own:create');
    }

    protected function grantAccessCode($user, $codeString)
    {
        $accessCode = AccessCode::where('code', $codeString)->first();
        if (!$accessCode) {
            $accessCode = new AccessCode();
            $accessCode->code = $codeString;
            $accessCode->description = 'Test code';
            $accessCode->save();
        }

        $role = new Role();
        $role->name = 'Role ' . $codeString;
        $role->description = 'Test role';
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
    public function it_can_render_the_direct_purchase_order_create_page()
    {
        $paymentTerm2 = new PaymentTerm();
        $paymentTerm2->text = 'Net 60 Days';
        $paymentTerm2->created_by = $this->user->id;
        $paymentTerm2->save();

        $paymentTerm1 = new PaymentTerm();
        $paymentTerm1->text = 'Cash on Delivery';
        $paymentTerm1->created_by = $this->user->id;
        $paymentTerm1->save();

        $response = $this->actingAs($this->user)->get('/direct_purchase_order/create');
        $response->assertStatus(200);
        $response->assertSee('Cash on Delivery');
        $response->assertSee('Net 60 Days');
        $response->assertSeeInOrder(['Cash on Delivery', 'Net 60 Days']);
    }

    /** @test */
    public function it_can_display_payment_term_name_via_model_accessor()
    {
        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Net 15 Days';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $this->assertEquals('Net 15 Days', $paymentTerm->name);
        $this->assertEquals('Net 15 Days', $paymentTerm->toArray()['name']);
    }

    protected function createSupplier($name = 'Test Supplier')
    {
        $supplier = new Supplier();
        $supplier->name = $name;
        $supplier->address = '123 Test Street';
        $supplier->primary_contact_no = '09123456789';
        $supplier->created_by = $this->user->id;
        $supplier->save();

        return $supplier;
    }

    protected function createProject($name = 'Test Project')
    {
        $project = new Project();
        $project->name = $name;
        $project->status = 'ACTV';
        $project->created_by = $this->user->id;
        $project->save();

        return $project;
    }

    protected function createSection($project, $name = 'Test Section')
    {
        $section = new \App\Models\Section();
        $section->project_id = $project->id;
        $section->name = $name;
        $section->created_by = $this->user->id;
        $section->save();

        return $section;
    }

    protected function createMaterialItem($name = 'Portland Cement')
    {
        $item = new MaterialItem();
        $item->material_group_id = 1;
        $item->name = $name;
        $item->brand = 'Holcim';
        $item->specification_unit_packaging = '40kg bag';
        $item->created_by = $this->user->id;
        $item->save();

        return $item;
    }

    /** @test */
    public function it_renders_type_searchable_supplier_project_and_section_fields_on_create_page()
    {
        $supplier = $this->createSupplier('Acme Hardware Supplies');

        $project = new Project();
        $project->name = 'Metro Expressway Phase 1';
        $project->status = 'ACTV';
        $project->created_by = $this->user->id;
        $project->save();

        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = '30 Days Credit';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $response = $this->actingAs($this->user)->get('/direct_purchase_order/create');
        $response->assertStatus(200);

        // Searchable Supplier
        $response->assertSee('id="supplierInput"', false);
        $response->assertSee('list="supplierList"', false);
        $response->assertSee('Acme Hardware Supplies');

        // Required Project with red asterisk
        $response->assertSee('Project <span class="text-danger">*</span>', false);
        $response->assertSee('id="projectInput"', false);
        $response->assertSee('list="projectList"', false);
        $response->assertSee('Metro Expressway Phase 1');

        // Optional Section
        $response->assertSee('Section (Optional)', false);
        $response->assertSee('id="sectionInput"', false);
        $response->assertSee('list="sectionList"', false);

        // Required Payment Term
        $response->assertSee('id="paymentTermSelect"', false);
        $response->assertSee('required', false);
        $response->assertSee('30 Days Credit');

        // Searchable Material Item
        $response->assertSee('id="materialList"', false);
        $response->assertSee('list="materialList"', false);
        $response->assertSee('item-material-input', false);
        $response->assertSee('clear-item-material-btn', false);

        // Remarks as a separate textarea row
        $response->assertSee('item-remarks-row', false);
        $response->assertSee('<textarea class="form-control item-remarks"', false);
        $response->assertSee('colspan="5"', false);

        // Single line item remove button management script
        $response->assertSee('updateItemRemoveButtons', false);
        $response->assertSee('At least one item is required to make a direct PO.', false);
    }

    /** @test */
    public function it_renders_remarks_as_separate_textarea_row_under_material_item()
    {
        $response = $this->actingAs($this->user)->get('/direct_purchase_order/create');
        $response->assertStatus(200);

        // Table headers should not have Remarks as a column
        $content = $response->getContent();
        $this->assertStringContainsString('<th style="width: 50%">Material Item</th>', $content);
        $this->assertStringNotContainsString('<th style="width: 20%">Remarks</th>', $content);

        // Should have remarks textarea row with colspan 5
        $this->assertStringContainsString('item-remarks-row', $content);
        $this->assertStringContainsString('<textarea class="form-control item-remarks" rows="2" placeholder="Optional remarks..."></textarea>', $content);
        $this->assertStringContainsString('<td colspan="5"', $content);
    }

    /** @test */
    public function it_renders_searchable_material_item_field_with_datalist_options_on_create_page()
    {
        $materialItem = $this->createMaterialItem('Deformed Rebar 16mm Grade 40');

        $response = $this->actingAs($this->user)->get('/direct_purchase_order/create');
        $response->assertStatus(200);

        $response->assertSee('id="materialList"', false);
        $response->assertSee('data-id="' . $materialItem->id . '"', false);
        $response->assertSee('Deformed Rebar 16mm Grade 40', false);
        $response->assertSee('item-material-input', false);
        $response->assertSee('clear-item-material-btn', false);
    }

    /** @test */
    public function it_fails_creation_when_project_is_missing_or_zero()
    {
        $supplier = $this->createSupplier('Concrete Pro');
        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Net 30 Days';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $materialItem = $this->createMaterialItem('Portland Cement');

        $items = [
            [
                'material_item_id' => $materialItem->id,
                'order_quantity'   => 10,
                'price'            => 250.00,
            ]
        ];

        // Missing project_id
        $response = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/create', [
            'supplier_id'     => $supplier->id,
            'payment_term_id' => $paymentTerm->id,
            'project_id'      => null,
            'items'           => json_encode($items),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => -2,
            'message' => 'Failed Validation',
        ]);
        $this->assertArrayHasKey('project_id', $response->json('data'));
        $this->assertEquals('Project is required.', $response->json('data.project_id.0'));

        // Project ID = 0
        $responseZero = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/create', [
            'supplier_id'     => $supplier->id,
            'payment_term_id' => $paymentTerm->id,
            'project_id'      => 0,
            'items'           => json_encode($items),
        ]);

        $responseZero->assertStatus(200);
        $responseZero->assertJson([
            'status'  => -2,
            'message' => 'Failed Validation',
        ]);
        $this->assertArrayHasKey('project_id', $responseZero->json('data'));

        // Non-existent project
        $responseInvalid = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/create', [
            'supplier_id'     => $supplier->id,
            'payment_term_id' => $paymentTerm->id,
            'project_id'      => 999999,
            'items'           => json_encode($items),
        ]);

        $responseInvalid->assertStatus(200);
        $responseInvalid->assertJson([
            'status'  => -2,
            'message' => 'Failed Validation',
        ]);
        $this->assertArrayHasKey('project_id', $responseInvalid->json('data'));
        $this->assertEquals('The selected Project is invalid.', $responseInvalid->json('data.project_id.0'));
    }

    /** @test */
    public function it_fails_creation_when_items_are_empty()
    {
        $supplier = $this->createSupplier('Concrete Pro');
        $project = $this->createProject('Tower 1');
        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Net 30 Days';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $response = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/create', [
            'supplier_id'     => $supplier->id,
            'payment_term_id' => $paymentTerm->id,
            'project_id'      => $project->id,
            'items'           => json_encode([]),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 0,
            'message' => 'At least one item is required to make a direct PO.',
        ]);
    }

    /** @test */
    public function it_fails_creation_when_payment_term_is_missing_or_zero()
    {
        $supplier = $this->createSupplier('Concrete Pro');
        $project = $this->createProject('Bridge Phase 2');
        $materialItem = $this->createMaterialItem('Portland Cement');

        $items = [
            [
                'material_item_id' => $materialItem->id,
                'order_quantity'   => 10,
                'price'            => 250.00,
            ]
        ];

        // Missing payment_term_id
        $response = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/create', [
            'supplier_id'     => $supplier->id,
            'payment_term_id' => 0,
            'project_id'      => $project->id,
            'items'           => json_encode($items),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => -2,
            'message' => 'Failed Validation',
        ]);
        $this->assertArrayHasKey('payment_term_id', $response->json('data'));
    }

    /** @test */
    public function it_fails_creation_when_payment_term_does_not_exist()
    {
        $supplier = $this->createSupplier('Concrete Pro');
        $project = $this->createProject('Bridge Phase 2');
        $materialItem = $this->createMaterialItem('Portland Cement');

        $items = [
            [
                'material_item_id' => $materialItem->id,
                'order_quantity'   => 10,
                'price'            => 250.00,
            ]
        ];

        $response = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/create', [
            'supplier_id'     => $supplier->id,
            'payment_term_id' => 99999,
            'project_id'      => $project->id,
            'items'           => json_encode($items),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => -2,
            'message' => 'Failed Validation',
        ]);
        $this->assertArrayHasKey('payment_term_id', $response->json('data'));
    }

    /** @test */
    public function it_can_create_direct_purchase_order_with_valid_supplier_and_payment_term()
    {
        $supplier = $this->createSupplier('Concrete Pro');
        $project = $this->createProject('Highway Rehabilitation');

        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Net 30 Days';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $materialItem = $this->createMaterialItem('Portland Cement');

        $items = [
            [
                'material_item_id' => $materialItem->id,
                'order_quantity'   => 5,
                'price'            => 220.00,
            ]
        ];

        $response = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/create', [
            'supplier_id'     => $supplier->id,
            'payment_term_id' => $paymentTerm->id,
            'project_id'      => $project->id,
            'items'           => json_encode($items),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
        ]);

        $this->assertDatabaseHas('direct_purchase_orders', [
            'supplier_id'     => $supplier->id,
            'payment_term_id' => $paymentTerm->id,
            'project_id'      => $project->id,
            'section_id'      => null,
            'status'          => 'DRFT',
        ]);
    }

    /** @test */
    public function it_allows_section_to_be_null_or_empty_or_zero_or_string_null_without_validation_errors()
    {
        $supplier = $this->createSupplier('Steel Supply');
        $project = $this->createProject('Bridge Repair');

        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Net 30 Days';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $materialItem = $this->createMaterialItem('Rebar 16mm');

        $items = [
            [
                'material_item_id' => $materialItem->id,
                'order_quantity'   => 10,
                'price'            => 500.00,
            ]
        ];

        // Testing section_id as string "null" (FormData behavior)
        $resStringNull = $this->actingAs($this->user)->post('/api/direct_purchase_order/create', [
            'supplier_id'     => $supplier->id,
            'payment_term_id' => $paymentTerm->id,
            'project_id'      => $project->id,
            'section_id'      => 'null',
            'items'           => json_encode($items),
        ]);
        $resStringNull->assertStatus(200);
        $resStringNull->assertJson(['status' => 1]);

        // Testing section_id as 0
        $resZero = $this->actingAs($this->user)->post('/api/direct_purchase_order/create', [
            'supplier_id'     => $supplier->id,
            'payment_term_id' => $paymentTerm->id,
            'project_id'      => $project->id,
            'section_id'      => 0,
            'items'           => json_encode($items),
        ]);
        $resZero->assertStatus(200);
        $resZero->assertJson(['status' => 1]);

        // Testing section_id as empty string ""
        $resEmpty = $this->actingAs($this->user)->post('/api/direct_purchase_order/create', [
            'supplier_id'     => $supplier->id,
            'payment_term_id' => $paymentTerm->id,
            'project_id'      => $project->id,
            'section_id'      => '',
            'items'           => json_encode($items),
        ]);
        $resEmpty->assertStatus(200);
        $resEmpty->assertJson(['status' => 1]);
    }

    /** @test */
    public function it_can_create_direct_purchase_order_with_optional_section()
    {
        $supplier = $this->createSupplier('Apex Hardware');
        $project = $this->createProject('Coastal Road');
        $section = $this->createSection($project, 'Section Alpha');

        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Net 15 Days';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $materialItem = $this->createMaterialItem('Gravel 3/4');

        $items = [
            [
                'material_item_id' => $materialItem->id,
                'order_quantity'   => 15,
                'price'            => 1200.00,
            ]
        ];

        $response = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/create', [
            'supplier_id'     => $supplier->id,
            'payment_term_id' => $paymentTerm->id,
            'project_id'      => $project->id,
            'section_id'      => $section->id,
            'items'           => json_encode($items),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
        ]);

        $this->assertDatabaseHas('direct_purchase_orders', [
            'supplier_id'     => $supplier->id,
            'payment_term_id' => $paymentTerm->id,
            'project_id'      => $project->id,
            'section_id'      => $section->id,
            'status'          => 'DRFT',
        ]);
    }

    /** @test */
    public function it_can_create_direct_purchase_order_with_line_item_remarks()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:view');

        $supplier = $this->createSupplier('Hardware Depot');
        $project = $this->createProject('Commercial Complex');

        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Cash on Delivery';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $materialItem = $this->createMaterialItem('Deformed Bar 12mm');

        $items = [
            [
                'material_item_id' => $materialItem->id,
                'order_quantity'   => 20,
                'price'            => 350.00,
                'remarks'          => 'Deliver to Warehouse B',
            ]
        ];

        $response = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/create', [
            'supplier_id'     => $supplier->id,
            'payment_term_id' => $paymentTerm->id,
            'project_id'      => $project->id,
            'items'           => json_encode($items),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
        ]);

        $dpoId = $response->json('data.id');

        $this->assertDatabaseHas('direct_purchase_order_items', [
            'direct_purchase_order_id' => $dpoId,
            'material_item_id'         => $materialItem->id,
            'quantity'                 => 20,
            'price'                    => 350.00,
            'remarks'                  => 'Deliver to Warehouse B',
        ]);

        $displayResponse = $this->actingAs($this->user)->get('/direct_purchase_order/' . $dpoId);
        $displayResponse->assertStatus(200);
        $displayResponse->assertSee('Deliver to Warehouse B');
        $displayResponse->assertSee('<th>Remarks</th>', false);

        // Mark as APRV to allow print view
        $dpo = DirectPurchaseOrder::find($dpoId);
        $dpo->status = 'APRV';
        $dpo->save();

        $printResponse = $this->actingAs($this->user)->get('/direct_purchase_order/print/' . $dpoId);
        $printResponse->assertStatus(200);
        $printResponse->assertSee('Deliver to Warehouse B');
        $printResponse->assertSee('<th>Remarks</th>', false);
        $printResponse->assertSee('/storage/sys_images/header_with_address.png');
    }

    /** @test */
    public function it_displays_image_header_on_each_printed_copy()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:own:view');

        $project = $this->createProject('Header Project');
        $supplier = $this->createSupplier('Header Supplier');

        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Cash on Delivery';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $dpo = new DirectPurchaseOrder();
        $dpo->supplier_id = $supplier->id;
        $dpo->payment_term_id = $paymentTerm->id;
        $dpo->project_id = $project->id;
        $dpo->status = 'APRV';
        $dpo->created_by = $this->user->id;
        $dpo->updated_by = $this->user->id;
        $dpo->approved_by = $this->user->id;
        $dpo->approved_at = now();
        $dpo->save();

        $printResponse = $this->actingAs($this->user)->get('/direct_purchase_order/print/' . $dpo->id);
        $printResponse->assertStatus(200);
        $printResponse->assertSee('<img src="/storage/sys_images/header_with_address.png" style="width:500px"/>', false);
        $this->assertEquals(3, substr_count($printResponse->getContent(), '/storage/sys_images/header_with_address.png'));
        $printResponse->assertSee('DIRECT PURCHASE ORDER (Supplier Copy)');
        $printResponse->assertSee('DIRECT PURCHASE ORDER (Accounting Copy)');
        $printResponse->assertSee('DIRECT PURCHASE ORDER (Purchaser Copy)');
        $printResponse->assertSee('DIRECT PURCHASE ORDER (Site Copy)');
        $printResponse->assertSee('DIRECT PURCHASE ORDER (Warehouse Copy)');
        $this->assertEquals(2, substr_count($printResponse->getContent(), '/storage/sys_images/header.png'));
    }

    /** @test */
    public function it_includes_site_copy_and_warehouse_copy_in_print_view()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:own:view');

        $project = $this->createProject('Warehouse Project');
        $supplier = $this->createSupplier('Site Supplier');
        $materialItem = $this->createMaterialItem('Steel Rebar 16mm');

        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Net 30';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $dpo = new DirectPurchaseOrder();
        $dpo->supplier_id = $supplier->id;
        $dpo->payment_term_id = $paymentTerm->id;
        $dpo->project_id = $project->id;
        $dpo->status = 'APRV';
        $dpo->created_by = $this->user->id;
        $dpo->updated_by = $this->user->id;
        $dpo->approved_by = $this->user->id;
        $dpo->approved_at = now();
        $dpo->save();

        $item = new DirectPurchaseOrderItem();
        $item->direct_purchase_order_id = $dpo->id;
        $item->material_item_id = $materialItem->id;
        $item->quantity = 50;
        $item->price = 450.00;
        $item->remarks = 'For Foundation';
        $item->save();

        $response = $this->actingAs($this->user)->get('/direct_purchase_order/print/' . $dpo->id);
        $response->assertStatus(200);

        // Verify Site Copy
        $response->assertSee('DIRECT PURCHASE ORDER (Site Copy)');
        $response->assertSee('Received By');
        $response->assertSee('Steel Rebar 16mm');
        $response->assertSee('For Foundation');

        // Verify Warehouse Copy
        $response->assertSee('DIRECT PURCHASE ORDER (Warehouse Copy)');
        $response->assertSee('Validated By');
        $response->assertSee('Received');
        $response->assertSee('Rejected');
        $response->assertSee('Diff:');
        $response->assertSee('<img src="/storage/sys_images/header.png" style="width:500px"/>', false);
    }

    /** @test */
    public function it_does_not_render_approve_disapprove_or_go_to_review_buttons_on_display_page()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:own:view');
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:view');
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:approve');
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:reject');

        $project = $this->createProject('Display Test Project');
        $supplier = $this->createSupplier('Display Test Supplier');

        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Net 15';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $dpo = new DirectPurchaseOrder();
        $dpo->supplier_id = $supplier->id;
        $dpo->payment_term_id = $paymentTerm->id;
        $dpo->project_id = $project->id;
        $dpo->status = 'PEND';
        $dpo->created_by = $this->user->id;
        $dpo->updated_by = $this->user->id;
        $dpo->save();

        $response = $this->actingAs($this->user)->get('/direct_purchase_order/' . $dpo->id);
        $response->assertStatus(200);

        // Buttons must NOT be present on the regular display page
        $response->assertDontSee('Go to Review');
        $response->assertDontSee('id="approveBtn"', false);
        $response->assertDontSee('id="rejectBtn"', false);
        $response->assertDontSee('Disapprove');

        // Review buttons MUST be available on the review page
        $reviewResponse = $this->actingAs($this->user)->get('/review/direct_purchase_order/' . $dpo->id);
        $reviewResponse->assertStatus(200);
        $reviewResponse->assertSee('id="approveBtn"', false);
        $reviewResponse->assertSee('id="rejectBtn"', false);
    }

    /** @test */
    public function it_allows_printing_only_purchaser_copy_in_draft_status_with_remarks_as_row_after_item()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:own:view');

        $project = $this->createProject('Draft Print Project');
        $supplier = $this->createSupplier('Draft Print Supplier');
        $materialItem = $this->createMaterialItem('Gravel 3/4');

        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Cash on Delivery';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $dpo = new DirectPurchaseOrder();
        $dpo->supplier_id = $supplier->id;
        $dpo->payment_term_id = $paymentTerm->id;
        $dpo->project_id = $project->id;
        $dpo->status = 'DRFT';
        $dpo->created_by = $this->user->id;
        $dpo->updated_by = $this->user->id;
        $dpo->save();

        $item = new DirectPurchaseOrderItem();
        $item->direct_purchase_order_id = $dpo->id;
        $item->material_item_id = $materialItem->id;
        $item->quantity = 15;
        $item->price = 1200.00;
        $item->remarks = 'Pouring scheduled tomorrow';
        $item->save();

        // Check display page has print button for DRFT
        $displayResponse = $this->actingAs($this->user)->get('/direct_purchase_order/' . $dpo->id);
        $displayResponse->assertStatus(200);
        $displayResponse->assertSee('/direct_purchase_order/print/' . $dpo->id);

        // Check print page allows DRFT
        $response = $this->actingAs($this->user)->get('/direct_purchase_order/print/' . $dpo->id);
        $response->assertStatus(200);

        // MUST see ONLY Purchaser copy
        $response->assertSee('DIRECT PURCHASE ORDER (Purchaser Copy)');
        $response->assertDontSee('DIRECT PURCHASE ORDER (Supplier Copy)');
        $response->assertDontSee('DIRECT PURCHASE ORDER (Accounting Copy)');
        $response->assertDontSee('DIRECT PURCHASE ORDER (Site Copy)');
        $response->assertDontSee('DIRECT PURCHASE ORDER (Warehouse Copy)');

        // Line item remarks must appear as a row after the item row
        $response->assertSee('Gravel 3/4');
        $response->assertSee('<strong>Remarks:</strong> Pouring scheduled tomorrow', false);

        // Non-draft/non-approved status like PEND must abort 404
        $dpo->status = 'PEND';
        $dpo->save();
        $pendingPrintResponse = $this->actingAs($this->user)->get('/direct_purchase_order/print/' . $dpo->id);
        $pendingPrintResponse->assertStatus(404);
    }

    /** @test */
    public function it_removes_remarks_column_in_supplier_copy_and_indents_remarks_row_in_purchaser_and_accounting_copies()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:own:view');

        $project = $this->createProject('Copy Test Project');
        $supplier = $this->createSupplier('Copy Test Supplier');
        $materialItem = $this->createMaterialItem('Deformed Bar 12mm');

        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Net 30';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $dpo = new DirectPurchaseOrder();
        $dpo->supplier_id = $supplier->id;
        $dpo->payment_term_id = $paymentTerm->id;
        $dpo->project_id = $project->id;
        $dpo->status = 'APRV';
        $dpo->created_by = $this->user->id;
        $dpo->updated_by = $this->user->id;
        $dpo->approved_by = $this->user->id;
        $dpo->approved_at = now();
        $dpo->save();

        $item = new DirectPurchaseOrderItem();
        $item->direct_purchase_order_id = $dpo->id;
        $item->material_item_id = $materialItem->id;
        $item->quantity = 25;
        $item->price = 320.00;
        $item->remarks = 'Grade 60 Standard';
        $item->save();

        $response = $this->actingAs($this->user)->get('/direct_purchase_order/print/' . $dpo->id);
        $response->assertStatus(200);

        $content = $response->getContent();

        // Split by copy sections
        $supplierSection = strstr($content, 'DIRECT PURCHASE ORDER (Supplier Copy)');
        $accountingSection = strstr($content, 'DIRECT PURCHASE ORDER (Accounting Copy)');
        $purchaserSection = strstr($content, 'DIRECT PURCHASE ORDER (Purchaser Copy)');

        // Cut sections to their boundary
        $supplierHtml = substr($supplierSection, 0, strpos($supplierSection, 'DIRECT PURCHASE ORDER (Accounting Copy)'));
        $accountingHtml = substr($accountingSection, 0, strpos($accountingSection, 'DIRECT PURCHASE ORDER (Purchaser Copy)'));
        $purchaserHtml = substr($purchaserSection, 0, strpos($purchaserSection, '<!-- Site Copy -->'));

        // Supplier copy: NO remarks column and NO remarks row
        $this->assertStringNotContainsString('<th>Remarks</th>', $supplierHtml);
        $this->assertStringNotContainsString('Grade 60 Standard', $supplierHtml);

        // Accounting copy: remarks row with indent-left
        $this->assertStringNotContainsString('<th>Remarks</th>', $accountingHtml);
        $this->assertStringContainsString('class="indent-left"', $accountingHtml);
        $this->assertStringContainsString('<strong>Remarks:</strong> Grade 60 Standard', $accountingHtml);

        // Purchaser copy: remarks row with indent-left
        $this->assertStringNotContainsString('<th>Remarks</th>', $purchaserHtml);
        $this->assertStringContainsString('class="indent-left"', $purchaserHtml);
        $this->assertStringContainsString('<strong>Remarks:</strong> Grade 60 Standard', $purchaserHtml);
    }

    /** @test */
    public function it_renders_dmqr_field_on_direct_purchase_order_create_page()
    {
        $response = $this->actingAs($this->user)->get('/direct_purchase_order/create');
        $response->assertStatus(200);

        $response->assertSee('id="dmqrInput"', false);
        $response->assertSee('list="dmqrList"', false);
    }

    /** @test */
    public function it_can_create_direct_purchase_order_linked_to_direct_material_quantity_request()
    {
        $project = $this->createProject('Expressway Link');
        $material = $this->createMaterialItem('Steel Girder');
        $supplier = $this->createSupplier('Steel Fabricators Co');
        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = '45 Days';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $dmqr = new DirectMaterialQuantityRequest();
        $dmqr->project_id = $project->id;
        $dmqr->status = 'APRV';
        $dmqr->created_by = $this->user->id;
        $dmqr->save();

        $item = new DirectMaterialQuantityRequestItem();
        $item->direct_material_quantity_request_id = $dmqr->id;
        $item->material_item_id = $material->id;
        $item->quantity = 20;
        $item->save();

        $canvass = new DirectMaterialCanvass();
        $canvass->direct_material_quantity_request_id = $dmqr->id;
        $canvass->direct_material_quantity_request_item_id = $item->id;
        $canvass->supplier_id = $supplier->id;
        $canvass->payment_term_id = $paymentTerm->id;
        $canvass->price = 1500.00;
        $canvass->status = 'APRV';
        $canvass->created_by = $this->user->id;
        $canvass->save();

        $items = [
            [
                'direct_material_quantity_request_item_id' => $item->id,
                'direct_material_canvass_id'               => $canvass->id,
                'material_item_id'                         => $material->id,
                'order_quantity'                           => 10,
                'price'                                    => 1500.00,
                'remarks'                                  => 'First delivery'
            ]
        ];

        $response = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/create', [
            'direct_material_quantity_request_id' => $dmqr->id,
            'supplier_id'                         => $supplier->id,
            'payment_term_id'                     => $paymentTerm->id,
            'project_id'                          => $project->id,
            'items'                               => json_encode($items)
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $dpoId = $response->json('data.id');
        $dpo = DirectPurchaseOrder::find($dpoId);
        $this->assertEquals($dmqr->id, $dpo->direct_material_quantity_request_id);

        $dpoItem = DirectPurchaseOrderItem::where('direct_purchase_order_id', $dpoId)->first();
        $this->assertEquals($item->id, $dpoItem->direct_material_quantity_request_item_id);
        $this->assertEquals($canvass->id, $dpoItem->direct_material_canvass_id);
        $this->assertEquals(10, $dpoItem->quantity);
    }

    /** @test */
    public function it_can_create_multiple_direct_purchase_orders_for_dmqr_as_long_as_there_is_item_balance()
    {
        $project = $this->createProject('Bridge Renovation');
        $material = $this->createMaterialItem('Concrete Piles');
        $supplier = $this->createSupplier('Piling Specialist Corp');
        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = '30 Days';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $dmqr = new DirectMaterialQuantityRequest();
        $dmqr->project_id = $project->id;
        $dmqr->status = 'APRV';
        $dmqr->created_by = $this->user->id;
        $dmqr->save();

        $item = new DirectMaterialQuantityRequestItem();
        $item->direct_material_quantity_request_id = $dmqr->id;
        $item->material_item_id = $material->id;
        $item->quantity = 100;
        $item->save();

        $canvass = new DirectMaterialCanvass();
        $canvass->direct_material_quantity_request_id = $dmqr->id;
        $canvass->direct_material_quantity_request_item_id = $item->id;
        $canvass->supplier_id = $supplier->id;
        $canvass->payment_term_id = $paymentTerm->id;
        $canvass->price = 800.00;
        $canvass->status = 'APRV';
        $canvass->created_by = $this->user->id;
        $canvass->save();

        // First Direct PO: 60 items
        $po1 = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/create', [
            'direct_material_quantity_request_id' => $dmqr->id,
            'supplier_id'                         => $supplier->id,
            'payment_term_id'                     => $paymentTerm->id,
            'project_id'                          => $project->id,
            'items'                               => json_encode([[
                'direct_material_quantity_request_item_id' => $item->id,
                'direct_material_canvass_id'               => $canvass->id,
                'material_item_id'                         => $material->id,
                'order_quantity'                           => 60,
                'price'                                    => 800.00
            ]])
        ]);
        $po1->assertStatus(200);
        $po1->assertJson(['status' => 1]);

        // Second Direct PO: 40 items (remaining balance)
        $po2 = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/create', [
            'direct_material_quantity_request_id' => $dmqr->id,
            'supplier_id'                         => $supplier->id,
            'payment_term_id'                     => $paymentTerm->id,
            'project_id'                          => $project->id,
            'items'                               => json_encode([[
                'direct_material_quantity_request_item_id' => $item->id,
                'direct_material_canvass_id'               => $canvass->id,
                'material_item_id'                         => $material->id,
                'order_quantity'                           => 40,
                'price'                                    => 800.00
            ]])
        ]);
        $po2->assertStatus(200);
        $po2->assertJson(['status' => 1]);

        // Total ordered across both DPOs should equal requested 100
        $totalOrdered = DirectPurchaseOrderItem::where('direct_material_quantity_request_item_id', $item->id)->sum('quantity');
        $this->assertEquals(100, $totalOrdered);
    }

    /** @test */
    public function it_rejects_direct_purchase_order_when_order_quantity_exceeds_dmqr_item_balance()
    {
        $project = $this->createProject('Flyover Project');
        $material = $this->createMaterialItem('Cable Wire');
        $supplier = $this->createSupplier('Cables Inc');
        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Immediate';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $dmqr = new DirectMaterialQuantityRequest();
        $dmqr->project_id = $project->id;
        $dmqr->status = 'APRV';
        $dmqr->created_by = $this->user->id;
        $dmqr->save();

        $item = new DirectMaterialQuantityRequestItem();
        $item->direct_material_quantity_request_id = $dmqr->id;
        $item->material_item_id = $material->id;
        $item->quantity = 30;
        $item->save();

        $canvass = new DirectMaterialCanvass();
        $canvass->direct_material_quantity_request_id = $dmqr->id;
        $canvass->direct_material_quantity_request_item_id = $item->id;
        $canvass->supplier_id = $supplier->id;
        $canvass->payment_term_id = $paymentTerm->id;
        $canvass->price = 200.00;
        $canvass->status = 'APRV';
        $canvass->created_by = $this->user->id;
        $canvass->save();

        // Attempting to order 50 when only 30 requested
        $response = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/create', [
            'direct_material_quantity_request_id' => $dmqr->id,
            'supplier_id'                         => $supplier->id,
            'payment_term_id'                     => $paymentTerm->id,
            'project_id'                          => $project->id,
            'items'                               => json_encode([[
                'direct_material_quantity_request_item_id' => $item->id,
                'direct_material_canvass_id'               => $canvass->id,
                'material_item_id'                         => $material->id,
                'order_quantity'                           => 50,
                'price'                                    => 200.00
            ]])
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => -2]);
        $this->assertStringContainsString('exceeds remaining balance', $response->json('message'));
    }

    /** @test */
    public function it_returns_items_with_multiple_approved_suppliers_for_direct_po_endpoint()
    {
        $project = $this->createProject('Multi-Supplier Project');
        $mat1 = $this->createMaterialItem('Cement Bag');
        $mat2 = $this->createMaterialItem('Steel Bar');
        $supA = $this->createSupplier('Supplier Alpha');
        $supB = $this->createSupplier('Supplier Beta');

        $termA = new PaymentTerm();
        $termA->text = '30 Days Net';
        $termA->created_by = $this->user->id;
        $termA->save();

        $termB = new PaymentTerm();
        $termB->text = 'Cash on Delivery';
        $termB->created_by = $this->user->id;
        $termB->save();

        $dmqr = new DirectMaterialQuantityRequest();
        $dmqr->project_id = $project->id;
        $dmqr->status = 'APRV';
        $dmqr->created_by = $this->user->id;
        $dmqr->save();

        $item1 = new DirectMaterialQuantityRequestItem();
        $item1->direct_material_quantity_request_id = $dmqr->id;
        $item1->material_item_id = $mat1->id;
        $item1->quantity = 100;
        $item1->save();

        $item2 = new DirectMaterialQuantityRequestItem();
        $item2->direct_material_quantity_request_id = $dmqr->id;
        $item2->material_item_id = $mat2->id;
        $item2->quantity = 50;
        $item2->save();

        $canvass1 = new DirectMaterialCanvass();
        $canvass1->direct_material_quantity_request_id = $dmqr->id;
        $canvass1->direct_material_quantity_request_item_id = $item1->id;
        $canvass1->supplier_id = $supA->id;
        $canvass1->payment_term_id = $termA->id;
        $canvass1->price = 250.00;
        $canvass1->status = 'APRV';
        $canvass1->created_by = $this->user->id;
        $canvass1->save();

        $canvass2 = new DirectMaterialCanvass();
        $canvass2->direct_material_quantity_request_id = $dmqr->id;
        $canvass2->direct_material_quantity_request_item_id = $item2->id;
        $canvass2->supplier_id = $supB->id;
        $canvass2->payment_term_id = $termB->id;
        $canvass2->price = 1500.00;
        $canvass2->status = 'APRV';
        $canvass2->created_by = $this->user->id;
        $canvass2->save();

        $res = $this->actingAs($this->user)->getJson('/api/direct_material_quantity_request/for_direct_po/' . $dmqr->id);
        $res->assertStatus(200);
        $res->assertJson(['status' => 1]);

        $items = $res->json('data.items');
        $this->assertCount(2, $items);

        $suppliers = array_unique(array_column($items, 'supplier_id'));
        $this->assertCount(2, $suppliers);
        $this->assertContains($supA->id, $suppliers);
        $this->assertContains($supB->id, $suppliers);

        // Also verify the create page renders the multi-supplier handler script logic
        $createPage = $this->actingAs($this->user)->get('/direct_purchase_order/create');
        $createPage->assertStatus(200);
        $createPage->assertSee('renderDmqrItems', false);
    }

    /** @test */
    public function it_supports_same_supplier_with_different_payment_terms_in_direct_po()
    {
        $project = $this->createProject('Terms Project');
        $mat = $this->createMaterialItem('Cement 40kg');
        $sup = $this->createSupplier('Omni Supplier');

        $term1 = new PaymentTerm();
        $term1->text = 'Cash Term';
        $term1->created_by = $this->user->id;
        $term1->save();

        $term2 = new PaymentTerm();
        $term2->text = 'Credit 30 Days';
        $term2->created_by = $this->user->id;
        $term2->save();

        $dmqr = new DirectMaterialQuantityRequest();
        $dmqr->project_id = $project->id;
        $dmqr->status = 'APRV';
        $dmqr->created_by = $this->user->id;
        $dmqr->save();

        $item = new DirectMaterialQuantityRequestItem();
        $item->direct_material_quantity_request_id = $dmqr->id;
        $item->material_item_id = $mat->id;
        $item->quantity = 100;
        $item->save();

        // Same supplier, Cash term: $200
        $canvass1 = new DirectMaterialCanvass();
        $canvass1->direct_material_quantity_request_id = $dmqr->id;
        $canvass1->direct_material_quantity_request_item_id = $item->id;
        $canvass1->supplier_id = $sup->id;
        $canvass1->payment_term_id = $term1->id;
        $canvass1->price = 200.00;
        $canvass1->status = 'APRV';
        $canvass1->created_by = $this->user->id;
        $canvass1->save();

        // Same supplier, Credit 30 Days: $210
        $canvass2 = new DirectMaterialCanvass();
        $canvass2->direct_material_quantity_request_id = $dmqr->id;
        $canvass2->direct_material_quantity_request_item_id = $item->id;
        $canvass2->supplier_id = $sup->id;
        $canvass2->payment_term_id = $term2->id;
        $canvass2->price = 210.00;
        $canvass2->status = 'APRV';
        $canvass2->created_by = $this->user->id;
        $canvass2->save();

        $res = $this->actingAs($this->user)->getJson('/api/direct_material_quantity_request/for_direct_po/' . $dmqr->id);
        $res->assertStatus(200);
        $res->assertJson(['status' => 1]);

        $items = $res->json('data.items');
        $this->assertCount(2, $items);

        // Both items have the same supplier but different payment terms
        $this->assertEquals($sup->id, $items[0]['supplier_id']);
        $this->assertEquals($sup->id, $items[1]['supplier_id']);
        $this->assertNotEquals($items[0]['payment_term_id'], $items[1]['payment_term_id']);
        $this->assertEquals(200.00, $items[0]['price']);
        $this->assertEquals(210.00, $items[1]['price']);

        // Verify create view contains payment term filtering logic
        $createPage = $this->actingAs($this->user)->get('/direct_purchase_order/create');
        $createPage->assertStatus(200);
        $createPage->assertSee('updatePaymentTermsAndItemsForSupplier', false);
        $createPage->assertSee('renderDmqrItems', false);
    }
}
