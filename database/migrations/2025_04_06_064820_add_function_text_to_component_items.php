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
        Schema::table('component_items', function (Blueprint $table) {
            $table->text('function_text')->nullable()->after('function_variable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('component_items', function (Blueprint $table) {
            $table->dropColoumn('function_text');
        });
    }
};
