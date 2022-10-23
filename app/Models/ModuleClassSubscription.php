<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TheMembers\Courses;

class ModuleClassSubscription extends Model
{
    protected $connection = "plataform_mysql";
    protected $table = 'ead_class_module_subscription';
    protected $keyType ='string';

    protected $fillable = [
        "id",
        "tenant_id",
        "package_id",
        "course_id",
        "module_id",
        "class_id",
        "blocked",
        "release_date",
        "wait_days",
    ];

    public function course()
    {
        return $this->hasOne(Courses::class,'id','course_id');
    }

    public function coursesLinkeds() {
        return $this->hasMany(CoursesLinked::class,'ead_class_module_subscription_id','id');
    }

}