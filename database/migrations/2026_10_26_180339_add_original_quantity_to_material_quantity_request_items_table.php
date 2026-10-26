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
        Schema::table('material_quantity_request_items', function (Blueprint $table) {
            $table->decimal('original_quantity', 10, 2)->nullable()->default(null)->after('requested_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_quantity_request_items', function (Blueprint $table) {
            $table->dropColumn('original_quantity');
        });
    }
};
