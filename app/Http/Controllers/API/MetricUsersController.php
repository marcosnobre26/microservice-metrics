<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\ClassesHistories;
use App\Models\ModulesHistories;
use App\Models\CoursesHistories;
use App\Models\Classes;
use App\Models\Courses;
use App\Models\Modules;
use App\Models\MetricModules;
use App\Models\MetricCourses;
use App\Models\MetricClasses;
use App\Models\MetricUsers;
use App\Models\Package;
use App\Models\User;
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

    public function update($id, $user_id, $course_id, $tenant_id, $time_total)
    {
        $time_consumed = "00:00:00";
        $time_total = "00:00:00";
        $finished = '';
        $course = Courses::with('modules.classes')->where('id', $course_id)->first();
        $user = User::where('id', $user_id)->first();
        $packages = ModuleClassSubscription::where('course_id', $course->course_id)->get();
        

        foreach($packages as $package){

            $count_user = UserSubscription::where('package_id', $package->package_id)
            ->where('user_id',$user->id)
            ->count();

            $course_metric = MetricCourses::where('course_id', $course->id)
                ->where('tenant_id', $tenant_id)
                ->where('package_id', $package->package_id)
                ->first();
            $time_total = $course_metric->time_total;

            if($count_user > 0){

                $count = MetricUsers::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->where('tenant_id', $tenant_id)
                ->where('package_id', $package->package_id)
                ->count();

                foreach($course->modules as $module){
                    foreach($module->classes as $class){
                        $histories = ClassesHistories::where('class_id', $class->id)
                        ->where('finished', 1)
                        ->where('user_id', $user->id)
                        ->get();

                        foreach($histories as $history){
                            $time_consumed = $this->plus_time($time_consumed, $history->time);
                        }
                    }
                }

                $course_history = CoursesHistories::where('course_id',$course->id)
                ->where('user_id',$user->id)
                ->first();

                if($course_history->finished === 1)
                {
                    $finished = "Sim";
                }
                else{
                    $finished = "Não";
                }

    
                if($count > 0){
                    $metric_user = MetricUsers::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->where('tenant_id', $tenant_id)
                    ->where('package_id', $package->package_id)
                    ->first();
                }
                else{
                    $metric_user = new MetricUsers();
                }
    
                $metric_user->course_id = $course->id;
                $metric_user->user_id = $history->user_id;
                $metric_user->package_id = $package->package_id;
                $metric_user->name_user = $user->name;
                $metric_user->tenant_id = $tenant_id;
                $metric_user->time_consumed = $time_consumed;
                $metric_user->finished = $finished;
                $metric_user->percent_watched = $this->percentWatched($time_total, $time_consumed);;
                $metric_user->save();
            }
            
        }
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

    public function create(Request $request, $id)
    {
        $subscriptions = UserSubscription::where('user_id', $id)->get();

        foreach($subscriptions as $subscription)
        {
            $courses = ModuleClassSubscription::where('package_id', $subscription->package_id)->get();
            foreach($courses as $course){
                $metric_user = new MetricCourses();
                $metric_user->user_id = $id;
                $metric_user->course_id = $course->course_id;
                $metric_user->time_consumed = "00:00:00";
                $metric_user->package_id = $subscription->package_id;
                $metric_user->finished = "Não";
                $metric_user->tenant_id = $course->tenant_id;
                $metric_user->percent_watched = 0;    
                $metric_user->save();
            }
            
        }
    }
}
