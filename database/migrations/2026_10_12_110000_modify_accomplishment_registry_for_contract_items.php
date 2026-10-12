<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Erase old accomplishment records as instructed (user requested erasing existing records)
        DB::table('accomplishment_registry')->delete();

        Schema::table('accomplishment_registry', function (Blueprint $table) {
            $table->renameColumn('component_id', 'contract_item_id');
        });

        Schema::table('contract_items', function (Blueprint $table) {
            $table->boolean('exclude_from_monitoring')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_items', function (Blueprint $table) {
            $table->dropColumn('exclude_from_monitoring');
        });

        Schema::table('accomplishment_registry', function (Blueprint $table) {
            $table->renameColumn('contract_item_id', 'component_id');
        });
    }
};
