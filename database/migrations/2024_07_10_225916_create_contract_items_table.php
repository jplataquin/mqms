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
        Schema::create('contract_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('section_id');
            $table->string('item_code');
            $table->text('description');
            
            $table->double('contract_quantity');
            $table->decimal('contract_unit_price',10,2);

            $table->double('ref_1_quantity')->nullable();
            $table->decimal('ref_1_unit_price',10,2)->nullable();
            
            $table->bigInteger('unit_id');
            

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();
            

            $table->softDeletes();
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
        Schema::dropIfExists('contract_items');
    }
};
