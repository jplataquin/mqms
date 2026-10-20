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
        Schema::create('direct_material_quantity_request_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('direct_material_quantity_request_id');
            $table->bigInteger('material_item_id');
            $table->char('status', 4)->default('PEND');
            $table->double('quantity');
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('direct_material_quantity_request_items');
    }
};
