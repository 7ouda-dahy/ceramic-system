<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 🔥 إنشاء الأدمن
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'password' => Hash::make('12345678'),
            ]
        );

        // 🔥 تشغيل Seeder الفروع والخزن
        $this->call([
            BranchAndCashboxSeeder::class,
        ]);
    }
}