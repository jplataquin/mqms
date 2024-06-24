<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('material_canvass', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('material_quantity_request_id');
            $table->bigInteger('material_quantity_request_item_id');
            $table->bigInteger('supplier_id');
            $table->char('status',4)->default('PEND');
            $table->decimal('price',10,2);
            $table->bigInteger('payment_term_id')->default(0);

            $table->bigInteger('approved_by')->nullable();
            $table->bigInteger('disapproved_by')->nullable();
            $table->bigInteger('void_by')->nullable();
            
            
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();
           
            $table->dateTime('approved_at')->nullable();  
            $table->dateTime('disapproved_at')->nullable();
            $table->dateTime('void_at')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('material_canvass');
    }
};
