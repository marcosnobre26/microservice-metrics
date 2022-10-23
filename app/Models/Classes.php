<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class Classes extends Model
{

    protected $connection = "plataform_mysql";
    protected $table = 'ead_classes';

    protected $guarded = []; // YOLO

    protected $fillable = [
        "id",
        "module_id",
        "title",
        "subtitle",
        "description",
        "status",
        "blocked",
        "published",
        "release_date",
        "wait_days",
        "url_video",
        "url_video_s3",
        "url_image",
        "url_banner",
        "url_thumb",
        "url_ebook",
        "host",
        "slug",
        "url_image_circle",
        "url_image_square",
        "url_image_triangle",
        "url_image_free",
        "time_total",
        "template_type"
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

    public function comments()
    {
        return $this->hasMany(Commentary::class, 'class_id')->orderBy('created_at');
    }

    public function histories()
    {
        return $this->belongsTo(ClassesHistories::class, 'id', 'class_id');
    }

    public function module()
    {
        return $this->belongsTo(Modules::class, 'module_id', 'id');
    }

    public function material()
    {
        return $this->hasMany(Materials::class, 'class_id', 'id');
    }


    public function getVerifyPackageAttribute($value)
    {
        $userPackage = Auth()->user()->load('package')->package->pluck('id');
        $coursePackage = $this->module->course->package->pluck('id');

        if (Auth()->user()->auth == 0) {
            return 1;
        }

        foreach ($userPackage as $item) {
            if ($coursePackage->contains($item)) {
                return 1;
            }
        }

        return 0;
    }

    public function userHistorie()
    {
        return $this->hasOne(ClassesHistories::class, 'class_id', 'id', Auth::user()->id, 'user_id');
    }
    public function userList()
    {
        return $this->hasOne(MyList::class, 'class_id', 'id', Auth::user()->id, 'user_id');
    }

    // public function myList()
    // {

    //     $lession = MyList::
    //         where('user_id', Auth::user()->id)
    //         ->with('lession','module')
    //         ->get();

    //     return $lession;
    // }





}