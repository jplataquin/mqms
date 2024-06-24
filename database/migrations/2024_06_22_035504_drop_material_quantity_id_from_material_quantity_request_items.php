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
        Schema::table('material_quantity_request_items', function (Blueprint $table) {
            $table->dropColumn('material_quantity_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('material_quantity_request_items', function (Blueprint $table) {
            
            $table->bigInteger('material_quantity_id')->after('component_item_id');
        });
    }
};
