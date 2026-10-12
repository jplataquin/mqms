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
use App\Models\PurchaseOrderItem;
use App\Models\MaterialGroup;
use App\Models\MaterialItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $otherUser;
    protected $project;
    protected $materialGroup;
    protected $material1;
    protected $material2;

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

        $this->materialGroup = new MaterialGroup();
        $this->materialGroup->name = 'Metals';
        $this->materialGroup->created_by = $this->user->id;
        $this->materialGroup->save();

        $this->material1 = new MaterialItem();
        $this->material1->material_group_id = $this->materialGroup->id;
        $this->material1->name = 'Deformed Bar 16mm';
        $this->material1->specification_unit_packaging = '6m Length';
        $this->material1->brand = 'SteelCorp';
        $this->material1->created_by = $this->user->id;
        $this->material1->save();

        $this->material2 = new MaterialItem();
        $this->material2->material_group_id = $this->materialGroup->id;
        $this->material2->name = 'Portland Cement';
        $this->material2->specification_unit_packaging = '40kg Bag';
        $this->material2->brand = 'Holcim';
        $this->material2->created_by = $this->user->id;
        $this->material2->save();
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

        // Searchable Contains (Material Items)
        $response->assertSee('id="materialInput"', false);
        $response->assertSee('list="materialList"', false);
        $response->assertSee('placeholder="Search material item..."', false);
        $response->assertSee('id="clearMaterialBtn"', false);
        $response->assertSee('id="materialSelect"', false);
        $response->assertSee('id="materialList"', false);
        $response->assertSee('value="' . $this->material1->formattedName . '"', false);
        $response->assertSee('data-id="' . $this->material1->id . '"', false);
        $response->assertSee('value="' . $this->material2->formattedName . '"', false);
        $response->assertSee('data-id="' . $this->material2->id . '"', false);
    }

    /** @test */
    public function it_filters_purchase_order_api_list_by_contains_material_item()
    {
        $this->grantAccessCode($this->user, 'purchase_order:all:list');

        $po1 = $this->createPurchaseOrder($this->user->id);
        $poItem1 = new PurchaseOrderItem();
        $poItem1->purchase_order_id = $po1->id;
        $poItem1->component_item_id = 1;
        $poItem1->material_quantity_request_item_id = 1;
        $poItem1->material_canvass_id = 1;
        $poItem1->material_item_id = $this->material1->id;
        $poItem1->status = 'APRV';
        $poItem1->quantity = 10;
        $poItem1->price = 100.00;
        $poItem1->save();

        $po2 = $this->createPurchaseOrder($this->otherUser->id);
        $poItem2 = new PurchaseOrderItem();
        $poItem2->purchase_order_id = $po2->id;
        $poItem2->component_item_id = 1;
        $poItem2->material_quantity_request_item_id = 1;
        $poItem2->material_canvass_id = 1;
        $poItem2->material_item_id = $this->material2->id;
        $poItem2->status = 'APRV';
        $poItem2->quantity = 20;
        $poItem2->price = 200.00;
        $poItem2->save();

        // Query by Material 1
        $response1 = $this->actingAs($this->user)->getJson('/api/purchase_order/list?material_item_id=' . $this->material1->id);
        $response1->assertStatus(200);
        $poIds1 = array_column($response1->json('data'), 'id');
        $this->assertContains($po1->id, $poIds1);
        $this->assertNotContains($po2->id, $poIds1);

        // Query by Material 2
        $response2 = $this->actingAs($this->user)->getJson('/api/purchase_order/list?material_item_id=' . $this->material2->id);
        $response2->assertStatus(200);
        $poIds2 = array_column($response2->json('data'), 'id');
        $this->assertContains($po2->id, $poIds2);
        $this->assertNotContains($po1->id, $poIds2);

        // Query by non-existent / non-matching material ID
        $responseEmpty = $this->actingAs($this->user)->getJson('/api/purchase_order/list?material_item_id=-1');
        $responseEmpty->assertStatus(200);
        $this->assertEmpty($responseEmpty->json('data'));
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
