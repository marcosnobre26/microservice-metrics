<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\ClassesHistories;
use App\Models\ModulesHistories;
use App\Models\MetricModules;
use App\Models\Classes;
use App\Models\Modules;
use App\Models\MetricCourses;
use App\Models\MetricClasses;
use App\Models\ModuleClassSubscription;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

use Illuminate\Routing\Controller as BaseController;

class MetricClassesController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index()
    {
        return MetricClasses::get();
    }

    public function update(Request $request, $id)
    {

        $history = ClassesHistories::where('id', $id)->first();
        if($history->finished === 1){
            $count = MetricClasses::where('class_id', $history->class_id)->count();
            $array_class = [];
            $ponto = ':';

            if($count === 0){
            
                $array_packages = [];
                $array_package_id = [];
                
                
                $class = Classes::with('module.course')->where('id', $history->class_id)->first();
                $packages = ModuleClassSubscription::where('course_id', $class->module->course->id)->get();
                $users_access = 0;
                $package_id = '';
                //dd($packages);
                foreach($packages as $package){
                    $count = UserSubscription::where('package_id', $package->package_id)->where('user_id',$history->user_id)->count();
                    
                    if($count > 0){
                        
                        $users_access = UserSubscription::where('package_id', $package->package_id)->count();
                        array_push($array_package_id , $package->package_id);
                    
                    
                    
                    
                        //$pack = UserSubscription::where('package_id', $package->package_id)->where('user_id', $history->user_id)->count();
                        
                        //if($pack > 0)
                        //{
                        //    array_push($array_packages, $package);
                    // }
                    
                
                        //$class = Classes::with('module.course')->where('id', $history->class_id)->first();
                        
        
                        if($class->time_total === null){
                            $class->time_total = "00:00:00";
                        }

                        $format = strpos( $class->time_total, $ponto );
                        if($class->time_total === null){
                            $class->time_total = "00:00:00";
                        }
                        //$time_consumed = $this->plus_time($class->time_total, $history->time);
                        $time_consumed = $history->time;
                        $percented_watch = $this->percentWatched($class->time_total, $history->time);
                        $users_finished = ClassesHistories::where('class_id', $history->class_id)->where('finished', 1)->count();
                        $qtd_finished = $users_finished;
                        $percent_finished = 0;
                        //dd($time_consumed);
                        $metric_class = new MetricClasses();
        
                        $metric_class->class_id = $class->id;
                        $metric_class->name_aula = $class->title;
                        $metric_class->module_id = $class->module->id;
                        $metric_class->course_id = $class->module->course->id;
                        $metric_class->users_access = $users_access;
                        $metric_class->package_id = $package->package_id;
                        $metric_class->tenant_id = $history->tenant_id;
                        $metric_class->time_total = $class->time_total;
                        $metric_class->time_consumed = $time_consumed;
                        $metric_class->percent_users_watched = $percented_watch;
                        $metric_class->users_finished = $users_finished;
                        $metric_class->name_module = $class->module->title;
    
                        if($users_finished === 0)
                        {
                            $percent_finished = 0;                      
                        }else{
                            if($users_access === 0){
                                $percent_finished = 0;
                            }
                            else{
                                $percent_finished = $users_finished/$users_access;
                                $percent_finished = $percent_finished * 100;
                            }
                        }
                        $metric_class->users_finished_percented = $percent_finished;
                        $metric_class->save();
                    
                        array_push($array_class, $metric_class);
        
                        //$this->update_module($class->module->id, $time_consumed, $package->package_id, $class->module->course->id, $class->module, $count, $history->tenant_id);
                        $this->update_module($class->module, $time_consumed, $users_access, $package->package_id, $class->module->course, $history->tenant_id);
                    }
                
                }
                return response()->json(['data' => $array_class, 'Sucesso' => 200]);
               
            }
            else{
                $array_packages = [];
                $metric_class = MetricClasses::where('class_id', $history->class_id)->first();
                
                $class = Classes::with('module.course')->where('id', $history->class_id)->first();
                $packages = ModuleClassSubscription::where('course_id', $class->module->course->id)->get();
                
                foreach($packages as $package){
                    $count = UserSubscription::where('package_id', $package->package_id)->count();
                    

                    $users_access = UserSubscription::where('package_id', $package->package_id)->count();
                    array_push($array_package_id , $package->package_id);

                    if($class->time_total === null){
                        $class->time_total = "00:00:00";
                    }

                    $format = strpos( $class->time_total, $ponto );
                    if($class->time_total === null){
                        $class->time_total = "00:00:00";
                    }

                    $time_consumed = $history->time;
                    $percented_watch = $this->percentWatched($class->time_total, $history->time);
                    $users_finished = ClassesHistories::where('class_id', $history->class_id)->where('finished', 1)->count();
                    $qtd_finished = $users_finished;
                    $percent_finished = 0;

                    $metric_class = MetricClasses::where('package_id', $package->package_id)->where('class_id', $class->id)->first();
        
                    $metric_class->class_id = $class->id;
                        $metric_class->name_aula = $class->title;
                        $metric_class->module_id = $class->module->id;
                        $metric_class->course_id = $class->module->course->id;
                        $metric_class->users_access = $users_access;
                        $metric_class->package_id = $package->package_id;
                        $metric_class->tenant_id = $history->tenant_id;
                        $metric_class->time_total = $class->time_total;
                        $metric_class->time_consumed = $time_consumed;
                        $metric_class->percent_users_watched = $percented_watch;
                        $metric_class->users_finished = $users_finished;
                        $metric_class->name_module = $class->module->title;
    
                        if($users_finished === 0)
                        {
                            $percent_finished = 0;                      
                        }else{
                            if($users_access === 0){
                                $percent_finished = 0;
                            }
                            else{
                                $percent_finished = $users_finished/$users_access;
                                $percent_finished = $percent_finished * 100;
                            }
                        }
                        $metric_class->users_finished_percented = $percent_finished;
                        $metric_class->save();
                    
                        array_push($array_class, $metric_class);
        
                        //$this->update_module($class->module->id, $time_consumed, $package->package_id, $class->module->course->id, $class->module, $count, $history->tenant_id);
                        $this->update_module($class->module, $time_consumed, $users_access, $package->package_id, $class->module->course, $history->tenant_id);
                }
            }
            
            return response()->json(['data' => $array_class, 'Sucesso' => 200]);
        }
        else{
            return response()->json(['data' => 'Aula ainda não concluida.', 'Sucesso' => 200]);
        }
        
        
        
    }

    function update_module($module, $time, $users_access, $package_id, $course, $tenant_id) {
        //$this->update_module($class->module
        //$time_consumed
        //$users_access
        //$package->package_id
        //$class->module->course->id
        //$history->tenant_id);

        $percent_finished = 0;
        $count = MetricModules::where('module_id', $module->id)
        ->where('course_id', $course->id)
        ->where('package_id', $package_id)
        ->where('tenant_id', $tenant_id)
        ->count();
        $time_module_total = "00:00:00";
        $ponto = ':';
        $module = Modules::with('classes')->where('id', $module->id)->first();
        
        foreach($module->classes as $class){
            $format = strpos( $class->time_total, $ponto );
            if($class->time_total === null){
                $class->time_total = "00:00:00";
            }

            if(!$format){
                $class->time_total = gmdate('H:i:s', $class->time_total);
            }

            $time_module_total = $this->plus_time($time_module_total, $class->time_total);
        }

        //$users_finished = MetricModules::where('module_id', $history->class_id)->where('finished', 1)->count();
        $users_finished = ModulesHistories::where('module_id', $module->id)->where('finished', 1)->count();
        if($count > 0){
            
            $metric_module = MetricModules::where('module_id', $id_module)
            ->where('course_id', $course->id)
            ->where('package_id', $package_id)
            ->where('tenant_id', $tenant_id)
            ->first();

            $time_consumed = $this->plus_time($metric_module->time_consumed, $time);
            $metric_module->time_consumed = $time_consumed;
            $metric_module->time_total = $time_module_total;
            $metric_module->users_finished = $users_finished;
            $metric_module->users_access = $users_access;
            if($users_finished === 0)
            {
                $percent_finished = 0;                      
            }else{
                if($users_access === 0){
                    $percent_finished = 0;
                }
                else{
                    $percent_finished = $users_finished/$users_access;
                    $percent_finished = $percent_finished * 100;
                }
            }
            $metric_module->users_finished_percented = $percent_finished;
            $metric_module->percent_users_watched = $this->percentWatched($metric_module->time_total, $time_consumed);
            $metric_module->save();

            //$this->update_course($time, $package_id, $course->id, $module->course, $count, $tenant_id);
        }
        else{
            $total_time = "00:00:00";
            $metric_module = new MetricModules();
            $metric_module->module_id = $module->id;
            $metric_module->name_module = $module->title;
            $metric_module->users_access = $users_access;
            $metric_module->course_id = $course->id;
            $metric_module->package_id = $package_id;
            
            $time_consumed = $this->plus_time($total_time, $time);
            $metric_module->time_total = $time_module_total;
            $metric_module->time_consumed = $time_consumed;
            $metric_module->users_finished = $users_finished;
            $metric_module->tenant_id = $tenant_id;
            
            if($users_finished === 0)
            {
                $percent_finished = 0;                      
            }else{
                if($users_access === 0){
                    $percent_finished = 0;
                }
                else{
                    $percent_finished = $users_finished/$users_access;
                    $percent_finished = $percent_finished * 100;
                }
            }
            
            $metric_module->users_finished_percented = $percent_finished;
            $metric_module->percent_users_watched = $this->percentWatched($metric_module->time_total, $time_consumed);
            $metric_module->save();

            //$this->update_course($time, $package_id, $course->id, $module->course, $count, $tenant_id);
        }

        //return response()->json('Sucesso', 200);
    }

    function update_course( $time, $package_id, $course_id, $course, $number_users, $tenant_id ) {
        
        $count = MetricCourses::where('course_id', )->where('package_id', $package_id)->count();

        if($count > 0){
            $metric_course = MetricCourses::where('module_id', $id_module)->where('course_id', )->where('package_id', $package_id)->count();
            $time_consumed = $this->plus_time($metric_course->time_total, $time);
            $metric_course->time_consumed = $time_consumed;
            $metric_course->percent_users_watched = $this->percentWatched($metric_course->time_total, $time_consumed);
            $metric_course->save();
        }
        else{
            $metric_course = new MetricCourses();
            $metric_course->course_id = $course_id;
            $metric_course->users_access = $number_users;
            $metric_course->package_id = $package_id;
            $metric_course->tenant_id = $tenant_id;
            $metric_course->time_total = $time;
            $metric_course->time_consumed = $module->$time;
            $metric_course->percent_users_watched = 0;
            $metric_course->users_finished = 0;
            $metric_course->name_course = $module->course->title;
            $metric_course->users_finished_percented = 0;
            $metric_course->save();
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
        $arr = [];
        $class = Classes::with('module.course')->where('id', $id)->first();

        $packages = ModuleClassSubscription::where('course_id', $class->module->course->course_id)->get();
        $percent_finished = 0;

        foreach($packages as $package){

            $count = UserSubscription::where('package_id', $package->package_id)->count();

            $metric_class = new MetricClasses();
            $metric_class->class_id = $class->class_id;
            $metric_class->module_id = $class->module->id;
            $metric_class->course_id = $class->module->course->course_id;
            $metric_class->users_access = $count;
            $metric_class->package_id = $package->package_id;
            $metric_class->tenant_id = $class->module->course->tenant_id;
            $metric_class->time_total = "00:00:00";
            $metric_class->time_consumed = "00:00:00";
            $metric_class->percent_users_watched = 0;
            $metric_class->users_finished = 0;
            $metric_class->name_module = $class->module->title;
            $metric_class->users_finished_percented = 0;
            $metric_class->save();

            array_push($arr, $metric_class);
        }

        return [
            "Metrica de Aula cadastrada.",
            "data",
            $arr
        ];
    }
}
