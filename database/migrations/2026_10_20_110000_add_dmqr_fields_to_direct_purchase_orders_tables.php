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
        Schema::table('direct_purchase_orders', function (Blueprint $table) {
            $table->bigInteger('direct_material_quantity_request_id')->nullable()->after('section_id');
        });

        Schema::table('direct_purchase_order_items', function (Blueprint $table) {
            $table->bigInteger('direct_material_quantity_request_item_id')->nullable()->after('direct_purchase_order_id');
            $table->bigInteger('direct_material_canvass_id')->nullable()->after('direct_material_quantity_request_item_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('direct_purchase_orders', function (Blueprint $table) {
            $table->dropColumn('direct_material_quantity_request_id');
        });

        Schema::table('direct_purchase_order_items', function (Blueprint $table) {
            $table->dropColumn(['direct_material_quantity_request_item_id', 'direct_material_canvass_id']);
        });
    }
};
