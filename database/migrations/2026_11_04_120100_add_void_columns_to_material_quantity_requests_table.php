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
        Schema::table('material_quantity_requests', function (Blueprint $table) {
            $table->bigInteger('void_by')->nullable()->after('rejected_at');
            $table->dateTime('void_at')->nullable()->after('void_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_quantity_requests', function (Blueprint $table) {
            $table->dropColumn(['void_by', 'void_at']);
        });
    }
};
