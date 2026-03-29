<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchAndCashboxSeeder extends Seeder
{
    public function run(): void
    {
        // branches
        $fikriyaId = DB::table('branches')->updateOrInsert(
            ['name' => 'الفكرية'],
            ['is_active' => 1, 'created_at' => now(), 'updated_at' => now()]
        );

        $baniObeidId = DB::table('branches')->updateOrInsert(
            ['name' => 'بني عبيد'],
            ['is_active' => 1, 'created_at' => now(), 'updated_at' => now()]
        );

        // get ids
        $fikriya = DB::table('branches')->where('name', 'الفكرية')->first();
        $baniObeid = DB::table('branches')->where('name', 'بني عبيد')->first();

        // cashboxes
        DB::table('cashboxes')->updateOrInsert(
            ['slug' => 'fikriya'],
            [
                'branch_id' => $fikriya?->id,
                'name' => 'خزنة الفكرية',
                'is_central' => 0,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        DB::table('cashboxes')->updateOrInsert(
            ['slug' => 'bani-obeid'],
            [
                'branch_id' => $baniObeid?->id,
                'name' => 'خزنة بني عبيد',
                'is_central' => 0,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        DB::table('cashboxes')->updateOrInsert(
            ['slug' => 'central'],
            [
                'branch_id' => null,
                'name' => 'الخزنة المركزية',
                'is_central' => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }
}