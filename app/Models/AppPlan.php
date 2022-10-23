<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppPlan extends Model
{



    protected $fillable = [
        "id",
        "name",
        "description",
        "period",
        "price",
        "status",
        "trialDays",
        'identifier',
        'plan_iugu_id',
        'free',
        'parent_id',
        'storage_limit',
        'storage',
        'publish'

    ];

    protected $connection = "plataform_mysql";
    protected $table = 'app_plans';
    protected $keyType ='string';

    public function subscriptions()
    {
        return $this->hasMany(AppSubscription::class, 'plan_id', 'id');
    }

}