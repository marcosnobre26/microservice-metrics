<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class Courses extends Model
{

    protected $connection = "plataform_mysql";
    protected $table = 'ead_courses';

    protected $guarded = []; // YOLO

    protected $fillable = [
        "id",
        "tenant_id",
        "title",
        "status",
        "desc",
        "slug",
        "blocked",
        "published",
        "release_date",
        "wait_days",
        "url_upsell",
        "text_upsell",
        "url_cover_vertical",
        "url_cover_horizontal",
        "certificado_progress",
        "certificado_publich",
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

    public function modules(){
        return $this->hasMany(Modules::class, 'course_id','id')->orderBy('order', 'ASC');
    }

    public function package(){
        return $this->belongsToMany(Package::class, 'ead_class_module_subscription', 'course_id' ,'package_id');
    }


    public function getVerifyPackageAttribute($value)
    {
        $userPackage = Auth()->user()->load('package')->package->pluck('id');
        $coursePackage = $this->package->pluck('id');

        if(Auth()->user()->auth == 0){
            return 1;
        }

        foreach($userPackage as $item){
            if($coursePackage->contains($item)){
                return 1;
            }
        }

        return 0;
    }

    public function moduleSubscription()
    {
        return $this->hasMany( Subscription\ModuleClassSubscription::class, 'course_id');
    }

    public function certificate() {
        return $this->hasOne(Certificate::class, 'course_id');
    }
    public function certificateIssued() {
        return $this->hasOne(CertificateUser::class,'course_id', 'id',Auth::user()->id,'user_id');
    }

}