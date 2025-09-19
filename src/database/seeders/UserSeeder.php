<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $honten = Store::firstOrCreate(
            ['code' => 'S01'],
            [
                'name' => '本店',
                'address' => '〇〇市1-1',
                'phone' => '090-0000-0001',
                'open_time' => '09:00',
                'close_time' => '20:00',
            ]
        );

        User::updateOrCreate(
            ['email' => 'hq@example.com'],
            [
                'name' => 'HQ 管理者',
                'password' => Hash::make('password'),
                'role' => UserRole::HQ,
                'store_id' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'store@example.com'],
            [
                'name' => '本店スタッフ',
                'password' => Hash::make('password'),
                'role' => UserRole::STORE,
                'store_id' => $honten->id,
            ]
        );
    }
}
