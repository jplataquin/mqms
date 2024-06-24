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
        Schema::table('material_quantity_requests', function (Blueprint $table) {
            $table->dateTime('disapproved_at')->nullable()->after('updated_at');
            $table->bigInteger('disapproved_by')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('material_quantity_requests', function (Blueprint $table) {
            $table->dropColumn('disapproved_at');
            $table->dropColumn('disapproved_by');
        });
    }
};
