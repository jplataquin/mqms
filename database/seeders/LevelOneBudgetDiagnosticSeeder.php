<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Unit;
use App\Models\MaterialGroup;
use App\Models\MaterialItem;
use App\Models\Project;
use App\Models\Section;
use App\Models\ContractItem;
use App\Models\Component;
use App\Models\ComponentItem;
use App\Models\MaterialQuantity;
use App\Models\MaterialQuantityRequest;
use App\Models\MaterialQuantityRequestItem;
use Carbon\Carbon;

class LevelOneBudgetDiagnosticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Ensure a user exists for audit tracking
        $user = User::first();
        if (!$user) {
            $user = new User();
            $user->name = 'Diagnostic Admin';
            $user->email = 'admin@mqms.local';
            $user->password = bcrypt('password');
            $user->save();
        }

        // 2. Units
        $unitPcs = Unit::where('text', 'Pcs')->first();
        if (!$unitPcs) {
            $unitPcs = new Unit();
            $unitPcs->text = 'Pcs';
            $unitPcs->created_by = $user->id;
            $unitPcs->save();
        }

        $unitBag = Unit::where('text', 'Bag')->first();
        if (!$unitBag) {
            $unitBag = new Unit();
            $unitBag->text = 'Bag';
            $unitBag->created_by = $user->id;
            $unitBag->save();
        }

        // 3. Material Groups & Items
        $groupSteel = MaterialGroup::where('name', 'Reinforcing Steel Bars')->first();
        if (!$groupSteel) {
            $groupSteel = new MaterialGroup();
            $groupSteel->name = 'Reinforcing Steel Bars';
            $groupSteel->created_by = $user->id;
            $groupSteel->save();
        }

        $groupCement = MaterialGroup::where('name', 'Cement & Aggregates')->first();
        if (!$groupCement) {
            $groupCement = new MaterialGroup();
            $groupCement->name = 'Cement & Aggregates';
            $groupCement->created_by = $user->id;
            $groupCement->save();
        }

        $materialSteel = MaterialItem::where('name', 'Deformed Bar 16mm x 6m Grade 40')->first();
        if (!$materialSteel) {
            $materialSteel = new MaterialItem();
            $materialSteel->material_group_id = $groupSteel->id;
            $materialSteel->name = 'Deformed Bar 16mm x 6m Grade 40';
            $materialSteel->brand = 'SteelAsia';
            $materialSteel->specification_unit_packaging = '6.0m length / pc';
            $materialSteel->created_by = $user->id;
            $materialSteel->save();
        }

        $materialCement = MaterialItem::where('name', 'Portland Cement Type 1')->first();
        if (!$materialCement) {
            $materialCement = new MaterialItem();
            $materialCement->material_group_id = $groupCement->id;
            $materialCement->name = 'Portland Cement Type 1';
            $materialCement->brand = 'Holcim';
            $materialCement->specification_unit_packaging = '40kg bag';
            $materialCement->created_by = $user->id;
            $materialCement->save();
        }

        // 4. Project
        $project = new Project();
        $project->name = 'Level 1 Diagnostic Demo Project (' . Carbon::now()->format('M d H:i') . ')';
        $project->status = 'ACTV';
        $project->created_by = $user->id;
        $project->save();

        // 5. Section
        $section = new Section();
        $section->project_id = $project->id;
        $section->name = 'Section 1 - Substructure & Foundation';
        $section->created_by = $user->id;
        $section->save();

        // 6. Contract Item
        $contractItem = new ContractItem();
        $contractItem->section_id = $section->id;
        $contractItem->item_code = 'CI-404';
        $contractItem->description = 'Reinforcing Steel & Concrete Structures';
        $contractItem->unit_id = $unitPcs->id;
        $contractItem->contract_quantity = 500.00;
        $contractItem->contract_unit_price = 1200.00;
        $contractItem->created_by = $user->id;
        $contractItem->save();

        // 7. Component
        $component = new Component();
        $component->section_id = $section->id;
        $component->contract_item_id = $contractItem->id;
        $component->name = 'Footing Column Rebar & Pouring';
        $component->unit_id = $unitPcs->id;
        $component->quantity = 1.0;
        $component->status = 'APRV';
        $component->created_by = $user->id;
        $component->save();

        // 8. Component Items
        // Item 1: Designed to FAIL / EXCEED budget (Allowed = 100, Consumed = 60 * 2.0 = 120)
        $componentItemOverBudget = new ComponentItem();
        $componentItemOverBudget->component_id = $component->id;
        $componentItemOverBudget->name = '16mm Deformed Bar Budget Limit';
        $componentItemOverBudget->unit_id = $unitPcs->id;
        $componentItemOverBudget->quantity = 100.00; // Allowed Budget = 100.00
        $componentItemOverBudget->budget_price = 450.00;
        $componentItemOverBudget->function_type_id = 3;
        $componentItemOverBudget->function_variable = 1;
        $componentItemOverBudget->created_by = $user->id;
        $componentItemOverBudget->save();

        // Item 2: Designed to PASS / WITHIN budget (Allowed = 50, Consumed = 25 * 1.0 = 25)
        $componentItemWithinBudget = new ComponentItem();
        $componentItemWithinBudget->component_id = $component->id;
        $componentItemWithinBudget->name = 'Portland Cement Budget Limit';
        $componentItemWithinBudget->unit_id = $unitBag->id;
        $componentItemWithinBudget->quantity = 50.00; // Allowed Budget = 50.00
        $componentItemWithinBudget->budget_price = 280.00;
        $componentItemWithinBudget->function_type_id = 3;
        $componentItemWithinBudget->function_variable = 1;
        $componentItemWithinBudget->created_by = $user->id;
        $componentItemWithinBudget->save();

        // 9. Material Quantities (with Equivalent factor)
        // Equivalent factor of 2.0 for Item 1
        $mqSteel = new MaterialQuantity();
        $mqSteel->component_item_id = $componentItemOverBudget->id;
        $mqSteel->material_item_id = $materialSteel->id;
        $mqSteel->quantity = 50.00;
        $mqSteel->equivalent = 2.0; // Factor: 2.0
        $mqSteel->created_by = $user->id;
        $mqSteel->save();

        // Equivalent factor of 1.0 for Item 2
        $mqCement = new MaterialQuantity();
        $mqCement->component_item_id = $componentItemWithinBudget->id;
        $mqCement->material_item_id = $materialCement->id;
        $mqCement->quantity = 50.00;
        $mqCement->equivalent = 1.0; // Factor: 1.0
        $mqCement->created_by = $user->id;
        $mqCement->save();

        // 10. Approved Material Quantity Request (MQR)
        $mqr = new MaterialQuantityRequest();
        $mqr->project_id = $project->id;
        $mqr->section_id = $section->id;
        $mqr->contract_item_id = $contractItem->id;
        $mqr->component_id = $component->id;
        $mqr->status = 'APRV';
        $mqr->description = 'Approved site requisition for Footing Rebar & Pouring';
        $mqr->date_needed = Carbon::now()->addDays(5)->format('Y-m-d');
        $mqr->approved_by = $user->id;
        $mqr->approved_at = Carbon::now();
        $mqr->created_by = $user->id;
        $mqr->save();

        // 11. Approved Request Items
        // Requested 60 pcs * 2.0 equivalent = 120 actual (exceeds budget of 100 by 20)
        $mqriOver = new MaterialQuantityRequestItem();
        $mqriOver->material_quantity_request_id = $mqr->id;
        $mqriOver->component_item_id = $componentItemOverBudget->id;
        $mqriOver->material_item_id = $materialSteel->id;
        $mqriOver->requested_quantity = 60.00;
        $mqriOver->status = 'APRV';
        $mqriOver->save();

        // Requested 25 bags * 1.0 equivalent = 25 actual (within budget of 50)
        $mqriWithin = new MaterialQuantityRequestItem();
        $mqriWithin->material_quantity_request_id = $mqr->id;
        $mqriWithin->component_item_id = $componentItemWithinBudget->id;
        $mqriWithin->material_item_id = $materialCement->id;
        $mqriWithin->requested_quantity = 25.00;
        $mqriWithin->status = 'APRV';
        $mqriWithin->save();

        if ($this->command) {
            $this->command->info("Level 1 Budget Diagnostic test data successfully seeded!");
            $this->command->info("---------------------------------------------------------");
            $this->command->info("Project ID:       {$project->id} ({$project->name})");
            $this->command->info("Over-Budget Item: {$componentItemOverBudget->name} (Budget: 100, Requested Actual: 120, Over: 20)");
            $this->command->info("In-Budget Item:   {$componentItemWithinBudget->name} (Budget: 50, Requested Actual: 25)");
            $this->command->info("---------------------------------------------------------");
            $this->command->info("Run protocol diagnostic command:");
            $this->command->info("php artisan protocol:run level-1-budget-diagnostic --project={$project->id}");
        }
    }
}
