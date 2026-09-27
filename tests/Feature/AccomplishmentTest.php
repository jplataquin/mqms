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
        $response->assertSee('Latest Quantity');
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
        // 1. Create a prior accomplishment record
        $priorAccomplishment = new \App\Models\Accomplishment();
        $priorAccomplishment->component_id = $this->component->id;
        $priorAccomplishment->type = 'ACTUAL';
        $priorAccomplishment->entry_data = '2026-09-20';
        $priorAccomplishment->quantity = 15.00; // 30% of 50
        $priorAccomplishment->remarks = 'First entry';
        $priorAccomplishment->created_by = $this->user->id;
        $priorAccomplishment->save();

        // 2. Create the current accomplishment record being displayed
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
        $response->assertSee('Latest Quantity');
        // It must show the PRIOR accomplishment because the CURRENT one is excluded!
        $response->assertSee('15.00 Pcs (30%) as of 2026-09-20.');

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

    /** @test */
    public function it_can_render_the_accomplishment_studio_mode_page()
    {
        $response = $this->actingAs($this->user)->get('/accomplishment/project/' . $this->project->id . '/studio');
        $response->assertStatus(200);
        $response->assertSee('Accomplishment Studio');
        $response->assertSee($this->project->name);
    }

    /** @test */
    public function it_renders_gantt_chart_with_placeholder_pill_support_and_alignment()
    {
        $response = $this->actingAs($this->user)->get('/accomplishment/project/' . $this->project->id . '/studio');
        $response->assertStatus(200);
        $response->assertSee('.pill-placeholder', false);
        $response->assertSee('pill-placeholder-target', false);
        $response->assertSee('pill-placeholder-actual', false);
        $response->assertSee('pill-target', false);
        $response->assertSee('pill-actual', false);
        $response->assertSee('zoomOut', false);
        $response->assertSee('Back to Months', false);
        $response->assertSee('day-header', false);
        $response->assertSee('day-cell', false);
    }

    /** @test */
    public function it_can_activate_target_tab_via_query_param()
    {
        $response = $this->actingAs($this->user)->get('/accomplishment/component/' . $this->component->id . '?type=TARGET');
        $response->assertStatus(200);
        $response->assertSee('btn-success');
    }

    /** @test */
    public function it_can_fetch_studio_mode_data_via_api()
    {
        // Add an accomplishment record to verify it's loaded in the hierarchy
        $acc = new \App\Models\Accomplishment();
        $acc->component_id = $this->component->id;
        $acc->type = 'ACTUAL';
        $acc->entry_data = '2026-09-25';
        $acc->quantity = 15.0;
        $acc->remarks = 'Studio test entry';
        $acc->created_by = $this->user->id;
        $acc->save();

        $response = $this->actingAs($this->user)->getJson('/api/accomplishment/project/' . $this->project->id . '/studio-data');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'data' => [
                'id' => $this->project->id,
                'name' => $this->project->name,
            ]
        ]);

        $responseData = $response->json('data');
        $this->assertNotEmpty($responseData['sections']);
        $this->assertEquals($this->section->id, $responseData['sections'][0]['id']);
        $this->assertNotEmpty($responseData['sections'][0]['contract_items']);
        $this->assertEquals($this->contractItem->id, $responseData['sections'][0]['contract_items'][0]['id']);
        $this->assertNotEmpty($responseData['sections'][0]['contract_items'][0]['components']);
        $this->assertEquals($this->component->id, $responseData['sections'][0]['contract_items'][0]['components'][0]['id']);
        $this->assertNotEmpty($responseData['sections'][0]['contract_items'][0]['components'][0]['accomplishments']);
        $this->assertEquals(15.0, $responseData['sections'][0]['contract_items'][0]['components'][0]['accomplishments'][0]['quantity']);
    }

    /** @test */
    public function it_can_delete_an_accomplishment_via_api()
    {
        $acc = new \App\Models\Accomplishment();
        $acc->component_id = $this->component->id;
        $acc->type = 'ACTUAL';
        $acc->entry_data = '2026-09-25';
        $acc->quantity = 15.0;
        $acc->remarks = 'Entry to be deleted';
        $acc->created_by = $this->user->id;
        $acc->save();

        $response = $this->actingAs($this->user)->postJson('/api/accomplishment/delete', [
            'id' => $acc->id
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'message' => 'Successfully deleted accomplishment.'
        ]);

        $this->assertSoftDeleted('accomplishment_registry', [
            'id' => $acc->id,
            'deleted_by' => $this->user->id
        ]);
    }

    /** @test */
    public function it_returns_error_when_deleting_non_existent_accomplishment()
    {
        $response = $this->actingAs($this->user)->postJson('/api/accomplishment/delete', [
            'id' => 999999
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 0,
            'message' => 'Record not found'
        ]);
    }

    /** @test */
    public function it_renders_delete_pill_button_and_confirmation_modal_in_studio_mode()
    {
        $response = $this->actingAs($this->user)->get('/accomplishment/project/' . $this->project->id . '/studio');
        $response->assertStatus(200);
        $response->assertSee('delete-pill-btn', false);
        $response->assertSee('deleteAccomplishment', false);
        $response->assertSee('viewAccomplishment', false);
        $response->assertSee('primary_modal', false);
        $response->assertSee('current-month-col', false);
        $response->assertSee('current-day-col', false);
        $response->assertSee('btnOverallProgress', false);
        $response->assertSee('showOverallProgress', false);
        $response->assertSee('showComponentProgress', false);
    }

    /** @test */
    public function it_can_fetch_accomplishments_via_third_party_api()
    {
        // 1. Create API Credential
        $credential = new \App\Models\ApiCredential();
        $credential->name = 'Test Third Party';
        $credential->api_key = 'test_api_key_' . uniqid();
        $credential->secret_key = 'test_secret_key_' . uniqid();
        $credential->created_by = $this->user->id;
        $credential->save();

        // 2. Create Accomplishment
        $acc = new \App\Models\Accomplishment();
        $acc->component_id = $this->component->id;
        $acc->type = 'ACTUAL';
        $acc->entry_data = '2026-09-27';
        $acc->quantity = 42.5;
        $acc->remarks = 'API test entry';
        $acc->created_by = $this->user->id;
        $acc->save();

        // 3. Prepare HMAC Request
        $method = 'GET';
        $path = 'api/call/accomplishments';
        $timestamp = time();
        $body = '';
        $payload = $method . $path . $timestamp . $body;
        $signature = hash_hmac('sha256', $payload, $credential->secret_key);

        $headers = [
            'X-API-KEY' => $credential->api_key,
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
            'Accept' => 'application/json'
        ];

        // 4. Send request
        $response = $this->withHeaders($headers)->get('/' . $path . '?component_id=' . $this->component->id);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'message' => 'Success'
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals(42.5, $data[0]['quantity']);
        $this->assertEquals('ACTUAL', $data[0]['type']);
        $this->assertEquals($this->component->id, $data[0]['component_id']);
    }

    /** @test */
    public function it_renders_print_report_button_on_project_sections_page()
    {
        $response = $this->actingAs($this->user)->get('/accomplishment/project/' . $this->project->id);
        $response->assertStatus(200);
        $response->assertSee('/accomplishment/project/' . $this->project->id . '/print');
        $response->assertSee('Accomplishment Form');
    }

    /** @test */
    public function it_renders_printable_project_report()
    {
        // Create an empty section with no components
        $emptySection = new \App\Models\Section();
        $emptySection->project_id = $this->project->id;
        $emptySection->name = 'Empty Ghost Section';
        $emptySection->created_by = $this->user->id;
        $emptySection->save();

        // Create an empty contract item with no components
        $emptyCi = new \App\Models\ContractItem();
        $emptyCi->section_id = $this->section->id;
        $emptyCi->item_code = 'GHOST-01';
        $emptyCi->description = 'Empty Ghost Contract Item';
        $emptyCi->item_type = 'MAT';
        $emptyCi->contract_quantity = 50;
        $emptyCi->unit_id = $this->unit->id;
        $emptyCi->contract_unit_price = 100;
        $emptyCi->created_by = $this->user->id;
        $emptyCi->save();

        $response = $this->actingAs($this->user)->get('/accomplishment/project/' . $this->project->id . '/print');
        $response->assertStatus(200);
        $response->assertSee('Project Accomplishment Form');
        $response->assertSee($this->project->name);
        $response->assertSee('SECTION: ' . $this->section->name);
        $response->assertSee($this->contractItem->item_code);
        $response->assertSee($this->contractItem->description);
        $response->assertSee($this->component->name);
        $response->assertSee('window.print()', false);

        // Verify rows without any components are hidden
        $response->assertDontSee('SECTION: Empty Ghost Section');
        $response->assertDontSee('Empty Ghost Contract Item');
    }

    /** @test */
    public function it_applies_page_break_after_every_30_rows_in_printable_form()
    {
        // Seed additional components so total rows exceed 30
        // (1 section + 1 CI + existing 1 component = 3 rows, so we add 30 more components)
        for ($i = 1; $i <= 30; $i++) {
            $comp = new \App\Models\Component();
            $comp->name = 'Bulk Component ' . $i;
            $comp->contract_item_id = $this->contractItem->id;
            $comp->quantity = 10;
            $comp->unit_id = $this->unit->id;
            $comp->use_count = 1;
            $comp->status = 'APRV';
            $comp->section_id = $this->section->id;
            $comp->created_by = $this->user->id;
            $comp->save();
        }

        $response = $this->actingAs($this->user)->get('/accomplishment/project/' . $this->project->id . '/print');
        $response->assertStatus(200);
        $response->assertSee('page-break', false);
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
