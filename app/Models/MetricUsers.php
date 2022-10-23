<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class MetricUsers extends Model
{

    protected $connection = "plataform_mysql";
    protected $table = 'metric_users';

    protected $guarded = []; // YOLO

    protected $fillable = [
        "id",
        "user_id",
        "course_id",
        "time_consumed",
        "package_id",
        "finished",
        "tenant_id",
        "percent_watched",
    ];
}