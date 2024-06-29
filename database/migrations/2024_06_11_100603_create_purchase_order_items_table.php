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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('purchase_order_id');
            $table->bigInteger('component_item_id');
            $table->bigInteger('material_quantity_request_item_id');
            $table->bigInteger('material_canvass_id');
            $table->bigInteger('material_id');
            $table->char('status',4)->default('PEND');
            $table->double('quantity');
            $table->decimal('price',10,2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
