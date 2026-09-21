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
        Schema::rename('accomplishments', 'accomplishment_registry');
        
        Schema::table('accomplishment_registry', function (Blueprint $table) {
            $table->renameColumn('entry_date', 'entry_data');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accomplishment_registry', function (Blueprint $table) {
            $table->renameColumn('entry_data', 'entry_date');
        });
        
        Schema::rename('accomplishment_registry', 'accomplishments');
    }
};
