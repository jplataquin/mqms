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
        Schema::table('direct_material_quantity_requests', function (Blueprint $table) {
            $table->date('date_needed')->nullable()->after('description');
        });

        Schema::table('direct_purchase_orders', function (Blueprint $table) {
            $table->date('date_needed')->nullable()->after('direct_material_quantity_request_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('direct_material_quantity_requests', function (Blueprint $table) {
            $table->dropColumn('date_needed');
        });

        Schema::table('direct_purchase_orders', function (Blueprint $table) {
            $table->dropColumn('date_needed');
        });
    }
};
