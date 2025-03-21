<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UnitTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('unit')->insert([
            ['nama' => 'Pcs', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Lembar', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Kg', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
