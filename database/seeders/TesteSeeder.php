<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Classes;
use App\Models\Courses;
use App\Models\Modules;
use App\Models\MetricCourses;
use App\Models\MetricClasses;
use App\Models\MetricModules;
use App\Models\ClassModuleSubscripton;
use App\Models\UserSubscription;
use App\Models\ClassesHistories;
use App\Models\CoursesHistories;
use App\Models\ModulesHistories;
use App\Models\Teste;
//use TheMembers\User;
//use App\User;

class TesteSeeder extends Seeder
{
    public function run()
    {
        //$count = User::count();
        //$count = "trinta e cinco marcos";

        $this->Teste();
        return 'end';
        //echo $count;
    }

    public function Teste(){
        $lists = Courses::with('modules')->get();

        foreach($lists as $list){
            $item = new Teste();
            $item->name = $list->title;
            $item->save();
        }
    }
}