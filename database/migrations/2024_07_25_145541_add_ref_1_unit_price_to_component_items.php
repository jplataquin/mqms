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
        Schema::table('component_items', function (Blueprint $table) {
            $table->decimal('ref_1_unit_price',10,2)->nullable()->after('ref_1_unit_id');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('component_items', function (Blueprint $table) {
            $table->dropColumn('ref_1_unit_price');
        });
    }
};
