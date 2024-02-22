<?php

namespace Database\Seeders;

use App\Models\Month;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MonthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $months = [
            ['label' => 'Budget de JANVIER', 'month_number' => 1],
            ['label' => 'Budget de FEVRIER', 'month_number' => 2],
            ['label' => 'Budget de MARS', 'month_number' => 3],
            ['label' => 'Budget d\'AVRIL', 'month_number' => 4],
            ['label' => 'Budget de MAI', 'month_number' => 5],
            ['label' => 'Budget de JUIN', 'month_number' => 6],
            ['label' => 'Budget de JUILLET', 'month_number' => 7],
            ['label' => 'Budget d\'AOÛT', 'month_number' => 8],
            ['label' => 'Budget de SEPTEMBRE', 'month_number' => 9],
            ['label' => 'Budget d\'OCTOBRE', 'month_number' => 10],
            ['label' => 'Budget de NOVEMBRE', 'month_number' => 11],
            ['label' => 'Budget de DECEMBRE', 'month_number' => 12],
        ];

        foreach($months as $value){
            $id = generateDBTableId(5, "App\Models\Month");
            Month::create(array_merge($value, ['id' => $id]));
        }
    }
}
