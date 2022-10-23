<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TheMembers\Subscription\ModuleClassSubscription;
use Illuminate\Support\Str;

class Modules extends Model
{

    protected $connection = "plataform_mysql";
    protected $table = 'ead_modules';

    protected $guarded = []; // YOLO

    protected $fillable = [
        "id",
        "course_id",
        "tenant_id",
        "title",
        "subtitle",
        "description",
        "url_video",
        "url_cover_vertical",
        "url_cover_retangulo",
        "slug",
        "blocked",
        "published",
        "release_date",
        "wait_days",
        "url_image_circle",
        "url_image_square",
        "url_image_triangle",
        "url_image_free",
        "view_mode"
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            $post->{$post->getKeyName()} = (string) Str::uuid();
        });
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }



    public function package()
    {
        return $this->hasMany(ModuleClassSubscription::class, 'module_id');
    }

    public function classes(){
        return $this->hasMany(Classes::class, 'module_id')->orderBy('order', 'ASC');
    }

    public function course(){
        return $this->hasOne(Courses::class, 'id','course_id');
    }


    public function getVerifyPackageAttribute($value)
    {
        $coursesLinkeds = Auth()->user()->load('coursesLinkeds')->coursesLinkeds->pluck('package_id')->toArray();
        $courseDoModulo = $this->course->package->pluck('id');

        if(Auth()->user()->auth == 0){
            return 1;
        }

        foreach($coursesLinkeds as $item){
            if($courseDoModulo->contains($item)){
                return 1;
            }
        }

        return 0;
    }

    public function classesHistories() {
        return $this->hasMany(ClassesHistories::class, 'module_id');
    }
}