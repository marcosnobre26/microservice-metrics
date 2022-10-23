<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSubscription;

class ClassModuleSubscripton extends Model
{

    protected $connection = "plataform_mysql";
    protected $table = 'ead_class_module_subscription';

    protected $fillable = [
        "id",
        "tenant_id",
        "module_id",
        "class_id",
        "user_id",
        "course_id",
        "package_id",
        "blocked",
        "release_date",
        "wait_days"
    ];

    public function count_package(){

        return UserSubscription::where('package_id', 'package_id')->count();
        //return $this->hasMany(UserSubscription::class, 'id','course_id')->count();
    }
}