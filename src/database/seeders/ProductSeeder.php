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
        Product::create(['sku'=>'OC-001','name'=>'おせち三段重','price'=>25000,'manufacturer'=>'メーカーA']);
        Product::create(['sku'=>'OC-002','name'=>'おせち二段重','price'=>18000,'manufacturer'=>'メーカーA']);
        Product::create(['sku'=>'EH-001','name'=>'恵方巻（上）','price'=>1200,'manufacturer'=>'メーカーB']);
    }
}
