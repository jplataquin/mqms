<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\Unit;
use App\Models\Accomplishment;
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

        // Create project, section, and contract item
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
    public function it_can_render_the_blank_accomplishment_contract_item_page()
    {
        $response = $this->actingAs($this->user)->get('/accomplishment/contract_item/' . $this->contractItem->id);
        $response->assertStatus(200);
        $response->assertSee('Test Contract Item 123');
        $response->assertSee('Add Registry Entry');
        $response->assertSee('Total Quantity');
        $response->assertSee('100.00 Pcs');
        $response->assertSee('No accomplishment records found');
        $response->assertSee('id="list"', false);
    }

    /** @test */
    public function it_can_render_the_accomplishment_contract_item_page_with_records()
    {
        // Create an accomplishment record for this contract item
        $accomplishment = new Accomplishment();
        $accomplishment->contract_item_id = $this->contractItem->id;
        $accomplishment->type = 'ACTUAL';
        $accomplishment->entry_data = '2026-09-21';
        $accomplishment->quantity = 35.50;
        $accomplishment->remarks = 'Completed most of it';
        $accomplishment->created_by = $this->user->id;
        $accomplishment->save();

        // 1. Verify page renders successfully
        $response = $this->actingAs($this->user)->get('/accomplishment/contract_item/' . $this->contractItem->id);
        $response->assertStatus(200);
        $response->assertSee('Test Contract Item 123');
        $response->assertSee('Latest Quantity');
        $response->assertSee('35.50 Pcs (36%) as of 2026-09-21.');
        $response->assertSee('PHP 1,500.00');
        $response->assertSee('PHP 150,000.00');
        $response->assertSee('PHP 53,250.00');

        // 2. Verify API returns accomplishment list correctly
        $apiResponse = $this->actingAs($this->user)->get('/api/accomplishment/record/list?contract_item_id=' . $this->contractItem->id);
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonFragment([
            'contract_item_id' => $this->contractItem->id,
            'type' => 'ACTUAL',
            'quantity' => 35.50,
            'accomplished_amount' => 53250.0,
            'remarks' => 'Completed most of it',
            'creator_name' => $this->user->name
        ]);
    }

    /** @test */
    public function it_can_fetch_projects_via_api()
    {
        $response = $this->actingAs($this->user)->getJson('/api/accomplishment/project/list');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $this->project->id,
            'name' => 'Test Project 123'
        ]);
    }

    /** @test */
    public function it_can_fetch_sections_via_api()
    {
        $response = $this->actingAs($this->user)->getJson('/api/accomplishment/section/list?project_id=' . $this->project->id);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $this->section->id,
            'name' => 'Test Section 123'
        ]);
    }

    /** @test */
    public function it_can_fetch_contract_items_via_api()
    {
        $response = $this->actingAs($this->user)->getJson('/api/accomplishment/contract_item/list?section_id=' . $this->section->id);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $this->contractItem->id,
            'description' => 'Test Contract Item 123'
        ]);
    }

    /** @test */
    public function it_can_create_and_manage_accomplishment_records()
    {
        // 1. Create Accomplishment record
        $accomplishment = new Accomplishment();
        $accomplishment->contract_item_id = $this->contractItem->id;
        $accomplishment->type = 'ACTUAL';
        $accomplishment->entry_data = '2026-08-12';
        $accomplishment->quantity = 25.5;
        $accomplishment->remarks = 'Completed quarter of contract item';
        $accomplishment->created_by = $this->user->id;
        $accomplishment->save();

        $this->assertDatabaseHas('accomplishment_registry', [
            'id' => $accomplishment->id,
            'contract_item_id' => $this->contractItem->id,
            'type' => 'ACTUAL',
            'quantity' => 25.5,
            'remarks' => 'Completed quarter of contract item',
        ]);

        // 2. Test relationships
        $this->assertEquals($this->contractItem->id, $accomplishment->ContractItem->id);
        $this->assertEquals($this->user->id, $accomplishment->CreatedBy->id);

        // 3. Test ContractItem relationship
        $this->assertCount(1, $this->contractItem->fresh()->Accomplishments);
        $this->assertEquals($accomplishment->id, $this->contractItem->fresh()->Accomplishments->first()->id);

        // 4. Test Soft Delete
        $accomplishment->delete();
        $this->assertSoftDeleted('accomplishment_registry', [
            'id' => $accomplishment->id
        ]);
    }

    /** @test */
    public function it_can_render_the_accomplishment_create_page()
    {
        $response = $this->actingAs($this->user)->get('/accomplishment/contract_item/' . $this->contractItem->id . '/create');
        $response->assertStatus(200);
        $response->assertSee('Create Accomplishment');
        $response->assertSee('Test Contract Item 123');
    }

    /** @test */
    public function it_can_create_an_accomplishment_via_api()
    {
        $response = $this->actingAs($this->user)->postJson('/api/accomplishment/create', [
            'contract_item_id' => $this->contractItem->id,
            'type'             => 'TARGET',
            'entry_date'       => '2026-08-12',
            'quantity'         => 20.4,
            'remarks'          => 'Target accomplishment for contract item phase'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'message' => ''
        ]);

        $this->assertDatabaseHas('accomplishment_registry', [
            'contract_item_id' => $this->contractItem->id,
            'type'             => 'TARGET',
            'quantity'         => 20.4,
            'remarks'          => 'Target accomplishment for contract item phase',
            'created_by'       => $this->user->id
        ]);
    }

    /** @test */
    public function it_fails_accomplishment_api_creation_due_to_validation()
    {
        $response = $this->actingAs($this->user)->postJson('/api/accomplishment/create', [
            'contract_item_id' => $this->contractItem->id,
            'type'             => 'INVALID_TYPE', // Invalid enum
            'entry_date'       => 'not-a-date',   // Invalid date
            'quantity'         => 'not-numeric'   // Invalid quantity
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => -2,
            'message' => 'Failed Validation'
        ]);
    }

    /** @test */
    public function it_can_create_an_accomplishment_via_new_api_with_required_fields()
    {
        $this->grantAccessCode($this->user, 'accomplishment:all:create');

        $target = new Accomplishment();
        $target->contract_item_id = $this->contractItem->id;
        $target->type = 'TARGET';
        $target->entry_data = '2026-09-30';
        $target->quantity = 100.0;
        $target->remarks = 'Target for contract item';
        $target->created_by = $this->user->id;
        $target->save();

        $response = $this->actingAs($this->user)->postJson('/api/accomplishment/add', [
            'contract_item_id' => $this->contractItem->id,
            'entry_data'       => '2026-09-21',
            'quantity'         => 45.2,
            'remarks'          => 'Highly critical required remarks'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'message' => ''
        ]);

        $this->assertDatabaseHas('accomplishment_registry', [
            'contract_item_id' => $this->contractItem->id,
            'type'             => 'ACTUAL',
            'quantity'         => 45.2,
            'remarks'          => 'Highly critical required remarks',
            'created_by'       => $this->user->id
        ]);
    }

    /** @test */
    public function it_fails_new_api_creation_if_required_fields_are_missing()
    {
        $this->grantAccessCode($this->user, 'accomplishment:all:create');

        // 1. Missing remarks (which is required in the add API)
        $response = $this->actingAs($this->user)->postJson('/api/accomplishment/add', [
            'contract_item_id' => $this->contractItem->id,
            'entry_data'       => '2026-09-21',
            'quantity'         => 45.2,
            'remarks'          => '' // empty
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => -2,
            'message' => 'Failed Validation'
        ]);

        // 2. Missing entry_data
        $response = $this->actingAs($this->user)->postJson('/api/accomplishment/add', [
            'contract_item_id' => $this->contractItem->id,
            'quantity'         => 45.2,
            'remarks'          => 'Some remarks'
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
            'contract_item_id' => $this->contractItem->id,
            'entry_data'       => '2026-09-21',
            'quantity'         => 45.2,
            'remarks'          => 'Some remarks'
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
        $priorAccomplishment = new Accomplishment();
        $priorAccomplishment->contract_item_id = $this->contractItem->id;
        $priorAccomplishment->type = 'ACTUAL';
        $priorAccomplishment->entry_data = '2026-09-20';
        $priorAccomplishment->quantity = 15.00; // 15% of 100
        $priorAccomplishment->remarks = 'First entry';
        $priorAccomplishment->created_by = $this->user->id;
        $priorAccomplishment->save();

        // 2. Create the current accomplishment record being displayed
        $accomplishment = new Accomplishment();
        $accomplishment->contract_item_id = $this->contractItem->id;
        $accomplishment->type = 'ACTUAL';
        $accomplishment->entry_data = '2026-09-21';
        $accomplishment->quantity = 45.75;
        $accomplishment->remarks = 'Highly targeted remarks for specific record';
        $accomplishment->created_by = $this->user->id;
        $accomplishment->save();

        $response = $this->actingAs($this->user)->get('/accomplishment/record/' . $accomplishment->id);
        $response->assertStatus(200);
        
        // Assert reference details are shown
        $response->assertSee('Contract Item Reference Details');
        $response->assertSee('Test Project 123');
        $response->assertSee('Test Section 123');
        $response->assertSee('Test Contract Item 123');
        $response->assertSee('Total Quantity');
        $response->assertSee('100.00 Pcs');
        $response->assertSee('Latest Quantity');
        $response->assertSee('15.00 Pcs (15%) as of 2026-09-20.');

        // Assert record details are shown
        $response->assertSee('Accomplishment Registry Record Details');
        $response->assertSee('Record ID');
        $response->assertSee('ACTUAL');
        $response->assertSee('45.75');
        $response->assertSee('PHP 1,500.00');
        $response->assertSee('PHP 150,000.00');
        $response->assertSee('PHP 22,500.00');
        $response->assertSee('Accomplished Amount (PHP)');
        $response->assertSee('PHP 68,625.00');
        $response->assertSee('Highly targeted remarks for specific record');
    }

    /** @test */
    public function it_calculates_accomplished_amount_in_php_on_contract_item_and_accomplishment_models()
    {
        $accomplishment = new Accomplishment();
        $accomplishment->contract_item_id = $this->contractItem->id;
        $accomplishment->type = 'ACTUAL';
        $accomplishment->entry_data = '2026-09-21';
        $accomplishment->quantity = 40.0;
        $accomplishment->remarks = 'Calculated amount test';
        $accomplishment->created_by = $this->user->id;
        $accomplishment->save();

        // 40.0 * 1500.00 = 60,000.00 PHP
        $this->assertEquals(60000.0, $accomplishment->accomplished_amount);
        $this->assertEquals(60000.0, $this->contractItem->fresh()->accomplished_amount);
    }

    /** @test */
    public function it_fails_creation_if_quantity_exceeds_contract_item_total_quantity()
    {
        $this->grantAccessCode($this->user, 'accomplishment:all:create');

        // 1. Test original API (_create)
        $response1 = $this->actingAs($this->user)->postJson('/api/accomplishment/create', [
            'contract_item_id' => $this->contractItem->id,
            'type'             => 'ACTUAL',
            'entry_date'       => '2026-09-21',
            'quantity'         => 105.0, // exceeds contract quantity of 100
            'remarks'          => 'Exceeds limit'
        ]);

        $response1->assertStatus(200);
        $response1->assertJson([
            'status' => -2,
            'message' => 'Failed Validation'
        ]);

        // 2. Test new API (_add)
        $response2 = $this->actingAs($this->user)->postJson('/api/accomplishment/add', [
            'contract_item_id' => $this->contractItem->id,
            'entry_data'       => '2026-09-21',
            'quantity'         => 105.0, // exceeds contract quantity of 100
            'remarks'          => 'Exceeds limit'
        ]);

        $response2->assertStatus(200);
        $response2->assertJson([
            'status' => -2,
            'message' => 'Failed Validation'
        ]);
    }

    /** @test */
    public function it_fails_creation_if_quantity_is_lower_than_or_equal_to_last_previous_accomplishment_quantity()
    {
        $this->grantAccessCode($this->user, 'accomplishment:all:create');

        // Create target on or after date to satisfy milestone validation
        $target = new Accomplishment();
        $target->contract_item_id = $this->contractItem->id;
        $target->type = 'TARGET';
        $target->entry_data = '2026-09-30';
        $target->quantity = 100.0;
        $target->remarks = 'Target for contract item';
        $target->created_by = $this->user->id;
        $target->save();

        // Create an initial accomplishment record (quantity = 25.0)
        $prior = new Accomplishment();
        $prior->contract_item_id = $this->contractItem->id;
        $prior->type = 'ACTUAL';
        $prior->entry_data = '2026-09-20';
        $prior->quantity = 25.0;
        $prior->remarks = 'Prior accomplishment';
        $prior->created_by = $this->user->id;
        $prior->save();

        // 1. Test _create API with strictly lower quantity (20.0 <= 25.0)
        $response1 = $this->actingAs($this->user)->postJson('/api/accomplishment/create', [
            'contract_item_id' => $this->contractItem->id,
            'type'             => 'ACTUAL',
            'entry_date'       => '2026-09-21',
            'quantity'         => 20.0,
            'remarks'          => 'Lower quantity'
        ]);

        $response1->assertStatus(200);
        $response1->assertJson([
            'status' => -2,
            'message' => 'Failed Validation',
            'data' => [
                'quantity' => ['The quantity must not be lower than or equal to the last previous accomplishment record quantity.']
            ]
        ]);

        // 2. Test _create API with equal quantity (25.0 <= 25.0)
        $response2 = $this->actingAs($this->user)->postJson('/api/accomplishment/create', [
            'contract_item_id' => $this->contractItem->id,
            'type'             => 'ACTUAL',
            'entry_date'       => '2026-09-21',
            'quantity'         => 25.0,
            'remarks'          => 'Equal quantity'
        ]);

        $response2->assertStatus(200);
        $response2->assertJson([
            'status' => -2,
            'message' => 'Failed Validation',
            'data' => [
                'quantity' => ['The quantity must not be lower than or equal to the last previous accomplishment record quantity.']
            ]
        ]);

        // 3. Test _add API with strictly lower quantity (20.0 <= 25.0)
        $response3 = $this->actingAs($this->user)->postJson('/api/accomplishment/add', [
            'contract_item_id' => $this->contractItem->id,
            'entry_data'       => '2026-09-21',
            'quantity'         => 20.0,
            'remarks'          => 'Lower quantity',
            'type'             => 'ACTUAL'
        ]);

        $response3->assertStatus(200);
        $response3->assertJson([
            'status' => -2,
            'message' => 'Failed Validation',
            'data' => [
                'quantity' => ['The quantity must not be lower than or equal to the last previous accomplishment record quantity.']
            ]
        ]);

        // 4. Test _add API with equal quantity (25.0 <= 25.0)
        $response4 = $this->actingAs($this->user)->postJson('/api/accomplishment/add', [
            'contract_item_id' => $this->contractItem->id,
            'entry_data'       => '2026-09-21',
            'quantity'         => 25.0,
            'remarks'          => 'Equal quantity',
            'type'             => 'ACTUAL'
        ]);

        $response4->assertStatus(200);
        $response4->assertJson([
            'status' => -2,
            'message' => 'Failed Validation',
            'data' => [
                'quantity' => ['The quantity must not be lower than or equal to the last previous accomplishment record quantity.']
            ]
        ]);
    }

    /** @test */
    public function it_allows_creation_if_quantity_is_greater_than_last_previous_accomplishment_quantity()
    {
        $this->grantAccessCode($this->user, 'accomplishment:all:create');

        // Create target on or after date to satisfy milestone validation
        $target = new Accomplishment();
        $target->contract_item_id = $this->contractItem->id;
        $target->type = 'TARGET';
        $target->entry_data = '2026-09-30';
        $target->quantity = 100.0;
        $target->remarks = 'Target for contract item';
        $target->created_by = $this->user->id;
        $target->save();

        // Create an initial accomplishment record (quantity = 25.0)
        $prior = new Accomplishment();
        $prior->contract_item_id = $this->contractItem->id;
        $prior->type = 'ACTUAL';
        $prior->entry_data = '2026-09-20';
        $prior->quantity = 25.0;
        $prior->remarks = 'Prior accomplishment';
        $prior->created_by = $this->user->id;
        $prior->save();

        // 1. Test _create API with greater quantity (30.0 > 25.0)
        $response1 = $this->actingAs($this->user)->postJson('/api/accomplishment/create', [
            'contract_item_id' => $this->contractItem->id,
            'type'             => 'ACTUAL',
            'entry_date'       => '2026-09-21',
            'quantity'         => 30.0,
            'remarks'          => 'Higher quantity via create'
        ]);

        $response1->assertStatus(200);
        $response1->assertJson([
            'status' => 1,
            'message' => ''
        ]);

        $this->assertDatabaseHas('accomplishment_registry', [
            'contract_item_id' => $this->contractItem->id,
            'type'             => 'ACTUAL',
            'quantity'         => 30.0,
        ]);

        // 2. Test _add API with even higher quantity (35.0 > 30.0)
        $response2 = $this->actingAs($this->user)->postJson('/api/accomplishment/add', [
            'contract_item_id' => $this->contractItem->id,
            'entry_data'       => '2026-09-22',
            'quantity'         => 35.0,
            'remarks'          => 'Higher quantity via add',
            'type'             => 'ACTUAL'
        ]);

        $response2->assertStatus(200);
        $response2->assertJson([
            'status' => 1,
            'message' => ''
        ]);

        $this->assertDatabaseHas('accomplishment_registry', [
            'contract_item_id' => $this->contractItem->id,
            'type'             => 'ACTUAL',
            'quantity'         => 35.0,
        ]);
    }

    /** @test */
    public function it_scopes_previous_accomplishment_validation_by_type()
    {
        $this->grantAccessCode($this->user, 'accomplishment:all:create');

        // Create an ACTUAL accomplishment record (quantity = 30.0)
        $priorActual = new Accomplishment();
        $priorActual->contract_item_id = $this->contractItem->id;
        $priorActual->type = 'ACTUAL';
        $priorActual->entry_data = '2026-09-20';
        $priorActual->quantity = 30.0;
        $priorActual->remarks = 'Actual accomplishment';
        $priorActual->created_by = $this->user->id;
        $priorActual->save();

        // 1. Create a TARGET accomplishment with quantity 10.0 (lower than ACTUAL 30.0, but no prior TARGET exists)
        $response1 = $this->actingAs($this->user)->postJson('/api/accomplishment/create', [
            'contract_item_id' => $this->contractItem->id,
            'type'             => 'TARGET',
            'entry_date'       => '2026-09-21',
            'quantity'         => 10.0,
            'remarks'          => 'First target'
        ]);

        $response1->assertStatus(200);
        $response1->assertJson([
            'status' => 1,
            'message' => ''
        ]);

        // 2. Creating another TARGET with quantity 8.0 (lower than TARGET 10.0) should fail
        $response2 = $this->actingAs($this->user)->postJson('/api/accomplishment/create', [
            'contract_item_id' => $this->contractItem->id,
            'type'             => 'TARGET',
            'entry_date'       => '2026-09-22',
            'quantity'         => 8.0,
            'remarks'          => 'Lower target'
        ]);

        $response2->assertStatus(200);
        $response2->assertJson([
            'status' => -2,
            'message' => 'Failed Validation',
            'data' => [
                'quantity' => ['The quantity must not be lower than or equal to the last previous accomplishment record quantity.']
            ]
        ]);

        // 3. Creating another TARGET with quantity 15.0 (greater than TARGET 10.0) should succeed
        $response3 = $this->actingAs($this->user)->postJson('/api/accomplishment/add', [
            'contract_item_id' => $this->contractItem->id,
            'type'             => 'TARGET',
            'entry_date'       => '2026-09-23',
            'quantity'         => 15.0,
            'remarks'          => 'Higher target'
        ]);

        $response3->assertStatus(200);
        $response3->assertJson([
            'status' => 1,
            'message' => ''
        ]);
    }

    /** @test */
    public function it_can_render_the_accomplishment_studio_mode_page()
    {
        $this->assertEquals(150000.0, $this->project->getTotalContractAmount());

        $response = $this->actingAs($this->user)->get('/accomplishment/project/' . $this->project->id . '/studio');
        $response->assertStatus(200);
        $response->assertSee('Accomplishment Studio');
        $response->assertSee($this->project->name);
        $response->assertSee($this->project->name . ' — P150,000.00');
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
        $response = $this->actingAs($this->user)->get('/accomplishment/contract_item/' . $this->contractItem->id . '?type=TARGET');
        $response->assertStatus(200);
        $response->assertSee('btn-success');
    }

    /** @test */
    public function it_can_fetch_studio_mode_data_via_api()
    {
        // Add an accomplishment record to verify it's loaded in the hierarchy
        $acc = new Accomplishment();
        $acc->contract_item_id = $this->contractItem->id;
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
        $this->assertNotEmpty($responseData['sections'][0]['contract_items'][0]['accomplishments']);
        $this->assertEquals(15.0, $responseData['sections'][0]['contract_items'][0]['accomplishments'][0]['quantity']);
    }

    /** @test */
    public function it_can_delete_an_accomplishment_via_api()
    {
        $acc = new Accomplishment();
        $acc->contract_item_id = $this->contractItem->id;
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
    public function it_can_delete_multiple_accomplishments_via_api()
    {
        $acc1 = new Accomplishment();
        $acc1->contract_item_id = $this->contractItem->id;
        $acc1->type = 'TARGET';
        $acc1->entry_data = '2026-09-01';
        $acc1->quantity = 10.0;
        $acc1->created_by = $this->user->id;
        $acc1->save();

        $acc2 = new Accomplishment();
        $acc2->contract_item_id = $this->contractItem->id;
        $acc2->type = 'ACTUAL';
        $acc2->entry_data = '2026-09-15';
        $acc2->quantity = 5.0;
        $acc2->created_by = $this->user->id;
        $acc2->save();

        $response = $this->actingAs($this->user)->postJson('/api/accomplishment/delete-multiple', [
            'ids' => [$acc1->id, $acc2->id]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'message' => 'Successfully deleted 2 accomplishment(s).'
        ]);

        $this->assertSoftDeleted('accomplishment_registry', [
            'id' => $acc1->id,
            'deleted_by' => $this->user->id
        ]);
        $this->assertSoftDeleted('accomplishment_registry', [
            'id' => $acc2->id,
            'deleted_by' => $this->user->id
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
        $response->assertSee('dragSelectionBox', false);
        $response->assertSee('bulkActionsToolbar', false);
        $response->assertSee('deleteSelectedAccomplishments', false);
        $response->assertSee('handlePillClick', false);
        $response->assertSee('col-resizer', false);
        $response->assertSee('--col-wbs-width', false);
        $response->assertSee('--col-scope-width', false);
        $response->assertSee('--col-acc-width', false);
    }

    /** @test */
    public function it_renders_drawer_velocity_metrics_with_est_completion_before_required_speed_in_studio_mode()
    {
        $response = $this->actingAs($this->user)->get('/accomplishment/project/' . $this->project->id . '/studio');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('Running Speed', $content);
        $this->assertStringContainsString('Est. Completion', $content);
        $this->assertStringContainsString('Required Speed', $content);
        $this->assertStringContainsString('Next Target', $content);

        $runningPos = strpos($content, 'Running Speed');
        $estCompPos = strpos($content, 'Est. Completion');
        $reqSpeedPos = strpos($content, 'Required Speed');
        $nextTargetPos = strpos($content, 'Next Target');

        $this->assertTrue($runningPos < $estCompPos);
        $this->assertTrue($estCompPos < $reqSpeedPos);
        $this->assertTrue($reqSpeedPos < $nextTargetPos);
    }

    /** @test */
    public function it_renders_overall_progress_contract_items_without_vertical_overflow_scroll()
    {
        $response = $this->actingAs($this->user)->get('/accomplishment/project/' . $this->project->id . '/studio');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('Weighted Accomplishment Breakdown', $content);
        // Ensure table-responsive container has no vertical overflow or max-height restrictions
        $this->assertStringNotContainsString('max-height: 220px', $content);
        $this->assertStringNotContainsString('overflow-y: auto', $content);
        $this->assertStringContainsString('<div class="table-responsive">', $content);
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
        $acc = new Accomplishment();
        $acc->contract_item_id = $this->contractItem->id;
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
        $response = $this->withHeaders($headers)->get('/' . $path . '?contract_item_id=' . $this->contractItem->id);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'message' => 'Success'
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals(42.5, $data[0]['quantity']);
        $this->assertEquals('ACTUAL', $data[0]['type']);
        $this->assertEquals($this->contractItem->id, $data[0]['contract_item_id']);
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
        // Create an empty section with no contract items
        $emptySection = new Section();
        $emptySection->project_id = $this->project->id;
        $emptySection->name = 'Empty Ghost Section';
        $emptySection->created_by = $this->user->id;
        $emptySection->save();

        $response = $this->actingAs($this->user)->get('/accomplishment/project/' . $this->project->id . '/print');
        $response->assertStatus(200);
        $response->assertSee('Project Accomplishment Form');
        $response->assertSee($this->project->name);
        $response->assertSee('SECTION: ' . $this->section->name);
        $response->assertSee($this->contractItem->item_code);
        $response->assertSee($this->contractItem->description);
        $response->assertSee('window.print()', false);

        // Verify sections without any contract items are hidden
        $response->assertDontSee('SECTION: Empty Ghost Section');
    }

    /** @test */
    public function it_applies_page_break_after_every_30_rows_in_printable_form()
    {
        // Seed additional contract items so total rows exceed 30
        for ($i = 1; $i <= 30; $i++) {
            $ci = new ContractItem();
            $ci->section_id = $this->section->id;
            $ci->item_code = 'BULK-' . $i;
            $ci->description = 'Bulk Contract Item ' . $i;
            $ci->item_type = 'MAT';
            $ci->contract_quantity = 10;
            $ci->unit_id = $this->unit->id;
            $ci->contract_unit_price = 100;
            $ci->created_by = $this->user->id;
            $ci->save();
        }

        $response = $this->actingAs($this->user)->get('/accomplishment/project/' . $this->project->id . '/print');
        $response->assertStatus(200);
        $response->assertSee('page-break', false);
    }

    /** @test */
    public function it_excludes_contract_items_with_exclude_from_monitoring_from_studio_and_print_form()
    {
        $excludedCi = new ContractItem();
        $excludedCi->section_id = $this->section->id;
        $excludedCi->item_code = 'EXCL-01';
        $excludedCi->description = 'Excluded Special Contract Item';
        $excludedCi->item_type = 'MAT';
        $excludedCi->contract_quantity = 100;
        $excludedCi->unit_id = $this->unit->id;
        $excludedCi->contract_unit_price = 100;
        $excludedCi->exclude_from_monitoring = 1;
        $excludedCi->created_by = $this->user->id;
        $excludedCi->save();

        // Check studio data API returns contract items with exclude_from_monitoring flag
        $studioResponse = $this->actingAs($this->user)->getJson('/api/accomplishment/project/' . $this->project->id . '/studio-data');
        $studioResponse->assertStatus(200);
        $items = $studioResponse->json('data.sections.0.contract_items');
        $excludedItem = collect($items)->firstWhere('item_code', 'EXCL-01');
        $this->assertNotNull($excludedItem);
        $this->assertEquals(1, $excludedItem['exclude_from_monitoring']);

        // Check printable form
        $printResponse = $this->actingAs($this->user)->get('/accomplishment/project/' . $this->project->id . '/print');
        $printResponse->assertStatus(200);
        $printResponse->assertSee($this->contractItem->description);
        $printResponse->assertDontSee('Excluded Special Contract Item');

        // Verify studio top header contract amount includes only active and non-excluded items
        $this->assertEquals(160000.0, $this->project->getTotalContractAmount(false));
        $this->assertEquals(150000.0, $this->project->getTotalContractAmount(true));

        $studioView = $this->actingAs($this->user)->get('/accomplishment/project/' . $this->project->id . '/studio');
        $studioView->assertStatus(200);
        $studioView->assertSee($this->project->name . ' — P150,000.00');
        $studioView->assertDontSee($this->project->name . ' — P160,000.00');
    }

    /** @test */
    public function it_can_exclude_a_contract_item_via_api_and_context_menu_option()
    {
        $ciToExclude = new ContractItem();
        $ciToExclude->section_id = $this->section->id;
        $ciToExclude->item_code = 'EXCL-NOW';
        $ciToExclude->description = 'Contract Item to Exclude Now';
        $ciToExclude->item_type = 'MAT';
        $ciToExclude->contract_quantity = 75;
        $ciToExclude->unit_id = $this->unit->id;
        $ciToExclude->contract_unit_price = 100;
        $ciToExclude->created_by = $this->user->id;
        $ciToExclude->save();

        $this->assertEquals(0, $ciToExclude->exclude_from_monitoring);

        $response = $this->actingAs($this->user)->postJson('/api/accomplishment/contract_item/exclude', [
            'contract_item_id' => $ciToExclude->id
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'message' => 'Contract Item successfully excluded from accomplishment monitoring.'
        ]);

        $ciToExclude->refresh();
        $this->assertEquals(1, $ciToExclude->exclude_from_monitoring);

        // Verify context menu option in Studio Mode view
        $studioView = $this->actingAs($this->user)->get('/accomplishment/project/' . $this->project->id . '/studio');
        $studioView->assertStatus(200);
        $studioView->assertSee('excludeComponentFromMonitoring', false);
        $studioView->assertSee('Exclude from Monitoring', false);
        $studioView->assertSee('btnToggleExcluded', false);
        $studioView->assertSee('includeComponentInMonitoring', false);
        $studioView->assertSee('row-component-excluded', false);
        $studioView->assertSee('rgba(255, 255, 255, 0.08)', false);
        $studioView->assertSee('badge bg-white text-dark', false);
        // Excluded item amount (7,500) must not be in top header (should remain 150,000, not 157,500)
        $studioView->assertSee($this->project->name . ' — P150,000.00');
        $studioView->assertDontSee($this->project->name . ' — P157,500.00');
    }

    /** @test */
    public function it_can_include_an_excluded_contract_item_via_api()
    {
        $ciToInclude = new ContractItem();
        $ciToInclude->section_id = $this->section->id;
        $ciToInclude->item_code = 'REINC-01';
        $ciToInclude->description = 'Contract Item to Re-Include';
        $ciToInclude->item_type = 'MAT';
        $ciToInclude->contract_quantity = 75;
        $ciToInclude->unit_id = $this->unit->id;
        $ciToInclude->contract_unit_price = 100;
        $ciToInclude->exclude_from_monitoring = 1;
        $ciToInclude->created_by = $this->user->id;
        $ciToInclude->save();

        $this->assertEquals(1, $ciToInclude->exclude_from_monitoring);

        $response = $this->actingAs($this->user)->postJson('/api/accomplishment/contract_item/include', [
            'contract_item_id' => $ciToInclude->id
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 1,
            'message' => 'Contract Item successfully included in accomplishment monitoring.'
        ]);

        $ciToInclude->refresh();
        $this->assertEquals(0, $ciToInclude->exclude_from_monitoring);

        // After re-inclusion, studio top header must include the item (150,000 + 7,500 = 157,500)
        $studioView = $this->actingAs($this->user)->get('/accomplishment/project/' . $this->project->id . '/studio');
        $studioView->assertStatus(200);
        $studioView->assertSee($this->project->name . ' — P157,500.00');
    }

    /** @test */
    public function it_displays_weighted_percentage_for_contract_item_in_drawer()
    {
        // Add a second contract item to test weighting:
        // Item 1 ($this->contractItem): quantity = 100, unit price = 1500 => amount = 150,000
        // Item 2: quantity = 50, unit price = 1000 => amount = 50,000
        // Total project contract amount = 200,000
        // Item 1 weight = 150,000 / 200,000 = 75% (0.75)
        $ci2 = new ContractItem();
        $ci2->section_id = $this->section->id;
        $ci2->item_type = 'MAT';
        $ci2->item_code = 'C-102';
        $ci2->description = 'Second Item';
        $ci2->contract_quantity = 50;
        $ci2->unit_id = $this->unit->id;
        $ci2->contract_unit_price = 1000.00;
        $ci2->created_by = $this->user->id;
        $ci2->save();

        // Create latest actual accomplishment: 40 units out of 100 = 40% completion
        // Weighted actual = 40% * 0.75 = 30%
        $accActual = new Accomplishment();
        $accActual->contract_item_id = $this->contractItem->id;
        $accActual->type = 'ACTUAL';
        $accActual->entry_data = '2026-10-15';
        $accActual->quantity = 40.0;
        $accActual->remarks = 'Actual test entry';
        $accActual->created_by = $this->user->id;
        $accActual->save();

        // Create latest target accomplishment: 80 units out of 100 = 80% completion
        // Weighted target = 80% * 0.75 = 60%
        $accTarget = new Accomplishment();
        $accTarget->contract_item_id = $this->contractItem->id;
        $accTarget->type = 'TARGET';
        $accTarget->entry_data = \Carbon\Carbon::now()->addDays(10)->format('Y-m-d');
        $accTarget->quantity = 80.0;
        $accTarget->remarks = 'Target test entry';
        $accTarget->created_by = $this->user->id;
        $accTarget->save();

        // Target on future date (2026-10-20) - Should NOT be delayed under Option 1 (milestone not yet reached)
        $response = $this->actingAs($this->user)->get('/accomplishment/contract_item/' . $this->contractItem->id);
        $response->assertStatus(200);

        // Weighted percentage is now only calculated in overall progress, not in contract item drawer
        $response->assertDontSee('Item Weight');
        $response->assertDontSee('Latest Actual (Weighted)');
        $response->assertDontSee('Latest Target (Weighted)');

        // Since the target on 2026-10-20 is in the future, it should NOT be marked as Behind Target yet
        $response->assertDontSee('table-danger');
        $response->assertDontSee('Behind Target');

        // Now create a past target milestone: 50 units on 2026-10-10 (which has passed)
        // Since actual is only 40 units (< 50 past target milestone), it IS now delayed!
        $pastTarget = new Accomplishment();
        $pastTarget->contract_item_id = $this->contractItem->id;
        $pastTarget->type = 'TARGET';
        $pastTarget->entry_data = '2026-10-10';
        $pastTarget->quantity = 50.0;
        $pastTarget->remarks = 'Past Target entry';
        $pastTarget->created_by = $this->user->id;
        $pastTarget->save();

        $responseWithPastTarget = $this->actingAs($this->user)->get('/accomplishment/contract_item/' . $this->contractItem->id);
        $responseWithPastTarget->assertStatus(200);
        $responseWithPastTarget->assertSee('table-danger');
        $responseWithPastTarget->assertSee('Behind Target');

        // Check Accomplishment Studio has behind target highlighting class and styles
        $responseStudio = $this->actingAs($this->user)->get('/accomplishment/project/' . $this->project->id . '/studio');
        $responseStudio->assertStatus(200);
        $responseStudio->assertSee('.row-component-behind', false);
        $responseStudio->assertSee('row-component-behind', false);
        $responseStudio->assertSee('Weighted Accomplishment Breakdown', false);
        $responseStudio->assertSee('Weight %', false);
        $responseStudio->assertSee('Accomplished Amount', false);
        $responseStudio->assertSee('Weighted Actual %', false);
        $responseStudio->assertSee('formatNum(row.weight)', false);
        $responseStudio->assertSee('formatNumber(row.actualMoney)', false);
        $responseStudio->assertSee('formatNum(row.weightedActual)', false);
        $responseStudio->assertSee('sumWeightedActual += weightedActual', false);
        $responseStudio->assertSee('accDateStr <= todayDateStr', false);
    }

    /** @test */
    public function it_allows_actual_accomplishment_without_future_target_as_long_as_quantity_does_not_exceed_last_target()
    {
        $this->grantAccessCode($this->user, 'accomplishment:all:create');

        // 0. If no target exists at all, creating actual fails
        $responseNoTarget = $this->actingAs($this->user)->postJson('/api/accomplishment/create', [
            'contract_item_id' => $this->contractItem->id,
            'type'             => 'ACTUAL',
            'entry_date'       => '2026-09-15',
            'quantity'         => 20.0,
            'remarks'          => 'Actual with no target at all'
        ]);

        $responseNoTarget->assertStatus(200);
        $responseNoTarget->assertJson([
            'status' => -2,
            'message' => 'Failed Validation',
            'data' => [
                'entry_date' => ['Cannot create an actual entry. No planned target exists on or after this date.']
            ]
        ]);

        // Only have a target in the past (e.g. 2026-09-10) with quantity 50.0
        $pastTarget = new Accomplishment();
        $pastTarget->contract_item_id = $this->contractItem->id;
        $pastTarget->type = 'TARGET';
        $pastTarget->entry_data = '2026-09-10';
        $pastTarget->quantity = 50.0;
        $pastTarget->remarks = 'Past target milestone';
        $pastTarget->created_by = $this->user->id;
        $pastTarget->save();

        // 1. Try to create ACTUAL on 2026-09-15 with quantity 60.0 (> 50.0) via _create -> fails
        $responseExceed = $this->actingAs($this->user)->postJson('/api/accomplishment/create', [
            'contract_item_id' => $this->contractItem->id,
            'type'             => 'ACTUAL',
            'entry_date'       => '2026-09-15',
            'quantity'         => 60.0,
            'remarks'          => 'Actual exceeding last target'
        ]);

        $responseExceed->assertStatus(200);
        $responseExceed->assertJson([
            'status' => -2,
            'message' => 'Failed Validation',
            'data' => [
                'quantity' => ['The quantity must not be greater than the last target quantity of 50.']
            ]
        ]);

        // 2. Try to create ACTUAL on 2026-09-15 with quantity 60.0 (> 50.0) via _add -> fails
        $responseExceedAdd = $this->actingAs($this->user)->postJson('/api/accomplishment/add', [
            'contract_item_id' => $this->contractItem->id,
            'entry_data'       => '2026-09-15',
            'quantity'         => 60.0,
            'remarks'          => 'Actual exceeding last target via add',
            'type'             => 'ACTUAL'
        ]);

        $responseExceedAdd->assertStatus(200);
        $responseExceedAdd->assertJson([
            'status' => -2,
            'message' => 'Failed Validation',
            'data' => [
                'quantity' => ['The quantity must not be greater than the last target quantity of 50.']
            ]
        ]);

        // 3. Create ACTUAL on 2026-09-15 with quantity 20.0 (<= 50.0) via _create -> succeeds!
        $response1 = $this->actingAs($this->user)->postJson('/api/accomplishment/create', [
            'contract_item_id' => $this->contractItem->id,
            'type'             => 'ACTUAL',
            'entry_date'       => '2026-09-15',
            'quantity'         => 20.0,
            'remarks'          => 'Actual after past target <= last target qty'
        ]);

        $response1->assertStatus(200);
        $response1->assertJson([
            'status' => 1,
            'message' => ''
        ]);

        // 4. Create ACTUAL on 2026-09-16 with quantity 50.0 (equal to last target qty) via _add -> succeeds!
        $response2 = $this->actingAs($this->user)->postJson('/api/accomplishment/add', [
            'contract_item_id' => $this->contractItem->id,
            'entry_data'       => '2026-09-16',
            'quantity'         => 50.0,
            'remarks'          => 'Actual after past target equal to last target qty',
            'type'             => 'ACTUAL'
        ]);

        $response2->assertStatus(200);
        $response2->assertJson([
            'status' => 1,
            'message' => ''
        ]);
    }

    /** @test */
    public function it_defaults_to_active_status_filter_on_projects_page()
    {
        $response = $this->actingAs($this->user)->get('/projects');
        $response->assertStatus(200);
        $response->assertSee('<option value="ACTV" selected>ACTV</option>', false);
    }

    protected function grantAccessCode($user, $codeString)
    {
        $accessCode = \App\Models\AccessCode::where('code', $codeString)->first();
        if (!$accessCode) {
            $accessCode = new \App\Models\AccessCode();
            $accessCode->code = $codeString;
            $accessCode->description = 'Test access code description';
            $accessCode->save();
        }

        $role = new \App\Models\Role();
        $role->name = 'Test Role ' . uniqid();
        $role->description = 'Test Description';
        $role->save();

        $roleAccessCode = new \App\Models\RoleAccessCode();
        $roleAccessCode->role_id = $role->id;
        $roleAccessCode->access_code_id = $accessCode->id;
        $roleAccessCode->save();

        $userRole = new \App\Models\UserRole();
        $userRole->user_id = $user->id;
        $userRole->role_id = $role->id;
        $userRole->save();
    }
}
