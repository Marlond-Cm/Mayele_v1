<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            FiliereSeeder::class,
            UniversiteSeeder::class,
            PlanSeeder::class,
            MatiereSeeder::class,
            CatalogueTechniqueSeeder::class,
            AdminSeeder::class,
        ]);
    }
}