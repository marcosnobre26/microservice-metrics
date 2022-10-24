<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TenantUsers;

class CoursesHistories extends Model
{
    use HasFactory;
    protected $connection = 'plataform_mysql';
    protected $table = 'plataforma.ead_courses_histories';
    protected $fillable = [
        'tenant_id',
        'course_id',
        'user_id',
        'finished'
    ];

    public function tenant()
    {
        return $this->belongsTo(TenantUsers::class, 'tenant_id');
    }
    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}