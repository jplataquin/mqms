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

class DirectPurchaseOrderReviewTest extends TestCase
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
    }

    protected function grantAccessCode($user, $codeString)
    {
        $accessCode = AccessCode::where('code', $codeString)->first();
        if (!$accessCode) {
            $accessCode = new AccessCode();
            $accessCode->code = $codeString;
            $accessCode->description = 'Test code ' . $codeString;
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

    protected function createPaymentTerm($text = 'Net 30 Days')
    {
        $paymentTerm = new PaymentTerm();
        $paymentTerm->text = $text;
        $paymentTerm->created_by = $this->user->id;
        $paymentTerm->save();

        return $paymentTerm;
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

    protected function createDpo($status = 'PEND', $remarks = null)
    {
        $supplier = $this->createSupplier();
        $paymentTerm = $this->createPaymentTerm();
        $materialItem = $this->createMaterialItem();

        $dpo = new DirectPurchaseOrder();
        $dpo->supplier_id = $supplier->id;
        $dpo->payment_term_id = $paymentTerm->id;
        $dpo->status = $status;
        $dpo->created_by = $this->user->id;
        $dpo->save();

        $item = new DirectPurchaseOrderItem();
        $item->direct_purchase_order_id = $dpo->id;
        $item->material_item_id = $materialItem->id;
        $item->quantity = 10;
        $item->price = 250.00;
        $item->status = $status === 'APRV' ? 'APRV' : 'PEND';
        $item->remarks = $remarks;
        $item->save();

        return $dpo;
    }

    /** @test */
    public function it_denies_review_list_when_lacking_permission()
    {
        $response = $this->actingAs($this->user)->get('/review/direct_purchase_orders');
        $response->assertSee('Access Denied');
    }

    /** @test */
    public function it_can_render_the_review_list_page_with_permission()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:view');

        $response = $this->actingAs($this->user)->get('/review/direct_purchase_orders');
        $response->assertStatus(200);
        $response->assertSee('Review Direct Purchase Orders');
    }

    /** @test */
    public function it_returns_only_pending_and_revo_dpos_by_default_in_review_list_api()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:view');

        $draftDpo = $this->createDpo('DRFT');
        $pendingDpo = $this->createDpo('PEND');
        $revoDpo = $this->createDpo('REVO');
        $approvedDpo = $this->createDpo('APRV');

        $response = $this->actingAs($this->user)->getJson('/api/review/direct_purchase_order/list');
        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $ids = array_column($response->json('data'), 'id');
        $this->assertContains($pendingDpo->id, $ids);
        $this->assertContains($revoDpo->id, $ids);
        $this->assertNotContains($draftDpo->id, $ids);
        $this->assertNotContains($approvedDpo->id, $ids);
    }

    /** @test */
    public function it_can_render_the_review_display_page()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:view');
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:approve');
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:reject');

        $pendingDpo = $this->createDpo('PEND', 'Special packaging requested');

        $response = $this->actingAs($this->user)->get('/review/direct_purchase_order/' . $pendingDpo->id);
        $response->assertStatus(200);
        $response->assertSee('Review Direct Purchase Order');
        $response->assertSee('Approve');
        $response->assertSee('Reject');
        $response->assertSee('Portland Cement');
        $response->assertSee('Special packaging requested');
        $response->assertSee('Remarks');
        $response->assertDontSee('View Original DPO');
        $response->assertDontSee('bg-light');
        $response->assertDontSee('bg-white');
    }

    /** @test */
    public function it_allows_authorized_reviewer_to_approve_pending_direct_po()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:approve');

        $dpo = $this->createDpo('PEND');

        $response = $this->actingAs($this->user)->postJson('/api/review/direct_purchase_order/approve', [
            'id' => $dpo->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'message' => 'Direct Purchase Order approved successfully',
        ]);

        $dpo->refresh();
        $this->assertEquals('APRV', $dpo->status);
        $this->assertEquals($this->user->id, $dpo->approved_by);
        $this->assertNotNull($dpo->approved_at);

        $this->assertDatabaseHas('direct_purchase_order_items', [
            'direct_purchase_order_id' => $dpo->id,
            'status' => 'APRV',
        ]);
    }

    /** @test */
    public function it_allows_approval_via_alias_api_endpoint()
    {
        $this->grantAccessCode($this->user, 'purchase_order:all:approve');

        $dpo = $this->createDpo('PEND');

        $response = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/approve', [
            'id' => $dpo->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $dpo->refresh();
        $this->assertEquals('APRV', $dpo->status);
    }

    /** @test */
    public function it_prevents_approving_a_non_pending_direct_po()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:approve');

        $dpo = $this->createDpo('DRFT');

        $response = $this->actingAs($this->user)->postJson('/api/review/direct_purchase_order/approve', [
            'id' => $dpo->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 0,
            'message' => 'Record cannot be approved, status is not pending',
        ]);
    }

    /** @test */
    public function it_denies_approval_when_user_lacks_permission()
    {
        $dpo = $this->createDpo('PEND');

        $response = $this->actingAs($this->user)->postJson('/api/review/direct_purchase_order/approve', [
            'id' => $dpo->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 0,
            'message' => 'Access Denied',
        ]);

        $dpo->refresh();
        $this->assertEquals('PEND', $dpo->status);
    }

    /** @test */
    public function it_allows_authorized_reviewer_to_reject_pending_direct_po()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:reject');

        $dpo = $this->createDpo('PEND');

        $response = $this->actingAs($this->user)->postJson('/api/review/direct_purchase_order/reject', [
            'id' => $dpo->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'message' => 'Direct Purchase Order rejected successfully',
        ]);

        $dpo->refresh();
        $this->assertEquals('REJC', $dpo->status);
        $this->assertEquals($this->user->id, $dpo->rejected_by);
        $this->assertNotNull($dpo->rejected_at);

        $this->assertDatabaseHas('direct_purchase_order_items', [
            'direct_purchase_order_id' => $dpo->id,
            'status' => 'REJC',
        ]);
    }

    /** @test */
    public function it_allows_authorized_reviewer_to_void_a_revo_direct_po()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:void');

        $dpo = $this->createDpo('REVO');

        $response = $this->actingAs($this->user)->postJson('/api/review/direct_purchase_order/void', [
            'id' => $dpo->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'message' => 'Direct Purchase Order voided successfully',
        ]);

        $dpo->refresh();
        $this->assertEquals('VOID', $dpo->status);
        $this->assertEquals($this->user->id, $dpo->void_by);
        $this->assertNotNull($dpo->void_at);

        $this->assertDatabaseHas('direct_purchase_order_items', [
            'direct_purchase_order_id' => $dpo->id,
            'status' => 'VOID',
        ]);
    }

    /** @test */
    public function it_allows_authorized_reviewer_to_reject_void_request_restoring_to_aprv()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:reject_void_request');

        $dpo = $this->createDpo('REVO');

        $response = $this->actingAs($this->user)->postJson('/api/review/direct_purchase_order/reject_void', [
            'id' => $dpo->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'message' => 'Void request rejected',
        ]);

        $dpo->refresh();
        $this->assertEquals('APRV', $dpo->status);

        $this->assertDatabaseHas('direct_purchase_order_items', [
            'direct_purchase_order_id' => $dpo->id,
            'status' => 'APRV',
        ]);
    }

    /** @test */
    public function it_allows_authorized_user_to_directly_void_an_approved_direct_po()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:void');

        $dpo = $this->createDpo('APRV');

        $response = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/void', [
            'id' => $dpo->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'message' => 'Direct Purchase Order voided successfully',
        ]);

        $dpo->refresh();
        $this->assertEquals('VOID', $dpo->status);
        $this->assertEquals($this->user->id, $dpo->void_by);
        $this->assertNotNull($dpo->void_at);

        $this->assertDatabaseHas('direct_purchase_order_items', [
            'direct_purchase_order_id' => $dpo->id,
            'status' => 'VOID',
        ]);
    }

    /** @test */
    public function it_allows_authorized_reviewer_to_void_approved_direct_po_via_review_api()
    {
        $this->grantAccessCode($this->user, 'purchase_order:all:void');

        $dpo = $this->createDpo('APRV');

        $response = $this->actingAs($this->user)->postJson('/api/review/direct_purchase_order/void', [
            'id' => $dpo->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $dpo->refresh();
        $this->assertEquals('VOID', $dpo->status);
    }

    /** @test */
    public function it_allows_owner_with_own_void_access_to_void_their_approved_direct_po()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:own:void');

        $dpo = $this->createDpo('APRV');

        $response = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/void', [
            'id' => $dpo->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 1]);

        $dpo->refresh();
        $this->assertEquals('VOID', $dpo->status);
    }

    /** @test */
    public function it_denies_direct_void_to_unauthorized_user()
    {
        $dpo = $this->createDpo('APRV');

        $response = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/void', [
            'id' => $dpo->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 0,
            'message' => 'Access Denied',
        ]);

        $dpo->refresh();
        $this->assertEquals('APRV', $dpo->status);
    }

    /** @test */
    public function it_prevents_voiding_a_draft_or_pending_direct_po()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:void');

        $draftDpo = $this->createDpo('DRFT');

        $response = $this->actingAs($this->user)->postJson('/api/direct_purchase_order/void', [
            'id' => $draftDpo->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 0,
            'message' => 'Record cannot be voided, status must be approved or void requested',
        ]);

        $draftDpo->refresh();
        $this->assertEquals('DRFT', $draftDpo->status);
    }

    /** @test */
    public function it_renders_void_button_on_display_page_when_user_has_void_permission()
    {
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:view');
        $this->grantAccessCode($this->user, 'direct_purchase_order:all:void');

        $dpo = $this->createDpo('APRV');

        $response = $this->actingAs($this->user)->get('/direct_purchase_order/' . $dpo->id);
        $response->assertStatus(200);
        $response->assertSee('id="directVoidBtn"', false);
    }
}
