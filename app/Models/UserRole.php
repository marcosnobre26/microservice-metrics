<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $connection = "plataform_mysql";
    protected $table = 'users_roles';
    protected $keyType = 'string';

    protected $fillable = [
        "user_id",
        "role_id",
        "tenant_id",
    ];
}