<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TenantUsers;

class ModulesHistories extends Model
{
    use HasFactory;
    protected $connection = "plataform_mysql";
    protected $table = 'ead_modules_histories';

    protected $fillable = [
        'tenant_id',
        'module_id',
        'user_id',
        'finished'
    ];

    public function tenant()
    {
        return $this->belongsTo(TenantUsers::class, 'tenant_id');
    }
    public function module()
    {
        return $this->belongsTo(Modules::class, 'module_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}