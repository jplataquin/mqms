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
        Schema::create('material_quantity_request_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('material_quantity_request_id');
            $table->bigInteger('component_item_id');
            $table->bigInteger('material_quantity_id');
            $table->decimal('requested_quantity',10,2);
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
        Schema::dropIfExists('material_quantity_request_items');
    }
};
