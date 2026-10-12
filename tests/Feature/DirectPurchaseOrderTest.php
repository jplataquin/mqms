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

        // Searchable Project
        $response->assertSee('id="projectInput"', false);
        $response->assertSee('list="projectList"', false);
        $response->assertSee('Metro Expressway Phase 1');

        // Searchable Section
        $response->assertSee('id="sectionInput"', false);
        $response->assertSee('list="sectionList"', false);

        // Required Payment Term
        $response->assertSee('id="paymentTermSelect"', false);
        $response->assertSee('required', false);
        $response->assertSee('30 Days Credit');
    }

    /** @test */
    public function it_fails_creation_when_payment_term_is_missing_or_zero()
    {
        $supplier = $this->createSupplier('Concrete Pro');
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
            'items'           => json_encode($items),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
        ]);

        $this->assertDatabaseHas('direct_purchase_orders', [
            'supplier_id'     => $supplier->id,
            'payment_term_id' => $paymentTerm->id,
            'status'          => 'DRFT',
        ]);
    }
}
