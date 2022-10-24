<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TenantUsers;

class Role extends Model
{
    use HasFactory;
    protected $connection = "plataform_mysql";

    public function permissions() {

        return $this->belongsToMany(Permission::class,'roles_permissions');
            
     }
     
     public function users() {
     
        return $this->belongsToMany(User::class,'users_roles');
            
     }

     public function tenants() {
     
      return $this->belongsToMany(TenantUsers::class,'users_roles');
          
   }
}