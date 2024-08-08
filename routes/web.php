<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Process;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::view('/peanut', 'peanut');

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/gitpull',function(){
    
    $result = Process::run('git pull');
 
    return $result->output();
});



Route::middleware(['auth'])->group(function () {

    Route::get('/access_code/create', [App\Http\Controllers\AccessCodeController::class, 'create']);
    Route::get('/access_code/{id}', [App\Http\Controllers\AccessCodeController::class, 'display']);
    Route::get('/access_codes', [App\Http\Controllers\AccessCodeController::class, 'list']);
    
    Route::get('/role/create', [App\Http\Controllers\RoleController::class, 'create']);
    Route::get('/role/{id}', [App\Http\Controllers\RoleController::class, 'display']);
    Route::get('/roles', [App\Http\Controllers\RoleController::class, 'list']);
    
    
    Route::get('/user_role/create', [App\Http\Controllers\UserRoleCodeController::class, 'create']);
    Route::get('/user_role/{id}', [App\Http\Controllers\UserRoleController::class, 'display']);
    Route::get('/user_roles', [App\Http\Controllers\UserRoleController::class, 'list']);
    
    Route::get('/project/create', [App\Http\Controllers\ProjectController::class, 'create']);
    Route::get('/project/{id}', [App\Http\Controllers\ProjectController::class, 'display']);
    Route::get('/projects', [App\Http\Controllers\ProjectController::class, 'list']);
    
    Route::get('/project/{project_id}/section/create', [App\Http\Controllers\SectionController::class, 'create']);
    Route::get('/project/section/{id}', [App\Http\Controllers\SectionController::class, 'display']);
    Route::get('/project/section/print/{id}', [App\Http\Controllers\SectionController::class, 'print']);
    
    Route::get('/project/section/contract_item/create/{section_id}', [App\Http\Controllers\ContractItemController::class, 'create']);
    Route::get('/project/section/contract_item/{id}', [App\Http\Controllers\ContractItemController::class, 'display']);
    Route::get('/project/section/contract_items', [App\Http\Controllers\ContractItemController::class, 'list']);
    
    Route::get('/project/section/contract_item/component/{id}', [App\Http\Controllers\ComponentController::class, 'display']);
    Route::get('/project/section/contract_item/component/print/{id}', [App\Http\Controllers\ComponentController::class, 'preview']);
    

    Route::get('/master_data/supplier/create', [App\Http\Controllers\SupplierController::class, 'create']);
    Route::get('/master_data/supplier/{id}', [App\Http\Controllers\SupplierController::class, 'display']);
    Route::get('/master_data/suppliers', [App\Http\Controllers\SupplierController::class, 'list']);
    
    Route::get('/master_data/material/group/create', [App\Http\Controllers\MaterialGroupController::class, 'create']);
    Route::get('/master_data/material/group/{id}', [App\Http\Controllers\MaterialGroupController::class, 'display']);
    Route::get('/master_data/material/groups', [App\Http\Controllers\MaterialGroupController::class, 'list']);
    
    Route::get('/master_data/material/item/create', [App\Http\Controllers\MaterialItemController::class, 'create']);
    Route::get('/master_data/material/item/{id}', [App\Http\Controllers\MaterialItemController::class, 'display']);
    Route::get('/master_data/material/items', [App\Http\Controllers\MaterialItemController::class, 'list']);
   
    Route::get('/master_data/payment_term/create', [App\Http\Controllers\PaymentTermController::class, 'create']);
    Route::get('/master_data/payment_term/{id}', [App\Http\Controllers\PaymentTermController::class, 'display']);
    Route::get('/master_data/payment_terms', [App\Http\Controllers\PaymentTermController::class, 'list']);
    
    Route::get('/master_data/unit/create', [App\Http\Controllers\UnitController::class, 'create']);
    Route::get('/master_data/unit/{id}', [App\Http\Controllers\UnitController::class, 'display']);
    Route::get('/master_data/units', [App\Http\Controllers\UnitController::class, 'list']);
    
    

    Route::get('/material_quantity_request/create/{project_id}/{section_id}/{component_id}', [App\Http\Controllers\MaterialQuantityRequestController::class, 'create']);
    Route::get('/material_quantity_request/{id}', [App\Http\Controllers\MaterialQuantityRequestController::class, 'display']);
    Route::get('/material_quantity_requests', [App\Http\Controllers\MaterialQuantityRequestController::class, 'list']);
    Route::get('/material_quantity_request/select/create', [App\Http\Controllers\MaterialQuantityRequestController::class, 'selectCreate']);
   
    Route::get('/review/material_quantity_request/{id}', [App\Http\Controllers\Review\MaterialQuantityRequestReviewController::class, 'display']);
    Route::get('/review/material_quantity_requests', [App\Http\Controllers\Review\MaterialQuantityRequestReviewController::class, 'list']);
   
    Route::get('/review/material_canvass', [App\Http\Controllers\Review\MaterialCanvassReviewController::class, 'list']);
    Route::get('/review/material_canvass/{id}', [App\Http\Controllers\Review\MaterialCanvassReviewController::class, 'display']);
    
 
    Route::get('/review/purchase_order/{id}', [App\Http\Controllers\Review\PurchaseOrderReviewController::class, 'display']);
    Route::get('/review/purchase_orders', [App\Http\Controllers\Review\PurchaseOrderReviewController::class, 'list']);
   
    Route::get('/material_canvass', [App\Http\Controllers\MaterialCanvassController::class, 'list']);
    Route::get('/material_canvass/{id}', [App\Http\Controllers\MaterialCanvassController::class, 'display']);
    Route::get('/material_canvass/print/{id}', [App\Http\Controllers\MaterialCanvassController::class, 'print']);
   
    Route::get('/review/components', [App\Http\Controllers\Review\ComponentReviewController::class, 'list']);
    Route::get('/review/component/{id}', [App\Http\Controllers\Review\ComponentReviewController::class, 'display']);
    

    Route::get('/purchase_orders', [App\Http\Controllers\PurchaseOrderController::class, 'list']);
    Route::get('/purchase_order/create/select', [App\Http\Controllers\PurchaseOrderController::class, 'select']);
    Route::get('/purchase_order/create/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'create']);
    Route::get('/purchase_order/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'display']);
    Route::get('/purchase_order/print/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'print']);

    Route::get('/report/a/select', [App\Http\Controllers\Reports\ReportAController::class, 'select']);
    Route::get('/report/a/generate/{project_id}/{section_id}/{component_id}', [App\Http\Controllers\Reports\ReportAController::class, 'generate']);
   

    Route::get('/users', [App\Http\Controllers\UserController::class, 'list']);
    Route::get('/users/create', [App\Http\Controllers\UserController::class, 'create']);
    Route::get('/user/{id}', [App\Http\Controllers\UserController::class, 'display']);
    
});



Route::get('adarna.js', function(){

    $response = Response::make(File::get(base_path('node_modules/adarna/build/adarna.js')), 200);
    $response->header("Content-Type", 'text/javascript');

    return $response;
});


Route::get('adarna.js.map', function(){

    $response = Response::make(File::get(base_path('node_modules/adarna/build/adarna.min.js.map')), 200);
    $response->header("Content-Type", 'text/javascript');

    return $response;
});

Route::get('/ui_components/{file}', function($file){

    $response = Response::make(File::get(base_path('resources/ui_components/'.$file)), 200);
    $response->header("Content-Type", 'text/javascript');

    return $response;
});