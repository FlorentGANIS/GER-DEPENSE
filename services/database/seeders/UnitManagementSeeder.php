<?php

namespace Database\Seeders;

use App\Models\ManagementUnit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['label' => 'PIECE'],
            ['label' => 'KG'],
            ['label' => 'L'],
            ['label' => 'CARTON'],
            ['label' => 'SACHET'],
            ['label' => 'ROULEAU']
        ];

        foreach($data as $value){
            $id = generateDBTableId(5, "App\Models\ManagementUnit");
            ManagementUnit::create(array_merge($value, ['id' => $id]));
        }

    }
}
