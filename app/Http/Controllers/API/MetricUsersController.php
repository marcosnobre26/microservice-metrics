<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\ClassesHistories;
use App\Models\CoursesHistories;
use App\Models\Classes;
use App\Models\MetricModules;
use App\Models\MetricCourses;
use App\Models\MetricClasses;
use App\Models\MetricUsers;
use App\Models\ModuleClassSubscription;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

use Illuminate\Routing\Controller as BaseController;

class MetricUsersController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index()
    {
        return MetricUsers::get();
    }

    public function create(Request $request, $id)
    {
        
        $history = CoursesHistories::where('id', $id)->first();
        $count = MetricCourses::where('user_id', $history->user_id)->where('course_id', $history->course_id)->count();
        if($count === 0){

            $array_packages = [];
            
            $course = Courses::where('id', $history->course_id)->first();
            $packages = ModuleClassSubscription::where('course_id', $course->course_id)->get();
            $users_access = 0;
            $package_id = '';

            foreach($packages as $package){
                $count = UserSubscription::where('package_id', $package->package_id)->count();
                $users_access = $users_access + $count;

                //$pack = UserSubscription::where('package_id', $package->package_id)->where('user_id', $history->user_id)->count();
                
                //if($pack > 0)
                //{
                //    array_push($array_packages, $package);
               // }
                
            }
            
            $course = Courses::where('course_id', $history->course_id)->first();
            $count = UserSubscription::where('package_id', $register->package_id)->count();
            if($course->time_total === null){
                $course->time_total = "00:00:00";
            }
            $time_consumed = $this->plus_time($course->time_total, $history->time);
            $percented_watch = $this->percentWatched($course->time_total, $history->time);
            $users_finished = CoursesHistories::where('course_id', $history->class_id)->where('finished', 1)->first();
            $qtd_finished = $users_finished;
            $percent_finished = 0;
            //foreach($array_packages as $item){

                $metric_course = new MetricCourses();

                $metric_course->course_id = $course->course_id;
                $metric_course->users_access = $users_access;
                $metric_course->package_id = $item->package_id;
                $metric_course->tenant_id = $history->tenant_id;
                $metric_course->time_total = $course->time_total;
                $metric_course->time_consumed = $time_consumed;
                $metric_course->percent_users_watched = $percented_watch;
                $metric_course->users_finished = $users_finished;
                $metric_course->$metric_module->name_course = $course->title;

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
                $metric_course->users_finished_percented = $percent_finished;
                $metric_course->save();
           // }
        }
        else{
            $metric_course = MetricCourses::where('user_id', $history->user_id)->where('course_id', $history->course_id)->first();
            $count = UserSubscription::where('package_id', $register->package_id)->count();
            $time_save = $this->plus_time($metric_course->time_consumed, $history->time_consumed);

            $metric_course->time_consumed = $time_save;

            if($history->finished === 1){
                $users_finished = CoursesHistories::where('course_id', $history->class_id)->where('finished', 1)->first();
                $qtd_finished = $users_finished;
                $percent_finished = 0;
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
                $metric_course->users_finished_percented = $percent_finished;
            }
            $metric_course->save();
        }
        
        return response()->json('Sucesso', 200);
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