<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\AccessCode;
use App\Models\RoleAccessCode;
use App\Models\Project;
use App\Models\PurchaseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $otherUser;
    protected $project;

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
            'name' => 'Alice Admin',
            'email' => 'alice@example.com',
        ]);

        $this->otherUser = User::factory()->create([
            'name' => 'Bob Builder',
            'email' => 'bob@example.com',
        ]);

        $this->project = new Project();
        $this->project->name = 'Highway Extension';
        $this->project->status = 'ACTV';
        $this->project->created_by = $this->user->id;
        $this->project->save();
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
        $role->name = 'Role ' . $codeString . '_' . uniqid();
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

    protected function createPurchaseOrder($creatorId, $status = 'APRV')
    {
        $po = new PurchaseOrder();
        $po->project_id = $this->project->id;
        $po->section_id = 1;
        $po->component_id = 1;
        $po->contract_item_id = 1;
        $po->material_quantity_request_id = 1;
        $po->supplier_id = 1;
        $po->payment_term_id = 1;
        $po->status = $status;
        $po->received_status = 'PEND';
        $po->created_by = $creatorId;
        $po->extras = json_encode([]);
        $po->save();

        return $po;
    }

    /** @test */
    public function it_renders_purchase_order_list_with_searchable_created_by_filter_and_defaults_to_current_user()
    {
        $this->grantAccessCode($this->user, 'purchase_order:all:list');

        $response = $this->actingAs($this->user)->get('/purchase_orders');
        $response->assertStatus(200);

        // Searchable Created By input field
        $response->assertSee('id="createdByInput"', false);
        $response->assertSee('list="createdByList"', false);
        $response->assertSee('placeholder="Search user..."', false);
        $response->assertSee('value="Alice Admin"', false);

        // Clear button
        $response->assertSee('id="clearCreatedByBtn"', false);

        // Hidden input storing selected user ID defaulted to current user
        $response->assertSee('id="createdBySelect"', false);
        $response->assertSee('value="' . $this->user->id . '"', false);

        // Datalist with all users
        $response->assertSee('id="createdByList"', false);
        $response->assertSee('value="Alice Admin"', false);
        $response->assertSee('data-id="' . $this->user->id . '"', false);
        $response->assertSee('value="Bob Builder"', false);
        $response->assertSee('data-id="' . $this->otherUser->id . '"', false);
    }

    /** @test */
    public function it_filters_purchase_order_api_list_by_created_by()
    {
        $this->grantAccessCode($this->user, 'purchase_order:all:list');

        $po1 = $this->createPurchaseOrder($this->user->id);
        $po2 = $this->createPurchaseOrder($this->otherUser->id);

        // Filter by Alice (current user)
        $responseAlice = $this->actingAs($this->user)->getJson('/api/purchase_order/list?created_by=' . $this->user->id);
        $responseAlice->assertStatus(200);
        $responseAlice->assertJson(['status' => 1]);

        $dataAlice = $responseAlice->json('data');
        $poIdsAlice = array_column($dataAlice, 'id');
        $this->assertContains($po1->id, $poIdsAlice);
        $this->assertNotContains($po2->id, $poIdsAlice);

        // Filter by Bob (other user)
        $responseBob = $this->actingAs($this->user)->getJson('/api/purchase_order/list?created_by=' . $this->otherUser->id);
        $responseBob->assertStatus(200);
        $responseBob->assertJson(['status' => 1]);

        $dataBob = $responseBob->json('data');
        $poIdsBob = array_column($dataBob, 'id');
        $this->assertContains($po2->id, $poIdsBob);
        $this->assertNotContains($po1->id, $poIdsBob);
    }

    /** @test */
    public function it_returns_all_purchase_orders_when_created_by_filter_is_cleared_for_user_with_all_list_access()
    {
        $this->grantAccessCode($this->user, 'purchase_order:all:list');

        $po1 = $this->createPurchaseOrder($this->user->id);
        $po2 = $this->createPurchaseOrder($this->otherUser->id);

        // When created_by is omitted
        $responseAll = $this->actingAs($this->user)->getJson('/api/purchase_order/list');
        $responseAll->assertStatus(200);
        $poIdsAll = array_column($responseAll->json('data'), 'id');
        $this->assertContains($po1->id, $poIdsAll);
        $this->assertContains($po2->id, $poIdsAll);

        // When created_by is empty string
        $responseEmpty = $this->actingAs($this->user)->getJson('/api/purchase_order/list?created_by=');
        $responseEmpty->assertStatus(200);
        $poIdsEmpty = array_column($responseEmpty->json('data'), 'id');
        $this->assertContains($po1->id, $poIdsEmpty);
        $this->assertContains($po2->id, $poIdsEmpty);
    }

    /** @test */
    public function it_returns_empty_when_created_by_does_not_match_any_user()
    {
        $this->grantAccessCode($this->user, 'purchase_order:all:list');

        $this->createPurchaseOrder($this->user->id);

        $response = $this->actingAs($this->user)->getJson('/api/purchase_order/list?created_by=-1');
        $response->assertStatus(200);
        $this->assertEmpty($response->json('data'));
    }

    /** @test */
    public function it_restricts_to_own_purchase_orders_when_user_only_has_own_list_access()
    {
        $this->grantAccessCode($this->user, 'purchase_order:own:list');

        $po1 = $this->createPurchaseOrder($this->user->id);
        $po2 = $this->createPurchaseOrder($this->otherUser->id);

        // Should only return Alice's PO even if created_by is omitted
        $response = $this->actingAs($this->user)->getJson('/api/purchase_order/list');
        $response->assertStatus(200);
        $poIds = array_column($response->json('data'), 'id');
        $this->assertContains($po1->id, $poIds);
        $this->assertNotContains($po2->id, $poIds);

        // Attempting to query Bob's PO yields empty data due to permission restriction
        $responseBob = $this->actingAs($this->user)->getJson('/api/purchase_order/list?created_by=' . $this->otherUser->id);
        $responseBob->assertStatus(200);
        $this->assertEmpty($responseBob->json('data'));
    }
}
