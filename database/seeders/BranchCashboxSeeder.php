<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Cashbox;

class BranchCashboxSeeder extends Seeder
{
    public function run(): void
    {
        $beni = Branch::firstOrCreate(['name' => 'بني عبيد']);
        $fikria = Branch::firstOrCreate(['name' => 'الفكرية']);

        Cashbox::firstOrCreate([
            'name' => 'خزنة بني عبيد'
        ], [
            'branch_id' => $beni->id,
            'is_central' => false,
            'is_active' => true,
        ]);

        Cashbox::firstOrCreate([
            'name' => 'خزنة الفكرية'
        ], [
            'branch_id' => $fikria->id,
            'is_central' => false,
            'is_active' => true,
        ]);

        Cashbox::firstOrCreate([
            'name' => 'الخزنة المركزية'
        ], [
            'branch_id' => null,
            'is_central' => true,
            'is_active' => true,
        ]);
    }
}