<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class MetricCourses extends Model
{

    protected $connection = "plataform_mysql";
    protected $table = 'metric_courses';

    protected $guarded = []; // YOLO

    protected $fillable = [
        "id",
        "course_id",
        "users_access",
        "package_id",
        "tenant_id",
        "time_total",
        "time_consumed",
        "percent_users_watched",
        "users_finished",
        "name_course",
        "users_finished_percented"
    ];
}