<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class MetricClasses extends Model
{

    protected $connection = "plataform_mysql";
    protected $table = 'metric_classes';

    protected $guarded = []; // YOLO

    protected $fillable = [
        "id",
        "class_id",
        "module_id",
        "course_id",
        "users_access",
        "package_id",
        "tenant_id",
        "time_total",
        "time_consumed",
        "percent_users_watched",
        "users_finished",
        "name_module",
        "users_finished_percented"
    ];
}