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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('project_id');
            $table->bigInteger('section_id');
            $table->bigInteger('component_id');
            $table->bigInteger('material_quantity_request_id');
            $table->bigInteger('supplier_id');
            $table->bigInteger('payment_term_id');
            $table->char('status',4)->default('PEND');
            $table->json('extras')->nullable();

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('approved_by')->nullable();
            $table->bigInteger('rejected_by')->nullable();
            $table->bigInteger('void_by')->nullable();
            
            
            $table->dateTime('approved_at')->nullable();  
            $table->dateTime('rejected_at')->nullable();
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
        Schema::dropIfExists('purchase_orders');
    }
};
