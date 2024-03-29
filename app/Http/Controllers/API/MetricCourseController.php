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
use App\Models\Package;
use App\Models\MetricUsers;
use App\Models\User;
use App\Models\UserRole;
use App\Models\TenantUsers;
use App\Models\ModuleClassSubscription;
use App\Models\ClassModuleSubscripton;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

use Illuminate\Routing\Controller as BaseController;

class MetricCourseController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index(Request $request)
    {
        $courses = Courses::where('tenant_id', $request->tenant_id)->get();
        
        foreach($courses as $course){
            $search = MetricCourses::where('course_id', $course->id)->count();

            if($search === 0)
            {
                $time = $this->metricInexist($course, $request->tenant_id);
                $course->time_total = $time;
            }
            else{
                $metric = MetricCourses::where('course_id', $course->id)->first();
                $course->time_total = $metric->time_total;
            }
            
        }
        //return MetricCourses::get();
        return $courses;
    }

    public function update(Request $request, $id)
    {
        $history = CoursesHistories::where('id',$id)->first();

        if($history->finished === 1){
            $course = Courses::where('id', $history->course_id)->first();

            $packages = ModuleClassSubscription::where('course_id', $course->id)->get();
            foreach($packages as $package){
                $count = UserSubscription::where('package_id', $package->package_id)->where('user_id',$history->user_id)->count();
                $history->time = "00:00:00";
                if($count > 0){
                    $this->update_course( $history->time, $package->package_id, $course, $history->tenant_id, $history->user_id);
                }
            }
        
            return response()->json('Sucesso', 200);
        }
        else{
            return response()->json(['data' => 'Curso ainda não concluido.', 'Sucesso' => 200]);
        }
        
    }

    function update_course( $time, $package_id, $course, $tenant_id, $user_id) {
        $users_access = 0;
        $ponto = ':';
        $packages = ModuleClassSubscription::where('course_id', $course->id)->get();

        foreach($packages as $package){
            $count = UserSubscription::where('package_id', $package->package_id)->count();
            $users_access = $users_access + $count;
        }
        
        $time_course_total = "00:00:00";
        $tempo_total = "00:00:00";
        $count = MetricCourses::where('course_id', $course->id)
        ->where('package_id', $package_id)
        ->where('tenant_id', $tenant_id)
        ->count();



        $course = Courses::with('modules.classes')->where('id', $course->id)->first();
        $users_finished = CoursesHistories::where('course_id', $course->id)->where('finished', 1)->count();
        foreach($course->modules as $module){
            foreach($module->classes as $class){
                $format = strpos( $class->time_total, $ponto );
                if($class->time_total === null){
                    $class->time_total = "00:00:00";
                }

                if(!$format){
                    $class->time_total = gmdate('H:i:s', $class->time_total);
                }

                $time_course_total = $this->plus_time($time_course_total, $class->time_total);
            }
        }
        
        if($count > 0){
            $metric_course = MetricCourses::where('course_id', $course->id)
            ->where('package_id', $package_id)
            ->where('tenant_id', $tenant_id)
            ->first();

            $metric_course->time_total = $time_course_total;
            $tempo_total="00:00:00";
            for($i = 0; $i < $users_finished; ++$i) {
                $tempo_total = $this->plus_time($tempo_total, $time_course_total);
            }

            $time_consumed = $this->plus_time($time_course_total, $time);
            $metric_course->time_consumed = $time_consumed;
            $metric_course->users_access = $users_access;
            $metric_course->package_id = $package_id;
            $metric_course->users_finished = $users_finished;
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
            
            $metric_course->users_finished_percented = $percent_finished;

            $metric_course->percent_users_watched = $this->percentWatched($tempo_total, $time_consumed);
            $metric_course->save();
            $this->update_user($user_id, $course->id, $tenant_id, $metric_course->time_total);
        }
        else{
            $metric_course = new MetricCourses();
            $metric_course->course_id = $course->id;
            $metric_course->time_total = $time_course_total;
            $tempo_total="00:00:00";
            
            for($i = 0; $i < $users_finished; ++$i) {
                $tempo_total = $this->plus_time($tempo_total, $time_course_total);
            }

            $time_consumed = $this->plus_time($time_course_total, $time);
            $metric_course->time_consumed = $time_consumed;
            $metric_course->name_course = $course->title;
            $metric_course->package_id = $package_id;
            $metric_course->tenant_id = $tenant_id;
            $metric_course->users_access = $users_access;
            $metric_course->users_finished = $users_finished;
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
            
            $metric_course->users_finished_percented = $percent_finished;
            $metric_course->percent_users_watched = $this->percentWatched($tempo_total, $time_consumed);
            $metric_course->save();
            $this->update_user($user_id, $course->id, $tenant_id, $metric_course->time_total);
        }
    }

    public function metricInexist($course, $tenant_id){
        //ddd($tenant_id);
        try{
        $users_access = 0;
        $ponto = ':';
        $time = "00:00:00";
        $time_course_total = "00:00:00";
        $course = Courses::with('modules.classes')->where('id', $course->id)->first();
        
        $count_packages = ModuleClassSubscription::where('course_id', $course->id)->count();
        if($count_packages === 0)
        {
            $metric_course = new MetricCourses();
            $metric_course->course_id = $course->id;
            $metric_course->time_total = "00:00:00";
            $metric_course->time_consumed = "00:00:00";
            $metric_course->name_course = $course->title;
            //$metric_course->package_id = $package->package_id;
            $metric_course->tenant_id = $course->tenant_id;
            $metric_course->users_access = 0;
            $metric_course->users_finished = 0;
            $metric_course->users_finished_percented = 0;
            $metric_course->percent_users_watched = 0;
            $metric_course->save();
            return "00:00:00";
        }
        $packages = ModuleClassSubscription::where('course_id', $course->id)->get();
        $users_finished = CoursesHistories::where('course_id', $course->id)->where('finished', 1)->count();
        $c=0;
        foreach($course->modules as $module){
            foreach($module->classes as $class){
                $format = strpos( $class->time_total, $ponto );
                if($class->time_total === null){
                    $class->time_total = "00:00:00";
                }

                if(!$format && $class->time_total != "00:00:00"){
                    if($c === 1)
                    {
                        //dd($class);
                        $c = $c + 1;
                    }
                    $class->time_total = gmdate('H:i:s', $class->time_total);
                    
                    //throw new Exception($class);
                }

                $time_course_total = $this->plus_time($time_course_total, $class->time_total);
            }
        }
        $metric_course = '';
        foreach($packages as $package){

            
                $count = UserSubscription::where('package_id', $package->package_id)->count();
                $users_access = $users_access + $count;

                
                $tempo_total = "00:00:00";
                $count = MetricCourses::where('course_id', $course->id)
                ->where('package_id', $package->package_id)
                ->where('tenant_id', $tenant_id)
                ->count();
      
                $metric_course = new MetricCourses();
                $metric_course->course_id = $course->id;
                $metric_course->time_total = $time_course_total;
                $tempo_total="00:00:00";
            
                for($i = 0; $i < $users_finished; ++$i) {
                    $tempo_total = $this->plus_time($tempo_total, $time_course_total);
                }

                $time_consumed = $this->plus_time($time_course_total, $time);
                $metric_course->time_consumed = $time_consumed;
                $metric_course->name_course = $course->title;
                $metric_course->package_id = $package->package_id;
                $metric_course->tenant_id = $tenant_id;
                $metric_course->users_access = $users_access;
                $metric_course->users_finished = $users_finished;
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
                
                $metric_course->users_finished_percented = $percent_finished;
                $metric_course->percent_users_watched = $this->percentWatched($tempo_total, $time_consumed);
                $metric_course->save();
                $time_course_total = "00:00:00";
                $tempo_total="00:00:00";
            
        }
        return $metric_course;
        }
        catch (Exception $e) {
            echo 'Exceção capturada: ',  $e->getMessage(), "\n";
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
        if($tot != 0 && $total != 0)
        {
            $percentual = round(($tot / $total) * 100);
        }else{
            $percentual = 0;
        }
        
        return $percentual;

            
    }

    public function searchAlunos(Request $search, $order){
        $cursos_de_aluno = MetricUsers::join('users', 'metric_users.user_id', '=', 'users.id')
        ->join('ead_courses', 'metric_users.course_id', '=', 'ead_courses.id')
        ->where(function ($query) use ($search, $order) {
            $query->where('users.name', 'like', '%' . $search . '%');
        })
        ->orderBy('ead_courses.title', $order)
        ->get();

        return $cursos_de_aluno;
    }

    public function searchCursos(Request $request, $search, $order, $course_id){
        $metrics = MetricUsers::join('ead_courses', 'metric_users.course_id', '=', 'ead_courses.id')
        ->where(function ($query) use ($search, $order) {
            $query->where('ead_courses.title', 'like', '%' . $search . '%');
        })
        ->orderBy('ead_courses.title', $order)
        ->get();
    }

    public function searchAlunosDoCurso(Request $request, $order, $course_id){

        
        $metrics = MetricUsers::join('users', 'metric_users.course_id', '=', 'ead_courses.id')
        ->where('course_id', $course_id)
        ->orderBy('ead_courses.title', $order)
        ->get();
    }

    public function searchTimeConsumed($order, $course_id, $perPage){
        $metrics = MetricCourses::orderBy('time_consumed', $order)
        ->where('course_id', $course_id)
        ->paginate($perPage);

        return $metrics;
    }

    public function searchUsersFinished($order){
        $metrics = MetricUsers::orderBy('users_finished_percented', $order)->get();

        return $metrics;
    }

    public function planCourses($plan, $order, $perPage){
        $courses = MetricCourses::where('package_id', $plan)->orderBy('name_course', $order)->paginate($perPage);

        return [
            "Cursos deste plano.",
            "data",
            $courses
        ];
    }

    public function searchName($search,$package_id, $perPage, $order){
        $courses = MetricCourses::where('name_course', 'like', '%' . $search . '%')
        ->where('package_id', $package_id)
        ->orderBy('name_course', $order)
        ->paginate($perPage);

        return [
            "Cursos deste plano.",
            "data",
            $courses
        ];
    }

    public function planCoursesFilteredName($plan, $perPage){
        $courses = MetricCourses::where('package_id', $plan)->orderBy('name_course', $request->order)->paginate($perPage);

        return [
            "Cursos deste plano.",
            "data",
            $courses
        ];
    }

    public function create($id){

        $courses_list = MetricCourses::where('course_id', $id)->get();
        foreach($courses_list as $item){
            $item->delete();
        }

        $arr = [];
        $course = Courses::where('id', $id)->first();
        $packages = ModuleClassSubscription::where('course_id', $id)->get();
        $users_finished = CoursesHistories::where('course_id', $id)->where('finished', 1)->first();
        
        foreach($packages as $package){
            
            $count = UserSubscription::where('package_id', $package->package_id)->count();
            $subscriptions = ClassModuleSubscripton::where('package_id', $package->package_id)->get();

            $metric_course = new MetricCourses();

            $metric_course->course_id = $course->id;
            $metric_course->users_access = $count;
            $metric_course->package_id = $package->package_id;
            $metric_course->tenant_id = $course->tenant_id;
            $metric_course->time_total = "00:00:00";
            $metric_course->time_consumed = "00:00:00";
            $metric_course->percent_users_watched = 0;
            $metric_course->users_finished = 0;
            $metric_course->name_course = $course->title;
            $metric_course->users_finished_percented = 0;
            $metric_course->save();

            foreach($subscriptions as $item){
                $this->createUsers($item->user_id);
            }

            
            //dd($metric_course);

            //array_push($arr, $metric_course);
        }

        /*return [
            "Metrica de Curso cadastrada.",
            "data",
            $arr
        ];*/
    }

    public function plans(Request $request){
        $packages = Package::where('tenant_id', $request->tenant_id)->get();

        return [
            "Pacotes.",
            "data",
            $packages
        ];
    }

    //public function createUsers($id, $user_id, $package_id)
    public function createUsers($subscription, $course, $time_total)
    {
        if($subscription->user_id === "d8fbd6d7-4ee6-453b-b812-8a4ba1122f5f"){
            //dd($time_total);
        }
        //$packages = ModuleClassSubscription::where('course_id', $id)->get();
        //$subscriptions = UserSubscription::where('user_id', $id)->get();
        //foreach($packages as $package)
        //{
            //$count = 0;
            //$subscriptions = UserSubscription::where('package_id', $package->package_id)->get();

            
            //foreach($subscriptions as $subscription)
            //{
                //if($count < 20)
                //{
                    //$count_user = User::where('id',$user_id)->count();

                    //if($count_user > 0){
                        //$user = User::where('id',$user_id)->first();
                        //dd($user);
                        //$courses = ModuleClassSubscription::where('package_id', $package_id)->get();
                        /*if($user->name != null)
                        {
                            $user_name = $user->name;
                        }*/
                        $finished = "Sim";
                        //if($subscription->user_id === "b55ddba2-6fba-49cb-a6db-4b191043badf"){
                            //dd($course);
                        //}
                        
                        $metrics = MetricUsers::where('user_id',$subscription->user_id)->where('course_id',$subscription->course_id)->get();
                        foreach($metrics as $metric){
                            $metric->delete();
                        }
                        $concluded = CoursesHistories::where('course_id', $subscription->course_id)->where('user_id',$subscription->user_id)->count();
                        
                        if($concluded === 0){
                            $finished = "Não";
                            
                        }
                        else{
                            $finish = CoursesHistories::where('course_id', $subscription->course_id)->where('user_id',$subscription->user_id)->first();
                            if($finish->finished === 1){
                                $finished = "Sim";
                            }else{
                                $finished = "Não";
                            }
                        }
                        
                        
                        //foreach($courses as $course){
                            $time_consumed = $this->courseTimeConsumed($subscription->course_id, $subscription->user_id, $course);
                            $metric_user = new MetricUsers();
                            $metric_user->user_id = $subscription->user_id;
                            $metric_user->course_id = $subscription->course_id;
                            //$metric_user->time_consumed = "00:00:00";
                            $metric_user->time_consumed = $time_consumed;
                            $metric_user->package_id = $subscription->package_id;
                            $metric_user->finished = $finished;
                            $metric_user->tenant_id = $subscription->tenant_id;
                            //$metric_user->percent_watched = 0;
                            $metric_user->percent_watched = $this->percentWatched($time_total, $time_consumed);
                            $metric_user->name_user = $subscription->name;
                            $metric_user->document = $subscription->document;
                            $metric_user->email = $subscription->email;
                            $metric_user->save();

                            return $metric_user;
                        //}
                    //}
                //} 
            //}
        //}
    }

    public function courseTimeConsumed($id, $user_id, $course){
        
        //$course = Courses::where('id', $id)->with('modules.classes')->first();
        //dd($user_id);
        $hora_um = "00:00:00";
        $count = 0;

        /*if($user_id === "d8fbd6d7-4ee6-453b-b812-8a4ba1122f5f"){
            dd($course->modules);
        }*/
        
        foreach($course->modules as $module){
            foreach($module->classes as $class){
                $hora_dois = $this->classConsumed($class->id, $user_id);
                

                $hora_um = $this->plus_time( $hora_um, $hora_dois );

                /*if($count === 1){
                    if($user_id === "d8fbd6d7-4ee6-453b-b812-8a4ba1122f5f"){
                        dd($hora_um);
                    }
                }
                $count = $count +1;*/
                
            }
        }

        return $hora_um;

    }

    public function courseTimeTotal($course){
        
        //$course = Courses::where('id', $id)->with('modules.classes')->first();
        //dd($user_id);
        $hora_um = "00:00:00";
        
        foreach($course->modules as $module){
            foreach($module->classes as $class){
                //$hora_dois = $this->classConsumed($class->id, $user_id);
                if($class->time === null){
                    $class->time = "00:00:00";
                }
                $hora_um = $this->plus_time( $hora_um, $class->time );
                
            }
        }

        

        return $hora_um;

    }

    public function classConsumed($id, $user_id){
        
        $classes = ClassesHistories::where('class_id', $id)->where('user_id', $user_id)->get();
        //if($user_id === "d8fbd6d7-4ee6-453b-b812-8a4ba1122f5f"){
            //dd($id);
        //}
        
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


    public function studentsToCourses(Request $request, $id_course,$perPage){

        //$arr = [];
        //$course = Courses::where('id', $id_course)->with('modules.classes')->first();
        
        //$time_total = $this->courseTimeTotal($course);
        if($request->order === 'name-asc'){

            /*$users = User::leftJoin('user_subscription', 'user_subscription.user_id', '=', 'users.id')
            ->join('ead_class_module_subscription', 'ead_class_module_subscription.package_id', '=', 'user_subscription.package_id')
            ->where('ead_class_module_subscription.course_id','=',$id_course)
            ->orderBy('name', 'asc')
            ->paginate($perPage);

            foreach($users as $user)
            {
                $item = $this->createUsers($user, $course, $time_total);
                array_push($arr, $item);
            }*/

            $metrics = MetricUsers::where('course_id', $id_course)
            ->orderBy('name_user', 'asc')
            ->paginate($perPage);

            //$metrics = $arr;
        }

        if($request->order === 'name-desc'){

            /*$users = User::leftJoin('user_subscription', 'user_subscription.user_id', '=', 'users.id')
            ->join('ead_class_module_subscription', 'ead_class_module_subscription.package_id', '=', 'user_subscription.package_id')
            ->where('ead_class_module_subscription.course_id','=',$id_course)
            ->orderBy('name', 'desc')
            ->paginate($perPage);

            foreach($users as $user)
            {
                $item = $this->createUsers($user, $course, $time_total);
                array_push($arr, $item);
            }*/

            $metrics = MetricUsers::where('course_id', $id_course)
            ->orderBy('name_user', 'desc')
            ->paginate($perPage);

            //$metrics = $arr;
        }

        if($request->order === 'percent-asc'){
            
            $metrics = MetricUsers::where('course_id', $id_course)
            ->orderBy('percent_watched', 'asc')
            ->paginate($perPage);
        }

        if($request->order === 'percent-desc'){
            $metrics = MetricUsers::where('course_id', $id_course)
            ->orderBy('percent_watched', 'desc')
            ->paginate($perPage);
        }
        

        return [
            "Alunos.",
            "data",
            $metrics
        ];
    }

    public function studentsFilterName(Request $request, $search, $id_course, $perPage){

        if($request->order === 'name-asc'){
            $metrics = MetricUsers::where('course_id', $id_course)
            ->where('name_user', 'like', '%' . $search . '%')
            ->orderBy('name_user', 'asc')
            ->paginate($perPage);
        }

        if($request->order === 'name-desc'){
            $metrics = MetricUsers::where('course_id', $id_course)
            ->where('name_user', 'like', '%' . $search . '%')
            ->orderBy('name_user', 'desc')
            ->paginate($perPage);
        }

        if($request->order === 'percent-asc'){
            $metrics = MetricUsers::where('course_id', $id_course)
            ->where('name_user', 'like', '%' . $search . '%')
            ->orderBy('percent_watched', 'asc')
            ->paginate($perPage);
        }

        if($request->order === 'percent-desc'){
            $metrics = MetricUsers::where('course_id', $id_course)
            ->where('name_user', 'like', '%' . $search . '%')
            ->orderBy('percent_watched', 'desc')
            ->paginate($perPage);
        }

        foreach($metrics as $metric){
            $user = User::where('id', $metric->user_id)->first();
            $metric->email = $user->email;
        }

        return [
            "Alunos.",
            "data",
            $metrics
        ];
    }

    public function studentsFilterCPF(Request $request, $search, $id_course, $perPage){

        $metric_users = MetricUsers::where('course_id', $id_course)->get();

        foreach($metric_users as $metric){
            if($metric->document === null)
            {
                $user = User::where('id', $metric->user_id)->first();
                $metric->document = $user->document;
                
            }

            if($metric->email === null)
            {
                $user = User::where('id', $metric->user_id)->first();
                $metric->email = $user->email;
            }
            $metric->save();
        }

        if($request->order === 'name-asc'){
            $metrics = MetricUsers::where('course_id', $id_course)
            ->where('document', 'like', '%' . $search . '%')
            ->orderBy('name_user', 'asc')
            ->paginate($perPage);
        }

        if($request->order === 'name-desc'){

            $metrics = MetricUsers::where('course_id', $id_course)
            ->where('document', 'like', '%' . $search . '%')
            ->orderBy('name_user', 'desc')
            ->paginate($perPage);
        }

        if($request->order === 'percent-asc'){

            $metrics = MetricUsers::where('course_id', $id_course)
            ->where('document', 'like', '%' . $search . '%')
            ->orderBy('percent_watched', 'asc')
            ->paginate($perPage);
        }

        if($request->order === 'percent-desc'){
            $metrics = MetricUsers::where('course_id', $id_course)
            ->where('document', 'like', '%' . $search . '%')
            ->orderBy('percent_watched', 'desc')
            ->paginate($perPage);
        }

        foreach($metrics as $metric){
            $user = User::where('id', $metric->user_id)->first();
            $metric->email = $user->email;
        }

        return [
            "Alunos.",
            "data",
            $metrics
        ];
    }

    public function studentsFilterEmail(Request $request, $search, $id_course, $perPage){

        $metric_users = MetricUsers::where('course_id', $id_course)->get();
        

        foreach($metric_users as $metric){
            if($metric->document === null)
            {
                $user = User::where('id', $metric->user_id)->first();
                $metric->document = $user->document;
                
            }

            if($metric->email === null)
            {
                $user = User::where('id', $metric->user_id)->first();
                $metric->email = $user->email;
            }
            $metric->save();
        }

        if($request->order === 'name-asc'){
            $metrics = MetricUsers::where('course_id', $id_course)
            ->where('email', 'like', '%' . $search . '%')
            ->orderBy('name_user', 'asc')
            ->paginate($perPage);
        }

        if($request->order === 'name-desc'){

            $metrics = MetricUsers::where('course_id', $id_course)
            ->where('email', 'like', '%' . $search . '%')
            ->orderBy('name_user', 'desc')
            ->paginate($perPage);
        }

        if($request->order === 'percent-asc'){

            $metrics = MetricUsers::where('course_id', $id_course)
            ->where('email', 'like', '%' . $search . '%')
            ->orderBy('percent_watched', 'asc')
            ->paginate($perPage);
        }

        if($request->order === 'percent-desc'){
            $metrics = MetricUsers::where('course_id', $id_course)
            ->where('email', 'like', '%' . $search . '%')
            ->orderBy('percent_watched', 'desc')
            ->paginate($perPage);
        }

        foreach($metrics as $metric){
            $user = User::where('id', $metric->user_id)->first();
            $metric->email = $user->email;
        }

        return [
            "Alunos.",
            "data",
            $metrics
        ];
    }

    public function update_user($user_id, $course_id, $tenant_id, $time_total)
    {
        $time_consumed = "00:00:00";
        $time_total = "00:00:00";
        $finished = '';
        $ponto = ':';
        $course = Courses::with('modules.classes')->where('id', $course_id)->first();
        $user = User::where('id', $user_id)->first();
        $packages = ModuleClassSubscription::where('course_id', $course->id)->get();

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
                        ->where('user_id', $user->id)
                        ->get();

                        $format = strpos( $class->time_total, $ponto );
                        if($class->time_total === null){
                            $class->time_total = "00:00:00";
                        }

                        if(!$format){
                            $class->time_total = gmdate('H:i:s', $class->time_total);
                        }
                        
                        foreach($histories as $history){
                            
                            $format = strpos( $history->time, $ponto );
                            if($history->time === null){
                                $history->time = "00:00:00";
                            }

                            if(!$format){
                                $history->time = gmdate('H:i:s', $history->time);
                            }
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

    public function tenants(Request $request, $id)
    {
        $arr = [];
        $tenant = [];
        //$users = UserRole::get();
        //dd($users);
        //$user = User::where('email', 'kratos@bomdeguerra.com')->first();
        $user = User::where('id', $id)->where('auth',0)->first();
        //$users = UserRole::where('user_id', $id)->where('role_id',2)->get();
        //dd($users);
        //foreach($users as $user)
        //{
            //$tenant = TenantUsers::find($user->tenant_id);
           // array_push($arr, $tenant);
        //}
        if($user)
        {
            $tenant = TenantUsers::where('id',$user->tenant_id)->get();
        }
        
        //$tenant = TenantUsers::where('id',127)->get();
        //dd($user->tenant_id);
        return $tenant;
        //return $arr;
    }
}
