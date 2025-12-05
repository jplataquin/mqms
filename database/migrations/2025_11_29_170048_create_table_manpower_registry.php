<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('manpower_registry', function (Blueprint $table) {
            $table->id();

            $table->char('status',4)->default('PEND');
            $table->char('type',7); //Labor/Skilled
            $table->char('structure_category',4)->nullable(); //VERT/HORI/BOTH

            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname');
            $table->string('suffix')->nullable();
            $table->char('gender',1);
            $table->date('birthdate');
            
            $table->string('mobile_no');
            $table->string('email')->nullable();

            $table->char('region',6);
            $table->string('province',6);
            $table->string('city_municipality',6);

            $table->boolean('mason')->default(false);
            $table->boolean('welder')->default(false);
            $table->boolean('painter')->default(false);
            $table->boolean('electrician')->default(false);
            $table->boolean('carpenter')->default(false);
            $table->boolean('roofer')->default(false);
            $table->boolean('tile_setter')->default(false);
            $table->boolean('metal_worker')->default(false);

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
