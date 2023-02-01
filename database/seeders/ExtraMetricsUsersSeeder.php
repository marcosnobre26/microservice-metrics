<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Classes;
use App\Models\Courses;
use App\Models\Modules;
use App\Models\MetricUsers;
use App\Models\MetricCourses;
use App\Models\MetricClasses;
use App\Models\MetricModules;
use App\Models\ClassModuleSubscripton;
use App\Models\UserSubscription;
use App\Models\ClassesHistories;
use App\Models\CoursesHistories;
use App\Models\ModulesHistories;
//use TheMembers\User;
use App\Models\User;

class ExtraMetricsUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->Users();
        return 'end';
    }

    public function Users(){//popular a tabela de metrics courses
        $list = Courses::with('modules.classes')->where('tenant_id',1591)->get();
        $courses = $list->groupBy('tenant_id');
        $count = 0;

        foreach($courses as $tenant){
            foreach($tenant as $course){

                $search = ClassModuleSubscripton::where('course_id', $course->id)->get();

                foreach($search as $register){

                    $list_users = UserSubscription::where('package_id', $register->package_id)->get();
                    

                    foreach($list_users as $user){
                        $time_plus = "00:00:00";
                        $time = "00:00:00";
                        if($user->user_id != null){
                            $user_register = User::where('id', $user->user_id)->first();
                            if($user_register != null){

                            
                                $finished = CoursesHistories::where('user_id', $user->user_id)->where('course_id', $course->id)->first();
                                $search_course_time = MetricCourses::where('package_id', $register->package_id)->where('course_id', $course->id)->first();
                                $time_total = $search_course_time->time_total;
                                $time_consumed = $this->courseTimeConsumed($course->id, $user->user_id);
                            
                                $percent_finished = 0;

                                $count_register = MetricUsers::where('user_id',$user_register->id)->where('course_id',$course->id)->count();
                                if($count_register === 0){
                                    $metric = new MetricUsers();
                                }
                                else{
                                    if($count_register > 1)
                                    {
                                        $metrics = MetricUsers::where('user_id',$user_register->id)->where('course_id',$course->id)->count();

                                        foreach($metrics as $item){
                                            $item->delete();
                                        }
                                        $metric = new MetricUsers();
                                    }
                                    else{
                                        $metric = MetricUsers::where('user_id',$user_register->id)->where('course_id',$course->id)->count();
                                    }
                                }

                                
                                $metric->course_id = $course->id;
                                $metric->user_id = $user_register->id;
                                $metric->name_user = $user_register->name;
                                $metric->email = $user_register->email;
                                $metric->time_consumed = $time_consumed;
                                $metric->package_id = $register->package_id;

                                for ($i = 0; $i < $count; $i++) {

                                    $time_plus = $this->plus_time( $time_total, $time_plus );
                                    $time = $time_plus;
                                }

                                if($time === "00:00:00" || $time_consumed === "00:00:00"){
                                    $metric->percent_watched = 0;
                                }
                                else{
                                    $metric->percent_watched = $this->percentWatched($time, $time_consumed);
                                }

                                if($finished){
                                    if($finished->finished === 0){
                                        $metric->finished = "Não";
                                    }
                                    else{
                                        $metric->finished = "Sim";
                                    }
                                    
                                }
                                else{
                                    $metric->finished = "Não";
                                }
                                $metric->tenant_id = $course->tenant_id;

                                if($time === "00:00:00" || $time_consumed === "00:00:00"){
                                    $metric->percent_watched = 0;
                                }
                                else{
                                    $metric->percent_watched = $this->percentWatched($time, $time_consumed);
                                }

                                $metric->save();
                                $time = "00:00:00";
                            }
                        }
                    }
                }
            }
        }
    }

    public function courseTimeConsumed($id, $user_id){
        $course = Courses::where('id', $id)->with('modules.classes')->first();
        $hora_um = "00:00:00";

        foreach($course->modules as $module){
            foreach($module->classes as $class){
                $hora_dois = $this->classConsumed($class->id, $user_id);

                $hora_um = $this->plus_time( $hora_um, $hora_dois );
                
            }
        }

        return $hora_um;

    }

    public function classConsumed($id, $user_id){
        $classes = ClassesHistories::where('class_id', $id)->where('user_id', $user_id)->get();
        $ponto = ':';
        $hora_um = "00:00:00";

        foreach($classes as $class){
            $format = strpos( $class->time, $ponto );
            if($class->time != null)
            {
                if(!$format){
                    $class->time = gmdate('H:i:s', $class->time);
                    //$class->save();
                }
            }
            else{
                $class->time = "00:00:00";
            };

            $hora_um = $this->plus_time( $hora_um, $class->time );

        }

        return $hora_um;
    }

    function plus_time( $time1, $time2 ) {

        $tempo1 = $time1;//tempo total
        $tempo2 = $time2;//tempo do aluno
        $exp1 = explode(":",$tempo1);
        $exp2 = explode(":",$tempo2);
        $qtdMinutes = 0;
        $qtdHours = 0;

        $seconds = (int) $exp1[2] ?? 0 + (int) $exp2[2] ?? 0;
        $minutes = (int) $exp1[1] ?? 0 + (int) $exp2[1] ?? 0;
        $hours = (int) $exp1[0] ?? 0 + (int) $exp2[0] ?? 0;

        $timeHour = '';
        $timeMinutes = '';
        $timeSeconds = '';

        $aditional_minutes = 0;
        $aditional_hours = 0;

        $timeSeconds = $exp1[2] + $exp2[2];

        

        if($timeSeconds > 60){
            $seconds_dividend = $timeSeconds;
            $seconds_divisor = 60; 

            $aditional_minutes = intdiv($seconds_dividend, $seconds_divisor);   
            while ($timeSeconds > 60) {
                $timeSeconds = $timeSeconds - 60;
            }
        }

        if(strlen($timeSeconds)<2){
            $timeSeconds = "0".$timeSeconds;
        }

        $timeMinutes=$exp1[1] + $exp2[1] + $aditional_minutes;

        if($timeMinutes > 60){
            $minutes_dividend = $timeMinutes;
            $minutes_divisor = 60; 

            $aditional_hours = intdiv($minutes_dividend, $minutes_divisor);
            while ($timeMinutes > 60) {
                $timeMinutes = $timeMinutes - 60;
            }
        }

        if(strlen($timeMinutes)<2){
            $timeMinutes = "0".$timeMinutes;
        }

        $timeHour=$exp1[0] + $exp2[0] + $aditional_hours;

        if(strlen($timeHour)<2){
            $timeHour = "0".$timeHour;
        }


        $time = $timeHour.':'.$timeMinutes.':'.$timeSeconds;

        return $time;
    }
}