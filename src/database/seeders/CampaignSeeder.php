<?php

namespace Database\Seeders;

use App\Models\Campaign;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Campaign::create(['name'=>'おせち2025','description'=>'おせち予約','start_date'=>'2025-10-01','end_date'=>'2025-12-25']);
        Campaign::create(['name'=>'恵方巻2026','description'=>'節分企画','start_date'=>'2026-01-10','end_date'=>'2026-02-03']);
    }
}
