<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $food = Activity::create(['name' => 'Еда']);
        $cars = Activity::create(['name' => 'Автомобили']);


        $meat = Activity::create(['name' => 'Мясная продукция', 'parent_id' => $food->id]);
        $milk = Activity::create(['name' => 'Молочная продукция', 'parent_id' => $food->id]);

        $trucks = Activity::create(['name' => 'Грузовые', 'parent_id' => $cars->id]);
        $carsPersonal = Activity::create(['name' => 'Легковые', 'parent_id' => $cars->id]);


        Activity::create(['name' => 'Запчасти', 'parent_id' => $carsPersonal->id]);
        Activity::create(['name' => 'Аксессуары', 'parent_id' => $carsPersonal->id]);
    }
}
