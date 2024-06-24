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
            $table->char('status',4)->default('PEND')->after('id');
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
            $table->dropColumn('status');
        });
    }
};
