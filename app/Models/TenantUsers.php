<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use TheMembers\Subscription\AppSubscriptionStorage;

class TenantUsers extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;


    protected $connection = "plataform_mysql";
    protected $table = "tenant_users";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        "id",
        "name",
        "description",
        "address",
    ];

    public function producer()
    {
        return $this->hasOne(User::class, 'tenant_id')->where('auth', '=', 0);
    }

    public function signature()
    {
        return $this->hasOne(AppSubscription::class, 'tenant_id')->join('app_plans', 'app_subscriptions.plan_id', 'app_plans.id');
    }


    public function TenantDomain()
    {
        return $this->hasMany(TenantDomain::class, 'tenant_id');
    }

    public function subscription()
    {
        return $this->hasOne(AppSubscription::class, 'tenant_id','id');
    }
    public function subscriptionStorage()
    {
        return $this->hasOne(AppSubscriptionStorage::class, 'tenant_id','id');
    }


}