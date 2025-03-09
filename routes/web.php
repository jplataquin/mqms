<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Process;
use \App\Http\Middleware\CheckForResetPassword;
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

//Auth::routes();
// Authentication Routes...
Route::get('login', [App\Http\Controllers\Auth\LoginController::class,'showLoginForm'])->name('login');
Route::post('login', [App\Http\Controllers\Auth\LoginController::class,'login']);
Route::post('logout', [App\Http\Controllers\Auth\LoginController::class,'logout'])->name('logout');

// Registration Routes...
// Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
// Route::post('register', 'Auth\RegisterController@register');

// Password Reset Routes...
// Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
// Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
// Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
// Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

Route::get('/gitpull',function(){
    
    $result = Process::run('git pull');
 
    return $result->output();
});

Route::middleware(['auth'])->group(function(){

    Route::get('/reset_password',[App\Http\Controllers\UserController::class, 'reset_password']);
});

Route::middleware(['auth',CheckForResetPassword::class])->group(function () {
    
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


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
    
    Route::get('/project/studio/{id}', [App\Http\Controllers\ProjectController::class, 'studio_display']);
    Route::get('/project/studio/overview/{project_id}', [App\Http\Controllers\project_studio\OverviewController::class, 'display']);

    Route::get('/project/{project_id}/section/create', [App\Http\Controllers\SectionController::class, 'create']);
    Route::get('/project/section/{id}', [App\Http\Controllers\SectionController::class, 'display']);
    Route::get('/project/section/print/{id}', [App\Http\Controllers\SectionController::class, 'print']);
    
    Route::get('/project/section/contract_item/create/{section_id}', [App\Http\Controllers\ContractItemController::class, 'create']);
    Route::get('/project/section/contract_item/{id}', [App\Http\Controllers\ContractItemController::class, 'display']);
    Route::get('/project/section/contract_items', [App\Http\Controllers\ContractItemController::class, 'list']);
    
    Route::get('/project/section/contract_item/component/{id}', [App\Http\Controllers\ComponentController::class, 'display']);
    Route::get('/project/section/contract_item/component/print/{id}', [App\Http\Controllers\ComponentController::class, 'preview']);
    Route::get('/material_budget/report/{id}', [App\Http\Controllers\MaterialQuantityController::class, 'report']);
    Route::get('component_item/report/{id}', [App\Http\Controllers\ComponentItemController::class, 'report']);

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
    
    

    Route::get('/material_quantity_request/create/{project_id}/{section_id}/{contract_item_id}/{component_id}', [App\Http\Controllers\MaterialQuantityRequestController::class, 'create']);
    Route::get('/material_quantity_request/{id}', [App\Http\Controllers\MaterialQuantityRequestController::class, 'display']);
    Route::get('/material_quantity_request/print/{id}', [App\Http\Controllers\MaterialQuantityRequestController::class, 'print']);
    Route::get('/material_quantity_requests', [App\Http\Controllers\MaterialQuantityRequestController::class, 'list']);
    Route::get('/material_quantity_request/select/create', [App\Http\Controllers\MaterialQuantityRequestController::class, 'selectCreate']);
    Route::get('/material_quantity_request/po_list/{id}', [App\Http\Controllers\MaterialQuantityRequestController::class, 'po_list']);
   

    Route::get('/review/material_quantity_request/{id}', [App\Http\Controllers\Review\MaterialQuantityRequestReviewController::class, 'display']);
    Route::get('/review/material_quantity_requests', [App\Http\Controllers\Review\MaterialQuantityRequestReviewController::class, 'list']);
   
    Route::get('/review/material_canvass', [App\Http\Controllers\Review\MaterialCanvassReviewController::class, 'list']);
    Route::get('/review/material_canvass/{id}', [App\Http\Controllers\Review\MaterialCanvassReviewController::class, 'display']);
    Route::get('/review/material_canvass/test_unavailable/{id}', [App\Http\Controllers\Review\MaterialCanvassReviewController::class, '_test_unavailable']);
    
 
    Route::get('/review/purchase_order/{id}', [App\Http\Controllers\Review\PurchaseOrderReviewController::class, 'display']);
    Route::get('/review/purchase_orders', [App\Http\Controllers\Review\PurchaseOrderReviewController::class, 'list']);
   
    Route::get('/material_canvass', [App\Http\Controllers\MaterialCanvassController::class, 'list']);
    Route::get('/material_canvass/{id}', [App\Http\Controllers\MaterialCanvassController::class, 'display']);
    Route::get('/material_canvass/print/{id}', [App\Http\Controllers\MaterialCanvassController::class, 'print']);
   
    Route::get('/review/components', [App\Http\Controllers\Review\ComponentReviewController::class, 'list']);
    Route::get('/review/component/{contract_item_id}/{component_id?}', [App\Http\Controllers\Review\ComponentReviewController::class, 'display']);
    

    Route::get('/purchase_orders', [App\Http\Controllers\PurchaseOrderController::class, 'list']);
    Route::get('/purchase_order/create/select', [App\Http\Controllers\PurchaseOrderController::class, 'select']);
    Route::get('/purchase_order/create/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'create']);
    Route::get('/purchase_order/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'display']);
    Route::get('/purchase_order/print/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'print']);

   // Route::get('/report/a/select', [App\Http\Controllers\Report\ReportAController::class, 'select']);
    //Route::get('/report/a/generate/{project_id}/{section_id}/{component_id}', [App\Http\Controllers\Report\ReportAController::class, 'generate']);
    
    Route::get('/report/project/parameters', [App\Http\Controllers\Report\ProjectReportController::class, 'parameters']);
    Route::get('/report/project/generate', [App\Http\Controllers\Report\ProjectReportController::class, 'generate']);
    Route::get('/report/project/print', [App\Http\Controllers\Report\ProjectReportController::class, 'print']);
    

    Route::get('/report/price/parameters', [App\Http\Controllers\Report\PriceReportController::class, 'parameters']);
    Route::get('/report/price/generate', [App\Http\Controllers\Report\PriceReportController::class, 'generate']);
    Route::get('/report/price/print', [App\Http\Controllers\Report\PriceReportController::class, 'print']);

    Route::get('/users', [App\Http\Controllers\UserController::class, 'list']);
    Route::get('/user/create', [App\Http\Controllers\UserController::class, 'create']);
    Route::get('/user/{id}', [App\Http\Controllers\UserController::class, 'display']);
    Route::get('/me',[App\Http\Controllers\UserController::class, 'me']);

    Route::get('/test_mq/{id}',[App\Http\Controllers\MaterialQuantityController::class, 'test_mq']);
});


Route::get('datepicker.js', function(){

    $response = Response::make(File::get(base_path('node_modules/vanillajs-datepicker/js/Datepicker.js')), 200);
    $response->header("Content-Type", 'text/javascript');

    return $response;
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

Route::get('/ui_components/comment/{file}', function($file){

    $response = Response::make(File::get(base_path('resources/ui_components/comment/'.$file)), 200);
    $response->header("Content-Type", 'text/javascript');

    return $response;
});

Route::get('/ui_components/create_forms/{file}', function($file){

    $response = Response::make(File::get(base_path('resources/ui_components/create_forms/'.$file)), 200);
    $response->header("Content-Type", 'text/javascript');

    return $response;
});