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
use App\Models\DirectPurchaseOrder;

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
    public function it_can_create_direct_material_quantity_request_in_pending_status()
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

        $dmqrId = $response->json('data.id');

        $this->assertDatabaseHas('direct_material_quantity_requests', [
            'id'          => $dmqrId,
            'project_id'  => $project->id,
            'description' => 'For highway repair',
            'status'      => 'PEND'
        ]);

        $this->assertDatabaseHas('direct_material_quantity_request_items', [
            'direct_material_quantity_request_id' => $dmqrId,
            'material_item_id' => $material->id,
            'quantity'         => 100,
            'remarks'          => 'Emergency delivery needed',
            'status'           => 'PEND'
        ]);

        // Verify delete button is displayed on display page while in PEND status
        $displayRes = $this->actingAs($this->user)->get('/direct_material_quantity_request/' . $dmqrId);
        $displayRes->assertStatus(200);
        $displayRes->assertSee('id="deleteBtn"', false);

        // Verify deleting while in PEND status succeeds
        $deleteRes = $this->actingAs($this->user)->postJson('/api/direct_material_quantity_request/delete', [
            'id' => $dmqrId
        ]);
        $deleteRes->assertStatus(200);
        $deleteRes->assertJson(['status' => 1]);

        $this->assertDatabaseMissing('direct_material_quantity_requests', [
            'id' => $dmqrId
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

    /** @test */
    public function it_renders_direct_material_quantity_request_views_with_content_container_for_htmx()
    {
        $project = $this->createProject('HTMX Test Project');
        $material = $this->createMaterialItem('HTMX Test Item');

        $dmqr = new DirectMaterialQuantityRequest();
        $dmqr->project_id = $project->id;
        $dmqr->status = 'DRFT';
        $dmqr->created_by = $this->user->id;
        $dmqr->save();

        $item = new DirectMaterialQuantityRequestItem();
        $item->direct_material_quantity_request_id = $dmqr->id;
        $item->material_item_id = $material->id;
        $item->quantity = 10;
        $item->save();

        // 1. List view
        $resList = $this->actingAs($this->user)->get('/direct_material_quantity_requests');
        $resList->assertStatus(200);
        $resList->assertSee('<div id="content">', false);

        // 2. Create view
        $resCreate = $this->actingAs($this->user)->get('/direct_material_quantity_request/create');
        $resCreate->assertStatus(200);
        $resCreate->assertSee('<div id="content">', false);

        // 3. Display view
        $resDisplay = $this->actingAs($this->user)->get('/direct_material_quantity_request/' . $dmqr->id);
        $resDisplay->assertStatus(200);
        $resDisplay->assertSee('<div id="content">', false);

        // Submit for review to test review views
        $dmqr->status = 'PEND';
        $dmqr->save();

        // 4. Review List view
        $resReviewList = $this->actingAs($this->user)->get('/review/direct_material_quantity_requests');
        $resReviewList->assertStatus(200);
        $resReviewList->assertSee('<div id="content">', false);

        // 5. Review Display view
        $resReviewDisplay = $this->actingAs($this->user)->get('/review/direct_material_quantity_request/' . $dmqr->id);
        $resReviewDisplay->assertStatus(200);
        $resReviewDisplay->assertSee('<div id="content">', false);

        // Approve to test canvass views
        $dmqr->status = 'APRV';
        $dmqr->save();

        // 6. Direct Material Canvass List view
        $resCanvassList = $this->actingAs($this->user)->get('/direct_material_canvass');
        $resCanvassList->assertStatus(200);
        $resCanvassList->assertSee('<div id="content">', false);

        // 7. Direct Material Canvass Display view
        $resCanvassDisplay = $this->actingAs($this->user)->get('/direct_material_canvass/' . $dmqr->id);
        $resCanvassDisplay->assertStatus(200);
        $resCanvassDisplay->assertSee('<div id="content">', false);

        // Add a canvass quote to test review canvass views
        $supplier = $this->createSupplier('Canvass Supplier');
        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Cash';
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        $canvass = new DirectMaterialCanvass();
        $canvass->direct_material_quantity_request_id = $dmqr->id;
        $canvass->direct_material_quantity_request_item_id = $item->id;
        $canvass->supplier_id = $supplier->id;
        $canvass->payment_term_id = $paymentTerm->id;
        $canvass->price = 100.00;
        $canvass->status = 'PEND';
        $canvass->created_by = $this->user->id;
        $canvass->save();

        // 8. Review Direct Material Canvass List view
        $resReviewCanvassList = $this->actingAs($this->user)->get('/review/direct_material_canvass');
        $resReviewCanvassList->assertStatus(200);
        $resReviewCanvassList->assertSee('<div id="content">', false);

        // 9. Review Direct Material Canvass Display view
        $resReviewCanvassDisplay = $this->actingAs($this->user)->get('/review/direct_material_canvass/' . $dmqr->id);
        $resReviewCanvassDisplay->assertStatus(200);
        $resReviewCanvassDisplay->assertSee('<div id="content">', false);
        $resReviewCanvassDisplay->assertSee('bi-x-lg', false);
        $resReviewCanvassDisplay->assertSee('bi-check-lg', false);
    }

    /** @test */
    public function it_renders_print_view_with_qr_code_and_hash_code()
    {
        $project = $this->createProject('Print QR Project');
        $material = $this->createMaterialItem('Print QR Item');

        $dmqr = new DirectMaterialQuantityRequest();
        $dmqr->project_id = $project->id;
        $dmqr->status = 'PEND';
        $dmqr->created_by = $this->user->id;
        $dmqr->save();

        $item = new DirectMaterialQuantityRequestItem();
        $item->direct_material_quantity_request_id = $dmqr->id;
        $item->material_item_id = $material->id;
        $item->quantity = 15;
        $item->remarks = 'Fragile';
        $item->save();

        $hashCode = $dmqr->getHashCode();
        $this->assertNotEmpty($hashCode);
        $this->assertEquals(6, strlen($hashCode));

        $res = $this->actingAs($this->user)->get('/direct_material_quantity_request/print/' . $dmqr->id);
        $res->assertStatus(200);
        $res->assertSee('class="qr_code"', false);
        $res->assertSee('/qrcode?d=', false);
        $res->assertSee('/review/direct_material_quantity_requests/qr/approve/' . $dmqr->id . '/' . $hashCode, false);
        $res->assertSee($hashCode);
    }

    /** @test */
    public function it_can_approve_direct_material_quantity_request_via_qr_code()
    {
        $project = $this->createProject('QR Approve Project');
        $material = $this->createMaterialItem('QR Approve Item');

        $dmqr = new DirectMaterialQuantityRequest();
        $dmqr->project_id = $project->id;
        $dmqr->status = 'PEND';
        $dmqr->created_by = $this->user->id;
        $dmqr->save();

        $item = new DirectMaterialQuantityRequestItem();
        $item->direct_material_quantity_request_id = $dmqr->id;
        $item->material_item_id = $material->id;
        $item->quantity = 25;
        $item->status = 'PEND';
        $item->save();

        $hashCode = $dmqr->getHashCode();

        $res = $this->actingAs($this->user)->get('/review/direct_material_quantity_requests/qr/approve/' . $dmqr->id . '/' . $hashCode);
        $res->assertStatus(200);
        $res->assertSee('Approved');
        $res->assertSee($hashCode);
        $res->assertSee('DMR: ' . str_pad($dmqr->id, 6, '0', STR_PAD_LEFT));

        $dmqr->refresh();
        $this->assertEquals('APRV', $dmqr->status);
        $this->assertEquals($this->user->id, $dmqr->approved_by);
        $this->assertNotNull($dmqr->approved_at);

        $item->refresh();
        $this->assertEquals('APRV', $item->status);
    }

    /** @test */
    public function it_denies_qr_approval_when_user_lacks_permission()
    {
        $unauthorizedUser = User::factory()->create(['status' => 'ACTV']);

        $project = $this->createProject('Denied QR Project');
        $material = $this->createMaterialItem('Denied QR Item');

        $dmqr = new DirectMaterialQuantityRequest();
        $dmqr->project_id = $project->id;
        $dmqr->status = 'PEND';
        $dmqr->created_by = $this->user->id;
        $dmqr->save();

        $item = new DirectMaterialQuantityRequestItem();
        $item->direct_material_quantity_request_id = $dmqr->id;
        $item->material_item_id = $material->id;
        $item->quantity = 5;
        $item->save();

        $hashCode = $dmqr->getHashCode();

        $res = $this->actingAs($unauthorizedUser)->get('/review/direct_material_quantity_requests/qr/approve/' . $dmqr->id . '/' . $hashCode);
        $res->assertStatus(200);
        $res->assertSee('--Access Denied--');

        $dmqr->refresh();
        $this->assertEquals('PEND', $dmqr->status);
    }

    /** @test */
    public function it_rejects_qr_approval_when_hash_code_is_invalid()
    {
        $project = $this->createProject('Invalid Hash Project');
        $material = $this->createMaterialItem('Invalid Hash Item');

        $dmqr = new DirectMaterialQuantityRequest();
        $dmqr->project_id = $project->id;
        $dmqr->status = 'PEND';
        $dmqr->created_by = $this->user->id;
        $dmqr->save();

        $item = new DirectMaterialQuantityRequestItem();
        $item->direct_material_quantity_request_id = $dmqr->id;
        $item->material_item_id = $material->id;
        $item->quantity = 5;
        $item->save();

        $res = $this->actingAs($this->user)->get('/review/direct_material_quantity_requests/qr/approve/' . $dmqr->id . '/WRONG1');
        $res->assertStatus(200);
        $res->assertSee('The printed copy is not updated');

        $dmqr->refresh();
        $this->assertEquals('PEND', $dmqr->status);
    }

    /** @test */
    public function it_rejects_qr_approval_when_record_is_not_in_pending_status()
    {
        $project = $this->createProject('Not Pending Project');
        $material = $this->createMaterialItem('Not Pending Item');

        $dmqr = new DirectMaterialQuantityRequest();
        $dmqr->project_id = $project->id;
        $dmqr->status = 'APRV';
        $dmqr->created_by = $this->user->id;
        $dmqr->save();

        $item = new DirectMaterialQuantityRequestItem();
        $item->direct_material_quantity_request_id = $dmqr->id;
        $item->material_item_id = $material->id;
        $item->quantity = 5;
        $item->save();

        $hashCode = $dmqr->getHashCode();

        $res = $this->actingAs($this->user)->get('/review/direct_material_quantity_requests/qr/approve/' . $dmqr->id . '/' . $hashCode);
        $res->assertStatus(200);
        $res->assertSee('The record is no longer in pending status');
    }

    /** @test */
    public function it_can_revert_approved_direct_material_quantity_request_to_pending()
    {
        $project = $this->createProject('Revert Project');
        $material = $this->createMaterialItem('Revert Item');

        $dmqr = new DirectMaterialQuantityRequest();
        $dmqr->project_id = $project->id;
        $dmqr->status = 'APRV';
        $dmqr->approved_by = $this->user->id;
        $dmqr->approved_at = now();
        $dmqr->created_by = $this->user->id;
        $dmqr->save();

        $item = new DirectMaterialQuantityRequestItem();
        $item->direct_material_quantity_request_id = $dmqr->id;
        $item->material_item_id = $material->id;
        $item->quantity = 10;
        $item->status = 'APRV';
        $item->save();

        $res = $this->actingAs($this->user)->postJson('/api/direct_material_quantity_request/revert_to_pending', [
            'id' => $dmqr->id
        ]);

        $res->assertStatus(200);
        $res->assertJson(['status' => 1]);

        $dmqr->refresh();
        $this->assertEquals('PEND', $dmqr->status);
        $this->assertNull($dmqr->approved_by);
        $this->assertNull($dmqr->approved_at);

        $item->refresh();
        $this->assertEquals('PEND', $item->status);
    }

    /** @test */
    public function it_prevents_reverting_when_dependent_direct_po_or_canvass_exists()
    {
        $project = $this->createProject('Dependency Project');
        $material = $this->createMaterialItem('Dependency Item');
        $supplier = $this->createSupplier('Dependency Supplier');

        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Cash on Delivery';
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
        $item->quantity = 10;
        $item->status = 'APRV';
        $item->save();

        // 1. With approved canvass quote
        $canvass = new DirectMaterialCanvass();
        $canvass->direct_material_quantity_request_id = $dmqr->id;
        $canvass->direct_material_quantity_request_item_id = $item->id;
        $canvass->supplier_id = $supplier->id;
        $canvass->payment_term_id = $paymentTerm->id;
        $canvass->price = 50.00;
        $canvass->status = 'APRV';
        $canvass->created_by = $this->user->id;
        $canvass->save();

        $resCanvass = $this->actingAs($this->user)->postJson('/api/direct_material_quantity_request/revert_to_pending', [
            'id' => $dmqr->id
        ]);
        $resCanvass->assertStatus(200);
        $resCanvass->assertJson(['status' => 0]);
        $this->assertStringContainsString('canvass', strtolower($resCanvass->json('message')));

        // Delete the canvass to test PO dependency
        $canvass->delete();

        // 2. With dependent direct purchase order
        $dpo = new DirectPurchaseOrder();
        $dpo->project_id = $project->id;
        $dpo->supplier_id = $supplier->id;
        $dpo->payment_term_id = $paymentTerm->id;
        $dpo->direct_material_quantity_request_id = $dmqr->id;
        $dpo->status = 'APRV';
        $dpo->created_by = $this->user->id;
        $dpo->save();

        $resPo = $this->actingAs($this->user)->postJson('/api/direct_material_quantity_request/revert_to_pending', [
            'id' => $dmqr->id
        ]);
        $resPo->assertStatus(200);
        $resPo->assertJson(['status' => 0]);
        $this->assertStringContainsString('purchase order', strtolower($resPo->json('message')));
    }

    /** @test */
    public function it_renders_review_link_button_only_when_status_is_pending()
    {
        $project = $this->createProject('Review Link Project');
        $material = $this->createMaterialItem('Review Link Item');

        $dmqr = new DirectMaterialQuantityRequest();
        $dmqr->project_id = $project->id;
        $dmqr->status = 'PEND';
        $dmqr->created_by = $this->user->id;
        $dmqr->save();

        $item = new DirectMaterialQuantityRequestItem();
        $item->direct_material_quantity_request_id = $dmqr->id;
        $item->material_item_id = $material->id;
        $item->quantity = 20;
        $item->save();

        // 1. Pending status: Review Link button MUST be visible
        $resPending = $this->actingAs($this->user)->get('/direct_material_quantity_request/' . $dmqr->id);
        $resPending->assertStatus(200);
        $resPending->assertSee('id="reviewLinkBtn"', false);
        $resPending->assertSee('Review Link');
        $resPending->assertSee('/review/direct_material_quantity_request/' . $dmqr->id, false);

        // 2. Draft status: Review Link button must NOT be visible
        $dmqr->status = 'DRFT';
        $dmqr->save();

        $resDraft = $this->actingAs($this->user)->get('/direct_material_quantity_request/' . $dmqr->id);
        $resDraft->assertStatus(200);
        $resDraft->assertDontSee('id="reviewLinkBtn"', false);

        // 3. Approved status: Review Link button must NOT be visible
        $dmqr->status = 'APRV';
        $dmqr->save();

        $resApproved = $this->actingAs($this->user)->get('/direct_material_quantity_request/' . $dmqr->id);
        $resApproved->assertStatus(200);
        $resApproved->assertDontSee('id="reviewLinkBtn"', false);
    }

    /** @test */
    public function it_can_generate_qr_code_image_via_qrcode_endpoint()
    {
        $res = $this->actingAs($this->user)->get('/qrcode?d=' . urlencode('https://example.com/test'));
        $res->assertStatus(200);
        $this->assertContains($res->headers->get('content-type'), ['image/png', 'image/svg+xml']);
        $this->assertGreaterThan(0, strlen($res->getContent()));
    }

    /** @test */
    public function it_renders_review_link_in_direct_material_canvass_views()
    {
        $project = $this->createProject('Canvass Review Link Project');
        $material = $this->createMaterialItem('Canvass Review Link Item');

        $dmqr = new DirectMaterialQuantityRequest();
        $dmqr->project_id = $project->id;
        $dmqr->status = 'APRV';
        $dmqr->created_by = $this->user->id;
        $dmqr->save();

        $item = new DirectMaterialQuantityRequestItem();
        $item->direct_material_quantity_request_id = $dmqr->id;
        $item->material_item_id = $material->id;
        $item->quantity = 15;
        $item->status = 'APRV';
        $item->save();

        // 1. Direct Material Canvass Display view should contain the review link button in the details area and URL
        $resDisplay = $this->actingAs($this->user)->get('/direct_material_canvass/' . $dmqr->id);
        $resDisplay->assertStatus(200);
        $resDisplay->assertSee('id="reviewLinkBtn"', false);
        $resDisplay->assertSee('review-link-btn', false);
        $resDisplay->assertSee('Review Link');
        $resDisplay->assertSee('/review/direct_material_canvass/' . $dmqr->id, false);

        // 2. Direct Material Canvass List view should not place review link per item row
        $resList = $this->actingAs($this->user)->get('/direct_material_canvass');
        $resList->assertStatus(200);
        $resList->assertDontSee('copy-review-link-btn', false);
    }

    /** @test */
    public function it_renders_single_void_button_when_direct_material_canvass_is_approved_and_can_void_it()
    {
        $project = $this->createProject('Void Canvass Project');
        $material = $this->createMaterialItem('Void Canvass Material');
        $supplier = $this->createSupplier('Void Canvass Supplier');
        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Cash';
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
        $item->quantity = 25;
        $item->status = 'APRV';
        $item->save();

        $canvass = new DirectMaterialCanvass();
        $canvass->direct_material_quantity_request_id = $dmqr->id;
        $canvass->direct_material_quantity_request_item_id = $item->id;
        $canvass->supplier_id = $supplier->id;
        $canvass->payment_term_id = $paymentTerm->id;
        $canvass->price = 100.00;
        $canvass->status = 'APRV';
        $canvass->created_by = $this->user->id;
        $canvass->approved_by = $this->user->id;
        $canvass->approved_at = now();
        $canvass->save();

        // 1. In Review display view: should see the single void button in action column
        $resReview = $this->actingAs($this->user)->get('/review/direct_material_canvass/' . $dmqr->id);
        $resReview->assertStatus(200);
        $resReview->assertSee('void-btn', false);
        $resReview->assertSee('Void');
        // Should not see approve or reject buttons for an approved quote
        $resReview->assertDontSee('<button type="button" class="btn btn-sm btn-success approve-btn"', false);
        $resReview->assertDontSee('<button type="button" class="btn btn-sm btn-outline-danger reject-btn"', false);

        // 2. In Direct Material Canvass display view: should also see single void button
        $resCanvass = $this->actingAs($this->user)->get('/direct_material_canvass/' . $dmqr->id);
        $resCanvass->assertStatus(200);
        $resCanvass->assertSee('void-canvass-btn', false);
        $resCanvass->assertDontSee('<button type="button" class="btn btn-sm btn-outline-danger delete-canvass-btn"', false);

        // 3. Void the canvass via API
        $voidRes = $this->actingAs($this->user)->postJson('/api/review/direct_material_canvass/void', [
            'id' => $canvass->id
        ]);
        $voidRes->assertStatus(200);
        $voidRes->assertJson(['status' => 1]);

        $canvass->refresh();
        $this->assertEquals('VOID', $canvass->status);
        $this->assertEquals($this->user->id, $canvass->void_by);
        $this->assertNotNull($canvass->void_at);

        // 4. After voiding, the void button is no longer shown
        $resReviewAfter = $this->actingAs($this->user)->get('/review/direct_material_canvass/' . $dmqr->id);
        $resReviewAfter->assertStatus(200);
        $resReviewAfter->assertDontSee('<button type="button" class="btn btn-sm btn-outline-danger void-btn"', false);
    }

    /** @test */
    public function it_prevents_voiding_approved_canvass_when_active_direct_po_exists()
    {
        $project = $this->createProject('Dep Void Project');
        $material = $this->createMaterialItem('Dep Void Material');
        $supplier = $this->createSupplier('Dep Void Supplier');
        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = 'Cash';
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
        $item->quantity = 10;
        $item->status = 'APRV';
        $item->save();

        $canvass = new DirectMaterialCanvass();
        $canvass->direct_material_quantity_request_id = $dmqr->id;
        $canvass->direct_material_quantity_request_item_id = $item->id;
        $canvass->supplier_id = $supplier->id;
        $canvass->payment_term_id = $paymentTerm->id;
        $canvass->price = 50.00;
        $canvass->status = 'APRV';
        $canvass->created_by = $this->user->id;
        $canvass->approved_by = $this->user->id;
        $canvass->approved_at = now();
        $canvass->save();

        $dpo = new DirectPurchaseOrder();
        $dpo->project_id = $project->id;
        $dpo->supplier_id = $supplier->id;
        $dpo->payment_term_id = $paymentTerm->id;
        $dpo->status = 'DRFT';
        $dpo->created_by = $this->user->id;
        $dpo->save();

        $dpoItem = new \App\Models\DirectPurchaseOrderItem();
        $dpoItem->direct_purchase_order_id = $dpo->id;
        $dpoItem->direct_material_quantity_request_item_id = $item->id;
        $dpoItem->direct_material_canvass_id = $canvass->id;
        $dpoItem->material_item_id = $material->id;
        $dpoItem->quantity = 10;
        $dpoItem->price = 50.00;
        $dpoItem->save();

        $res = $this->actingAs($this->user)->postJson('/api/review/direct_material_canvass/void', [
            'id' => $canvass->id
        ]);
        $res->assertStatus(200);
        $res->assertJson([
            'status' => 0,
            'message' => 'Record dependency issue, this canvass has active Direct Purchase Order(s) that are dependent.'
        ]);

        $canvass->refresh();
        $this->assertEquals('APRV', $canvass->status);
    }
}
