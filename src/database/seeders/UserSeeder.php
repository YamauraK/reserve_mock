<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//        $honten = Store::where('code','S01')->first();

//        User::create([
//            'name' => 'HQ 管理者',
//            'email'=> 'hq@example.com',
//            'password' => Hash::make('password'),
//            'role' => 'hq',
//            'store_id' => null,
//        ]);
//
//        User::create([
//            'name' => '本店スタッフ',
//            'email'=> 'store@example.com',
//            'password' => Hash::make('password'),
//            'role' => 'store',
//            'store_id' => $honten->id,
//        ]);

        User::create([
            'name' => '本店スタッフ',
            'email'=> 'yamaura@wingdoor.co.jp',
            'password' => Hash::make('password'),
            'role' => 'hq',
            'store_id' => null,
        ]);
    }
}
