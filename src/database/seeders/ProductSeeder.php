<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'sku' => 'OC-001',
                'name' => 'おせち三段重',
                'price' => 25000,
                'manufacturer' => 'メーカーA',
            ],
            [
                'sku' => 'OC-002',
                'name' => 'おせち二段重',
                'price' => 18000,
                'manufacturer' => 'メーカーA',
            ],
            [
                'sku' => 'EH-001',
                'name' => '恵方巻（上）',
                'price' => 1200,
                'manufacturer' => 'メーカーB',
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'manufacturer' => $product['manufacturer'],
                    'description' => $product['description'] ?? null,
                    'is_active' => true,
                    'is_all_store' => true,
                ]
            );
        }
    }
}
