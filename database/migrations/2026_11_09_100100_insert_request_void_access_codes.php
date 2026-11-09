<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $codes = [
            [
                'code' => 'material_request:all:request_void',
                'description' => 'Can request void for all material quantity requests',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'material_request:own:request_void',
                'description' => 'Can request void for own material quantity requests',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'direct_material_request:all:request_void',
                'description' => 'Can request void for all direct material quantity requests',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'direct_material_request:own:request_void',
                'description' => 'Can request void for own direct material quantity requests',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($codes as $code) {
            $exists = DB::table('access_codes')->where('code', $code['code'])->exists();
            if (!$exists) {
                DB::table('access_codes')->insert($code);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('access_codes')->whereIn('code', [
            'material_request:all:request_void',
            'material_request:own:request_void',
            'direct_material_request:all:request_void',
            'direct_material_request:own:request_void',
        ])->delete();
    }
};
