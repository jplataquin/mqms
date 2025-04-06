<?php
namespace App\Http\Controllers\Review;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Section;
use App\Models\Unit;
use App\Http\Traits\BudgetTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BudgetReviewController extends Controller
{

    use BudgetTrait;
    
    public function list(){

        $projects = Project::orderBy('name','ASC')->get();

        return view('/review/budget/list',[
            'projects' => $projects
        ]);
    }
    
    public function display($section_id){

        $section_id = (int)$section_id;

        return view('/review/budget/display',[
            'section_id' => $section_id
        ]);
    }

    public function sheet($section_id){
        
        $data = $this->prepareData($section_id);

        return view('/review/budget/sheet',$data);
    }
}