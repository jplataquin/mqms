<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/access_code/list', [App\Http\Controllers\AccessCodeController::class, '_list']);
    Route::post('/access_code/create', [App\Http\Controllers\AccessCodeController::class, '_create']);
    Route::post('/access_code/update', [App\Http\Controllers\AccessCodeController::class, '_update']);
    Route::post('/access_code/delete', [App\Http\Controllers\AccessCodeController::class, '_delete']);
   
    Route::get('/role/list', [App\Http\Controllers\RoleController::class, '_list']);
    Route::post('/role/create', [App\Http\Controllers\RoleController::class, '_create']);
    Route::post('/role/update', [App\Http\Controllers\RoleController::class, '_update']);
    Route::post('/role/delete', [App\Http\Controllers\RoleController::class, '_delete']); 
    Route::get('/role/access_codes/{role_id}', [App\Http\Controllers\RoleController::class, '_access_codes']);
    Route::post('/role/access_code/add', [App\Http\Controllers\RoleController::class, '_access_code_add']);
    Route::post('/role_access_code/remove', [App\Http\Controllers\RoleController::class, '_access_code_remove']);
    

    Route::get('/user/list', [App\Http\Controllers\UserController::class, '_list']);
    Route::get('/user/roles/{id}', [App\Http\Controllers\UserController::class, '_roles']);
    Route::post('/user/create', [App\Http\Controllers\UserController::class, '_create']);
    Route::post('/user/update', [App\Http\Controllers\UserController::class, '_update']);
    Route::post('/user/enable_reset_password', [App\Http\Controllers\UserController::class, '_enable_reset_password']);
    Route::post('/user/reset_password', [App\Http\Controllers\UserController::class, '_reset_password']);
    Route::post('/user/role/add', [App\Http\Controllers\UserController::class, '_add_role']);
    Route::post('/user/role/remove', [App\Http\Controllers\UserController::class, '_remove_role']);
    Route::post('/user/change_password', [App\Http\Controllers\UserController::class, 'user_change_password']);
    
    Route::get('/project/list', [App\Http\Controllers\ProjectController::class, '_list']);
    Route::post('/project/create', [App\Http\Controllers\ProjectController::class, '_create']);
    Route::post('/project/update', [App\Http\Controllers\ProjectController::class, '_update']);
    Route::post('/project/delete', [App\Http\Controllers\ProjectController::class, '_delete']);
    
    Route::get('/supplier/list', [App\Http\Controllers\SupplierController::class, '_list']);
    Route::post('/supplier/create', [App\Http\Controllers\SupplierController::class, '_create']);
    Route::post('/supplier/update', [App\Http\Controllers\SupplierController::class, '_update']);
    Route::post('/supplier/delete', [App\Http\Controllers\SupplierController::class, '_delete']);
    
    Route::get('/section/list', [App\Http\Controllers\SectionController::class, '_list']);
    Route::post('/section/create', [App\Http\Controllers\SectionController::class, '_create']);
    Route::post('/section/update', [App\Http\Controllers\SectionController::class, '_update']);
    Route::post('/section/delete', [App\Http\Controllers\SectionController::class, '_delete']);
    
    Route::get('/material/group/list', [App\Http\Controllers\MaterialGroupController::class, '_list']);
    Route::post('/material/group/create', [App\Http\Controllers\MaterialGroupController::class, '_create']);
    Route::post('/material/group/update', [App\Http\Controllers\MaterialGroupController::class, '_update']);
    Route::post('/material/group/delete', [App\Http\Controllers\MaterialGroupController::class, '_delete']);
    
    Route::get('/material/item/list', [App\Http\Controllers\MaterialItemController::class, '_list']);
    Route::post('/material/item/create', [App\Http\Controllers\MaterialItemController::class, '_create']);
    Route::post('/material/item/update', [App\Http\Controllers\MaterialItemController::class, '_update']);
    Route::post('/material/item/delete', [App\Http\Controllers\MaterialItemController::class, '_delete']);
    
    Route::get('/payment_term/list', [App\Http\Controllers\PaymentTermController::class, '_list']);
    Route::post('/payment_term/create', [App\Http\Controllers\PaymentTermController::class, '_create']);
    Route::post('/payment_term/update', [App\Http\Controllers\PaymentTermController::class, '_update']);
    Route::post('/payment_term/delete', [App\Http\Controllers\PaymentTermController::class, '_delete']);
    
    Route::get('/unit/list', [App\Http\Controllers\UnitController::class, '_list']);
    Route::post('/unit/create', [App\Http\Controllers\UnitController::class, '_create']);
    Route::post('/unit/update', [App\Http\Controllers\UnitController::class, '_update']);
    Route::post('/unit/delete', [App\Http\Controllers\UnitController::class, '_delete']);
  
    // Route::get('/material_budget/list', [App\Http\Controllers\MaterialBudgetController::class, '_list']);
    // Route::post('/material_budget/create', [App\Http\Controllers\MaterialBudgetController::class, '_create']);
    // Route::post('/material_budget/update', [App\Http\Controllers\MaterialBudgetController::class, '_update']);
    // Route::post('/material_budget/delete', [App\Http\Controllers\MaterialBudgetController::class, '_delete']);

    Route::get('/component', [App\Http\Controllers\ComponentController::class, '_retrieve']);
    Route::get('/component/list', [App\Http\Controllers\ComponentController::class, '_list']);
    Route::post('/component/create', [App\Http\Controllers\ComponentController::class, '_create']);
    Route::post('/component/update', [App\Http\Controllers\ComponentController::class, '_update']);
    Route::post('/component/delete', [App\Http\Controllers\ComponentController::class, '_delete']);

    Route::get('/component_item', [App\Http\Controllers\ComponentItemController::class, '_retrieve']);
    Route::post('/component_item/create', [App\Http\Controllers\ComponentItemController::class, '_create']);
    Route::post('/component_item/update', [App\Http\Controllers\ComponentItemController::class, '_update']);
    Route::post('/component_item/delete', [App\Http\Controllers\ComponentItemController::class, '_delete']);
    Route::post('/component_item/material/add', [App\Http\Controllers\ComponentItemController::class, '_material_add']);
    
    Route::get('/material_quantity/list', [App\Http\Controllers\MaterialQuantityController::class, '_list']);
    Route::post('/material_quantity/create', [App\Http\Controllers\MaterialQuantityController::class, '_create']);
    Route::post('/material_quantity/update', [App\Http\Controllers\MaterialQuantityController::class, '_update']);
    Route::post('/material_quantity/delete', [App\Http\Controllers\MaterialQuantityController::class, '_delete']);
    
    Route::post('/material_quantity_request/create', [App\Http\Controllers\MaterialQuantityRequestController::class, '_create']);
    Route::post('/material_quantity_request/update', [App\Http\Controllers\MaterialQuantityRequestController::class, '_update']);
    Route::get('/material_quantity_request/list', [App\Http\Controllers\MaterialQuantityRequestController::class, '_list']);
    Route::get('/material_quantity_request/total_approved_quantity', [App\Http\Controllers\MaterialQuantityRequestController::class, '_total_approved_quantity']);
    Route::get('/material_quantity_request/total_po_quantity', [App\Http\Controllers\MaterialQuantityRequestController::class, 'get_total_po_quantity']);
    Route::post('/material_quantity_request/revert_to_pending', [App\Http\Controllers\MaterialQuantityRequestController::class, '_revert_to_pending']);
    

    Route::get('/review/material_quantity_request/list', [App\Http\Controllers\Review\MaterialQuantityRequestReviewController::class, '_list']);
    Route::post('/review/material_quantity_request/approve', [App\Http\Controllers\Review\MaterialQuantityRequestReviewController::class, '_approve']);
    Route::post('/review/material_quantity_request/reject', [App\Http\Controllers\Review\MaterialQuantityRequestReviewController::class, '_reject']);
    
    Route::get('/review/purchase_order/list', [App\Http\Controllers\Review\PurchaseOrderReviewController::class, '_list']);
    Route::post('/review/purchase_order/approve', [App\Http\Controllers\Review\PurchaseOrderReviewController::class, '_approve']);
    Route::post('/review/purchase_order/reject', [App\Http\Controllers\Review\PurchaseOrderReviewController::class, '_reject']);
    Route::post('/review/purchase_order/reject_void', [App\Http\Controllers\Review\PurchaseOrderReviewController::class, '_reject_void']);
    Route::post('/review/purchase_order/void', [App\Http\Controllers\Review\PurchaseOrderReviewController::class, '_void']);
    
    Route::get('/review/material_canvass/list', [App\Http\Controllers\Review\MaterialCanvassReviewController::class, '_list']);
    Route::post('/review/material_canvass/approve', [App\Http\Controllers\Review\MaterialCanvassReviewController::class, '_approve']);
    Route::post('/review/material_canvass/reject', [App\Http\Controllers\Review\MaterialCanvassReviewController::class, '_reject']);
    
    Route::get('/review/component/list', [App\Http\Controllers\Review\ComponentReviewController::class, '_list']);
    Route::post('/review/component/approve', [App\Http\Controllers\Review\ComponentReviewController::class, '_approve']);
    Route::post('/review/component/reject', [App\Http\Controllers\Review\ComponentReviewController::class, '_reject']);
    Route::post('/review/component/revert_to_pending', [App\Http\Controllers\Review\ComponentReviewController::class, '_revert_to_pending']);
    

    Route::get('/material_canvass/list', [App\Http\Controllers\MaterialCanvassController::class, '_list']);
    Route::post('/material_canvass/create', [App\Http\Controllers\MaterialCanvassController::class, '_create']);
    Route::post('/material_canvass/delete', [App\Http\Controllers\MaterialCanvassController::class, '_delete']);
    Route::post('/material_canvass/void', [App\Http\Controllers\MaterialCanvassController::class, '_void']);


    Route::get('/purchase_order/select/list', [App\Http\Controllers\PurchaseOrderController::class, '_select']);
    Route::get('/purchase_order/list', [App\Http\Controllers\PurchaseOrderController::class, '_list']);
    Route::get('/purchase_order/total_ordered', [App\Http\Controllers\PurchaseOrderController::class, 'total_ordered']);
    Route::post('/purchase_order/create', [App\Http\Controllers\PurchaseOrderController::class, '_create']);
    Route::post('/purchase_order/request_void', [App\Http\Controllers\PurchaseOrderController::class, '_request_void']);
    Route::post('/purchase_order/delete', [App\Http\Controllers\PurchaseOrderController::class, '_delete']);
    
    
    Route::get('/contract_item/list', [App\Http\Controllers\ContractItemController::class, '_list']);
    Route::post('/contract_item/create', [App\Http\Controllers\ContractItemController::class, '_create']);
    Route::post('/contract_item/update', [App\Http\Controllers\ContractItemController::class, '_update']);
    Route::post('/contract_item/delete', [App\Http\Controllers\ContractItemController::class, '_delete']);


    Route::post('/comment/create', [App\Http\Controllers\CommentController::class, '_create']);
    Route::post('/comment/list', [App\Http\Controllers\CommentController::class, '_list']);
});
