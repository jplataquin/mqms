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

        // Single line item remove button management script
        $response->assertSee('updateItemRemoveButtons', false);
        $response->assertSee('At least one item is required to make a direct PO.', false);
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
    }
}
