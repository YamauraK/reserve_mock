<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = [
            [
                'code' => 'S01',
                'name' => '本店',
                'address' => '〇〇市1-1',
                'phone' => '090-0000-0001',
                'open_time' => '09:00',
                'close_time' => '20:00',
            ],
            [
                'code' => 'S02',
                'name' => '南店',
                'address' => '〇〇市2-2',
                'phone' => '090-0000-0002',
                'open_time' => '09:00',
                'close_time' => '20:00',
            ],
            [
                'code' => 'S03',
                'name' => '北店',
                'address' => '〇〇市3-3',
                'phone' => '090-0000-0003',
                'open_time' => '09:00',
                'close_time' => '20:00',
            ],
        ];

        foreach ($stores as $store) {
            Store::updateOrCreate(
                ['code' => $store['code']],
                [
                    'name' => $store['name'],
                    'address' => $store['address'],
                    'phone' => $store['phone'],
                    'open_time' => $store['open_time'],
                    'close_time' => $store['close_time'],
                    'is_active' => true,
                ]
            );
        }
    }
}
