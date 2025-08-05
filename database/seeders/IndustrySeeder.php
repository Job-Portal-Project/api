<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\Occupation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IndustrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Industry::factory()
            ->count(10)
            ->has(Occupation::factory()->count(10))
            ->create();
    }
}
