<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\AccessCode;
use App\Models\RoleAccessCode;
use App\Models\Project;
use App\Models\Section;
use App\Models\Supplier;
use App\Models\PaymentTerm;
use App\Models\MaterialItem;
use App\Models\DirectMaterialQuantityRequest;
use App\Models\DirectMaterialQuantityRequestItem;
use App\Models\DirectMaterialCanvass;

class DirectMaterialQuantityRequestTest extends TestCase
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

        $this->grantAccessCode($this->user, 'material_request:own:create');
        $this->grantAccessCode($this->user, 'material_request:all:list');
        $this->grantAccessCode($this->user, 'material_request:all:view');
        $this->grantAccessCode($this->user, 'material_request:all:update');
        $this->grantAccessCode($this->user, 'material_request:all:approve');
        $this->grantAccessCode($this->user, 'material_request:all:reject');
        $this->grantAccessCode($this->user, 'material_request:all:delete');

        $this->grantAccessCode($this->user, 'material_canvass:all:list');
        $this->grantAccessCode($this->user, 'material_canvass:all:view');
        $this->grantAccessCode($this->user, 'material_canvass:own:create');
        $this->grantAccessCode($this->user, 'material_canvass:all:approve');
        $this->grantAccessCode($this->user, 'material_canvass:all:reject');
        $this->grantAccessCode($this->user, 'material_canvass:all:delete');
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

    protected function createProject($name = 'Test Project')
    {
        $project = new Project();
        $project->name = $name;
        $project->status = 'ACTV';
        $project->created_by = $this->user->id;
        $project->save();
        return $project;
    }

    protected function createSupplier($name = 'Test Supplier')
    {
        $supplier = new Supplier();
        $supplier->name = $name;
        $supplier->address = '123 Supplier Road';
        $supplier->primary_contact_no = '09123456789';
        $supplier->created_by = $this->user->id;
        $supplier->save();
        return $supplier;
    }

    protected function createMaterialItem($name = 'Test Material')
    {
        $item = new MaterialItem();
        $item->material_group_id = 1;
        $item->name = $name;
        $item->brand = 'Standard';
        $item->specification_unit_packaging = 'Unit';
        $item->created_by = $this->user->id;
        $item->save();
        return $item;
    }

    /** @test */
    public function it_can_render_the_direct_material_quantity_request_create_page()
    {
        $project = $this->createProject('Bridge Project');
        $material = $this->createMaterialItem('Cement Bag');

        $response = $this->actingAs($this->user)->get('/direct_material_quantity_request/create');
        $response->assertStatus(200);
        $response->assertSee('Bridge Project');
        $response->assertSee('Cement Bag');
        $response->assertSee('item-material-input', false);
        $response->assertSee('item-remarks', false);
    }

    /** @test */
    public function it_can_create_direct_material_quantity_request_in_draft_status()
    {
        $project = $this->createProject('Highway Project');
        $material = $this->createMaterialItem('Asphalt 50kg');

        $items = [
            [
                'material_item_id' => $material->id,
                'quantity'         => 100,
                'remarks'          => 'Emergency delivery needed'
            ]
        ];

        $response = $this->actingAs($this->user)->postJson('/api/direct_material_quantity_request/create', [
            'project_id'  => $project->id,
            'description' => 'For highway repair',
            'items'       => json_encode($items)
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $this->assertDatabaseHas('direct_material_quantity_requests', [
            'project_id'  => $project->id,
            'description' => 'For highway repair',
            'status'      => 'DRFT'
        ]);

        $this->assertDatabaseHas('direct_material_quantity_request_items', [
            'material_item_id' => $material->id,
            'quantity'         => 100,
            'remarks'          => 'Emergency delivery needed',
            'status'           => 'PEND'
        ]);
    }

    /** @test */
    public function it_can_submit_and_approve_direct_material_quantity_request()
    {
        $project = $this->createProject('Tunnel Project');
        $material = $this->createMaterialItem('Rebar 25mm');

        $dmqr = new DirectMaterialQuantityRequest();
        $dmqr->project_id = $project->id;
        $dmqr->status = 'DRFT';
        $dmqr->created_by = $this->user->id;
        $dmqr->save();

        $item = new DirectMaterialQuantityRequestItem();
        $item->direct_material_quantity_request_id = $dmqr->id;
        $item->material_item_id = $material->id;
        $item->quantity = 50;
        $item->save();

        // Submit for review
        $resSubmit = $this->actingAs($this->user)->postJson('/api/direct_material_quantity_request/submit_for_review', [
            'id' => $dmqr->id
        ]);
        $resSubmit->assertStatus(200);
        $resSubmit->assertJson(['status' => 1]);

        $this->assertEquals('PEND', $dmqr->fresh()->status);

        // Approve
        $resApprove = $this->actingAs($this->user)->postJson('/api/review/direct_material_quantity_request/approve', [
            'id' => $dmqr->id
        ]);
        $resApprove->assertStatus(200);
        $resApprove->assertJson(['status' => 1]);

        $this->assertEquals('APRV', $dmqr->fresh()->status);
    }

    /** @test */
    public function it_can_record_canvass_quotes_for_approved_dmqr_and_approve_one_quote()
    {
        $project = $this->createProject('Tower Project');
        $material = $this->createMaterialItem('Ready Mix Concrete');

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

        $supplier1 = $this->createSupplier('Supplier Alpha');
        $supplier2 = $this->createSupplier('Supplier Beta');

        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = '30 Days Net';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        // Add Quote 1 (Price 500)
        $quote1 = $this->actingAs($this->user)->postJson('/api/direct_material_canvass/create', [
            'direct_material_quantity_request_id'      => $dmqr->id,
            'direct_material_quantity_request_item_id' => $item->id,
            'supplier_id'                             => $supplier1->id,
            'payment_term_id'                         => $paymentTerm->id,
            'price'                                   => 500.00
        ]);
        $quote1->assertStatus(200);
        $quote1->assertJson(['status' => 1]);
        $quote1Id = $quote1->json('data.id');

        // Add Quote 2 (Price 450)
        $quote2 = $this->actingAs($this->user)->postJson('/api/direct_material_canvass/create', [
            'direct_material_quantity_request_id'      => $dmqr->id,
            'direct_material_quantity_request_item_id' => $item->id,
            'supplier_id'                             => $supplier2->id,
            'payment_term_id'                         => $paymentTerm->id,
            'price'                                   => 450.00
        ]);
        $quote2->assertStatus(200);
        $quote2->assertJson(['status' => 1]);
        $quote2Id = $quote2->json('data.id');

        // Approve Quote 2 (Supplier Beta)
        $approveRes = $this->actingAs($this->user)->postJson('/api/review/direct_material_canvass/approve', [
            'id' => $quote2Id
        ]);
        $approveRes->assertStatus(200);
        $approveRes->assertJson(['status' => 1]);

        // Quote 2 should be APRV, Quote 1 should be DPRV (automatically disapproved)
        $this->assertEquals('APRV', DirectMaterialCanvass::find($quote2Id)->status);
        $this->assertEquals('DPRV', DirectMaterialCanvass::find($quote1Id)->status);
    }
}
