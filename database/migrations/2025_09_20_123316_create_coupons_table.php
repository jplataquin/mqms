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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->char('status',4)->default('UNCL');
            $table->char('salt',16);
            $table->char('code',64);
            $table->decimal('amount',10,2);
            $table->string('claimed_by_name')->nullable();
            
            $table->bigInteger('created_by');
            $table->bigInteger('processed_by')->nullable(); 
            $table->bigInteger('approved_by')->nullable();
            $table->bigInteger('rejected_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();
            $table->bigInteger('void_by')->nullable();

            $table->datetime('claimed_at')->nullable();
            $table->datetime('processed_at')->nullable();
            $table->datetime('approved_at')->nullable();
            $table->datetime('rejected_at')->nullable();
            $table->datetime('void_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
