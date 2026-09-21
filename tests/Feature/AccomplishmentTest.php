<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccomplishmentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;
    protected $section;
    protected $contractItem;
    protected $component;
    protected $unit;

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

        // Create a user and authenticate
        $this->user = User::factory()->create();

        // Create a unit first
        $this->unit = new Unit();
        $this->unit->text = 'Pcs';
        $this->unit->created_by = $this->user->id;
        $this->unit->save();

        // Create project, section, contract item, and component
        $this->project = new Project();
        $this->project->name = 'Test Project 123';
        $this->project->status = 'ACTV';
        $this->project->created_by = $this->user->id;
        $this->project->save();

        $this->section = new Section();
        $this->section->project_id = $this->project->id;
        $this->section->name = 'Test Section 123';
        $this->section->gross_total_amount = 500000.00;
        $this->section->created_by = $this->user->id;
        $this->section->save();

        $this->contractItem = new ContractItem();
        $this->contractItem->section_id = $this->section->id;
        $this->contractItem->item_type = 'MAT';
        $this->contractItem->item_code = 'C-101';
        $this->contractItem->description = 'Test Contract Item 123';
        $this->contractItem->contract_quantity = 100;
        $this->contractItem->unit_id = $this->unit->id;
        $this->contractItem->contract_unit_price = 1500.00;
        $this->contractItem->created_by = $this->user->id;
        $this->contractItem->save();

        $this->component = new Component();
        $this->component->name = 'Test Component 123';
        $this->component->contract_item_id = $this->contractItem->id;
        $this->component->quantity = 50;
        $this->component->unit_id = $this->unit->id;
        $this->component->use_count = 1;
        $this->component->status = 'APRV';
        $this->component->section_id = $this->section->id;
        $this->component->created_by = $this->user->id;
        $this->component->save();
    }

    /** @test */
    public function it_can_render_the_accomplishment_projects_list_page()
    {
        $response = $this->actingAs($this->user)->get('/accomplishment');
        $response->assertStatus(200);
        $response->assertSee('Accomplishment');
    }

    /** @test */
    public function it_can_render_the_accomplishment_sections_list_page()
    {
        $response = $this->actingAs($this->user)->get('/accomplishment/project/' . $this->project->id);
        $response->assertStatus(200);
        $response->assertSee('Test Project 123');
    }

    /** @test */
    public function it_can_render_the_accomplishment_contract_items_list_page()
    {
        $response = $this->actingAs($this->user)->get('/accomplishment/section/' . $this->section->id);
        $response->assertStatus(200);
        $response->assertSee('Test Section 123');
    }

    /** @test */
    public function it_can_render_the_accomplishment_components_list_page()
    {
        $response = $this->actingAs($this->user)->get('/accomplishment/contract_item/' . $this->contractItem->id);
        $response->assertStatus(200);
        $response->assertSee('Test Contract Item 123');
    }

    /** @test */
    public function it_can_render_the_blank_accomplishment_component_page()
    {
        $response = $this->actingAs($this->user)->get('/accomplishment/component/' . $this->component->id);
        $response->assertStatus(200);
        $response->assertSee('Test Component 123');
        $response->assertSee('Add Registry Entry');
        $response->assertSee('/accomplishment/component/' . $this->component->id . '/add');
        $response->assertSee('Total Quantity');
        $response->assertSee('50 Pcs');
        $response->assertSee('No accomplishment records found');
        // Ensure container exists
        $response->assertSee('id="list"', false);
    }

    /** @test */
    public function it_can_render_the_accomplishment_component_page_with_records()
    {
        // Create an accomplishment record for this component
        $accomplishment = new \App\Models\Accomplishment();
        $accomplishment->component_id = $this->component->id;
        $accomplishment->type = 'ACTUAL';
        $accomplishment->entry_data = '2026-09-21';
        $accomplishment->quantity = 35.50;
        $accomplishment->remarks = 'Completed most of it';
        $accomplishment->created_by = $this->user->id;
        $accomplishment->save();

        // 1. Verify page renders successfully
        $response = $this->actingAs($this->user)->get('/accomplishment/component/' . $this->component->id);
        $response->assertStatus(200);
        $response->assertSee('Test Component 123');
        $response->assertSee('Latest Quantity');
        $response->assertSee('35.50 Pcs (71%) as of 2026-09-21.');

        // 2. Verify API returns accomplishment list correctly (since records are loaded via AJAX)
        $apiResponse = $this->actingAs($this->user)->get('/api/accomplishment/record/list?component_id=' . $this->component->id);
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonFragment([
            'component_id' => $this->component->id,
            'type' => 'ACTUAL',
            'quantity' => 35.50,
            'remarks' => 'Completed most of it',
            'creator_name' => $this->user->name
        ]);
    }

    /** @test */
    public function it_can_fetch_projects_via_api()
    {
        $response = $this->actingAs($this->user)->get('/api/accomplishment/project/list?query=Test Project 123');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Test Project 123',
            'status' => 'ACTV'
        ]);
    }

    /** @test */
    public function it_can_fetch_sections_via_api()
    {
        $response = $this->actingAs($this->user)->get('/api/accomplishment/section/list?project_id=' . $this->project->id);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Test Section 123'
        ]);
    }

    /** @test */
    public function it_can_fetch_contract_items_via_api()
    {
        $response = $this->actingAs($this->user)->get('/api/accomplishment/contract_item/list?section_id=' . $this->section->id);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'description' => 'Test Contract Item 123'
        ]);
    }

    /** @test */
    public function it_can_fetch_components_via_api()
    {
        $response = $this->actingAs($this->user)->get('/api/accomplishment/component/list?contract_item_id=' . $this->contractItem->id);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Test Component 123',
            'status' => 'APRV'
        ]);
    }

    /** @test */
    public function it_can_create_and_manage_accomplishment_records()
    {
        // 1. Create Accomplishment record
        $accomplishment = new \App\Models\Accomplishment();
        $accomplishment->component_id = $this->component->id;
        $accomplishment->type = 'ACTUAL';
        $accomplishment->entry_data = '2026-08-12';
        $accomplishment->quantity = 25.5;
        $accomplishment->remarks = 'Completed half the component';
        $accomplishment->created_by = $this->user->id;
        $accomplishment->save();

        $this->assertDatabaseHas('accomplishment_registry', [
            'id' => $accomplishment->id,
            'component_id' => $this->component->id,
            'type' => 'ACTUAL',
            'quantity' => 25.5,
            'remarks' => 'Completed half the component',
        ]);

        // 2. Test relationships
        $this->assertEquals($this->component->id, $accomplishment->Component->id);
        $this->assertEquals($this->user->id, $accomplishment->CreatedBy->id);

        // 3. Test Component relationship
        $this->assertCount(1, $this->component->fresh()->Accomplishments);
        $this->assertEquals($accomplishment->id, $this->component->fresh()->Accomplishments->first()->id);

        // 4. Test Soft Delete
        $accomplishment->delete();
        $this->assertSoftDeleted('accomplishment_registry', [
            'id' => $accomplishment->id
        ]);
    }

    /** @test */
    public function it_can_render_the_accomplishment_create_page()
    {
        $response = $this->actingAs($this->user)->get('/accomplishment/component/' . $this->component->id . '/create');
        $response->assertStatus(200);
        $response->assertSee('Create Accomplishment');
        $response->assertSee('Test Component 123');
    }

    /** @test */
    public function it_can_create_an_accomplishment_via_api()
    {
        $response = $this->actingAs($this->user)->postJson('/api/accomplishment/create', [
            'component_id' => $this->component->id,
            'type'         => 'TARGET',
            'entry_date'   => '2026-08-12',
            'quantity'     => 20.4,
            'remarks'      => 'Target accomplishment for project phase'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'message' => ''
        ]);

        $this->assertDatabaseHas('accomplishment_registry', [
            'component_id' => $this->component->id,
            'type'         => 'TARGET',
            'quantity'     => 20.4,
            'remarks'      => 'Target accomplishment for project phase',
            'created_by'   => $this->user->id
        ]);
    }

    /** @test */
    public function it_fails_accomplishment_api_creation_due_to_validation()
    {
        $response = $this->actingAs($this->user)->postJson('/api/accomplishment/create', [
            'component_id' => $this->component->id,
            'type'         => 'INVALID_TYPE', // Invalid enum
            'entry_date'   => 'not-a-date',   // Invalid date
            'quantity'     => 'not-numeric'   // Invalid quantity
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => -2,
            'message' => 'Failed Validation'
        ]);
    }

    /** @test */
    public function it_can_render_the_new_accomplishment_add_page_with_component_details()
    {
        $response = $this->actingAs($this->user)->get('/accomplishment/component/' . $this->component->id . '/add');
        $response->assertStatus(200);
        
        // Assert view content
        $response->assertSee('Add Registry');
        $response->assertSee('Component Reference Details');
        $response->assertSee('Test Component 123');
        $response->assertSee('Test Project 123');
        $response->assertSee('Test Section 123');
        $response->assertSee('Test Contract Item 123');
        $response->assertSee('Total Quantity');
        $response->assertSee('50 Pcs');
    }

    /** @test */
    public function it_can_create_an_accomplishment_via_new_api_with_required_fields()
    {
        $this->grantAccessCode($this->user, 'accomplishment:all:create');

        $response = $this->actingAs($this->user)->postJson('/api/accomplishment/add', [
            'component_id' => $this->component->id,
            'entry_data'   => '2026-09-21',
            'quantity'     => 45.2,
            'remarks'      => 'Highly critical required remarks'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'message' => ''
        ]);

        $this->assertDatabaseHas('accomplishment_registry', [
            'component_id' => $this->component->id,
            'type'         => 'ACTUAL',
            'quantity'     => 45.2,
            'remarks'      => 'Highly critical required remarks',
            'created_by'   => $this->user->id
        ]);
    }

    /** @test */
    public function it_fails_new_api_creation_if_required_fields_are_missing()
    {
        $this->grantAccessCode($this->user, 'accomplishment:all:create');

        // 1. Missing remarks (which is now required in the new API)
        $response = $this->actingAs($this->user)->postJson('/api/accomplishment/add', [
            'component_id' => $this->component->id,
            'entry_data'   => '2026-09-21',
            'quantity'     => 45.2,
            'remarks'      => '' // empty
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => -2,
            'message' => 'Failed Validation'
        ]);

        // 2. Missing entry_data
        $response = $this->actingAs($this->user)->postJson('/api/accomplishment/add', [
            'component_id' => $this->component->id,
            'quantity'     => 45.2,
            'remarks'      => 'Some remarks'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => -2,
            'message' => 'Failed Validation'
        ]);
    }

    /** @test */
    public function it_denies_new_api_creation_if_user_lacks_access_code()
    {
        $response = $this->actingAs($this->user)->postJson('/api/accomplishment/add', [
            'component_id' => $this->component->id,
            'entry_data'   => '2026-09-21',
            'quantity'     => 45.2,
            'remarks'      => 'Some remarks'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 0,
            'message' => 'Access Denied'
        ]);
    }

    /** @test */
    public function it_can_render_the_accomplishment_record_display_page()
    {
        // Create an accomplishment record for this component
        $accomplishment = new \App\Models\Accomplishment();
        $accomplishment->component_id = $this->component->id;
        $accomplishment->type = 'ACTUAL';
        $accomplishment->entry_data = '2026-09-21';
        $accomplishment->quantity = 45.75;
        $accomplishment->remarks = 'Highly targeted remarks for specific record';
        $accomplishment->created_by = $this->user->id;
        $accomplishment->save();

        $response = $this->actingAs($this->user)->get('/accomplishment/record/' . $accomplishment->id);
        $response->assertStatus(200);
        
        // Assert reference details are shown
        $response->assertSee('Component Reference Details');
        $response->assertSee('Test Component 123');
        $response->assertSee('Test Project 123');
        $response->assertSee('Test Section 123');
        $response->assertSee('Test Contract Item 123');
        $response->assertSee('Total Quantity');
        $response->assertSee('50 Pcs');

        // Assert record details are shown
        $response->assertSee('Accomplishment Registry Record Details');
        $response->assertSee('Record ID');
        $response->assertSee('ACTUAL');
        $response->assertSee('45.75');
        $response->assertSee('Highly targeted remarks for specific record');
    }

    /** @test */
    public function it_fails_creation_if_quantity_exceeds_component_total_quantity()
    {
        $this->grantAccessCode($this->user, 'accomplishment:all:create');

        // 1. Test original API (_create)
        $response1 = $this->actingAs($this->user)->postJson('/api/accomplishment/create', [
            'component_id' => $this->component->id,
            'type'         => 'ACTUAL',
            'entry_date'   => '2026-09-21',
            'quantity'     => 105.0, // exceeds component total of 50
            'remarks'      => 'Exceeds limit'
        ]);

        $response1->assertStatus(200);
        $response1->assertJson([
            'status' => -2,
            'message' => 'Failed Validation'
        ]);

        // 2. Test new API (_add)
        $response2 = $this->actingAs($this->user)->postJson('/api/accomplishment/add', [
            'component_id' => $this->component->id,
            'entry_data'   => '2026-09-21',
            'quantity'     => 105.0, // exceeds component total of 50
            'remarks'      => 'Exceeds limit'
        ]);

        $response2->assertStatus(200);
        $response2->assertJson([
            'status' => -2,
            'message' => 'Failed Validation'
        ]);
    }

    protected function grantAccessCode($user, $codeString)
    {
        // 1. Create or find the AccessCode
        $accessCode = \App\Models\AccessCode::where('code', $codeString)->first();
        if (!$accessCode) {
            $accessCode = new \App\Models\AccessCode();
            $accessCode->code = $codeString;
            $accessCode->description = 'Test access code description';
            $accessCode->save();
        }

        // 2. Create a Role
        $role = new \App\Models\Role();
        $role->name = 'Test Role ' . uniqid();
        $role->description = 'Test Description';
        $role->save();

        // 3. Link Role to AccessCode
        $roleAccessCode = new \App\Models\RoleAccessCode();
        $roleAccessCode->role_id = $role->id;
        $roleAccessCode->access_code_id = $accessCode->id;
        $roleAccessCode->save();

        // 4. Link User to Role
        $userRole = new \App\Models\UserRole();
        $userRole->user_id = $user->id;
        $userRole->role_id = $role->id;
        $userRole->save();
    }
}
