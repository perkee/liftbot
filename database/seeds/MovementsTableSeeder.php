<?php

use Illuminate\Database\Seeder;

class MovementsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (explode('|', "Squat|Front Squat|Bench Press|Overhead Press|Deadlift|Incline Bench Press") as $name) {
            \App\Movement::firstOrCreateFromName($name)->save();
        }
    }
}
