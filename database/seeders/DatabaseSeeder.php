<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        //$this->call(MetricsCoursesSeeder::class);
        //$this->call(MetricsModulesSeeder::class);
        //$this->call(MetricsClassesSeeder::class);
        //$this->call(MetricsUsersSeeder::class);
        $this->call(ExtraMetricsUsersSeeder::class);
    }
}
