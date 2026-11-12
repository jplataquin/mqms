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
            $table->bigInteger('component_id')->nullable()->after('section_id');
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
            $table->dropColumn('component_id');
        });
    }
};
