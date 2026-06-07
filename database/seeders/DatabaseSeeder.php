<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UnitSeeder::class,
            UserSeeder::class,
            StandardCategorySeeder::class,
            SpmiPeriodSeeder::class,
            QualityStandardSeeder::class,
            StandardIndicatorSeeder::class,
        ]);

        Artisan::call('shield:install admin');
        Artisan::call('shield:generate --all --panel=admin --option=policies_and_permissions');
    }
}
