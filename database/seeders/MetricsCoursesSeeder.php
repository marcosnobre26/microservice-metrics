<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Courses;
use App\Models\MetricCourses;
use App\Models\ClassModuleSubscripton;
use App\Models\UserSubscription;
use App\Models\ClassesHistories;
use App\Models\CoursesHistories;
//use TheMembers\User;
//use App\User;

class MetricsCoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //$count = User::count();
        //$count = "trinta e cinco marcos";

        $this->Courses();
        return 'end';
        //echo $count;
    }

    public function Courses(){//popular a tabela de metrics courses
        $list = Courses::get();
        $courses = $list->groupBy('tenant_id');
        $count = 0;


        foreach($courses as $tenant){
            foreach($tenant as $course){

                $search = ClassModuleSubscripton::where('course_id', $course->id)->get();

                foreach($search as $register){

                    $time_plus = "00:00:00";
                    $time = "00:00:00";
                    $time_total = $this->courseTimeTotal($course->id);
                    $time_consumed = $this->courseTimeConsumed($course->id);
                    $percent_finished = 0;

                    $count = UserSubscription::where('package_id', $register->package_id)->count();
                    $qtd_finished = CoursesHistories::where('course_id', $course->id)->where('finished', 1)->count();

                    $metric = new MetricCourses();
                    $metric->course_id = $course->id;
                    $metric->name_course = $course->title;
                    $metric->users_access = $count;
                    $metric->package_id = $register->package_id;
                    $metric->time_total = $time_total;
                    $metric->tenant_id = $course->tenant_id;
                    

                    $metric->time_consumed = $time_consumed;

                    for ($i = 0; $i < $count; $i++) {

                        $time_plus = $this->plus_time( $time_total, $time_plus );
                        $time = $time_plus;
                    }

                    if($time === "00:00:00" || $time_consumed === "00:00:00"){
                        $metric->percent_users_watched = 0;
                    }
                    else{
                        $metric->percent_users_watched = $this->percentWatched($time, $time_consumed);
                    }
                    if($qtd_finished === 0)
                    {
                        $percent_finished = 0;                      
                    }else{
                        if($count === 0){
                            $percent_finished = 0;
                        }
                        else{
                            $percent_finished = $qtd_finished/$count;
                            $percent_finished = $percent_finished * 100;
                        }
                    }
                    $metric->users_finished = $qtd_finished;
                    $metric->users_finished_percented = $percent_finished;
                    $metric->save();
                    $time = "00:00:00";
                }
            }
        }

    }

    public function courseTimeTotal($id){
        $course = Courses::where('id', $id)->with('modules.classes')->first();
        $ponto = ':';
        $hora_um = "00:00:00";

        foreach($course->modules as $module){
            foreach($module->classes as $class){
                $format = strpos( $class->time_total, $ponto );
                if($class->time_total != null)
                {
                    if(!$format){
                        $class->time_total = gmdate('H:i:s', $class->time_total);
                        $class->save();
                    }
                }
                else{
                    $class->time_total = "00:00:00";
                }

                $hora_dois = $class->time_total;
                $hora_um = $this->plus_time( $hora_um, $hora_dois );
                
            }
        }

        return $hora_um;

    }

    public function courseTimeConsumed($id){
        $course = Courses::where('id', $id)->with('modules.classes')->first();
        $hora_um = "00:00:00";

        foreach($course->modules as $module){
            foreach($module->classes as $class){

                $hora_dois = $this->classConsumed($class->id);

                $hora_um = $this->plus_time( $hora_um, $hora_dois );
                
            }
        }

        return $hora_um;

    }

    public function classConsumed($id){
        $classes = ClassesHistories::where('class_id', $id)->get();
        $ponto = ':';
        $hora_um = "00:00:00";

        foreach($classes as $class){
            $format = strpos( $class->time, $ponto );
            if($class->time != null)
            {
                if(!$format){
                    $class->time = gmdate('H:i:s', $class->time);
                    $class->save();
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

    public function percentWatched($time_total, $consumed_hours){

        $time_plus = "00:00:00";

        $time = $consumed_hours;

        $total = $time_total;
        $explodeHoraTotal = explode(":",$total); //retorna um array onde cada elemento é separado por ":"
        $minutosTotal = $explodeHoraTotal[1];
        $minutosTotal = $minutosTotal*60;

        $horasTotal = $explodeHoraTotal[0];
        $horasTotal = ($horasTotal*3600);

        $total =$horasTotal+$minutosTotal+$explodeHoraTotal[2];

        $quebraHora = explode(":",$time); //retorna um array onde cada elemento é separado por ":"
        $minutos = $quebraHora[1];
        $minutos = $minutos*60;

        $horas = $quebraHora[0];
        $horas = ($horas*3600);
        $tot =$horas+$minutos+$quebraHora[2];

        $percentual = round(($tot / $total) * 100);
        return $percentual;

            
    }
}