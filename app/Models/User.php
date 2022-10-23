<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
//use TheMembers\Subscription\CoursesLinked;
use App\Models\UserSubscription;
use App\Models\TenantUsers;
use App\Models\UserTraits;
use Tymon\JWTAuth\Contracts\JWTSubject;

use Illuminate\Database\Eloquent\SoftDeletes;
//use TheMembers\Permissions\HasPermissionsTrait;



class User extends Authenticatable implements JWTSubject
{
    use SoftDeletes;
    use UserTraits;
    use Notifiable;
    //use HasPermissionsTrait; //Import The Trait

    protected $connection = "plataform_mysql";
    protected $table = 'users';
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $fillable = [
        "id",
        "name",
        "tenant_id",
        "email",
        "last_name",
        "document",
        "person_type",
        "cpf_cnpj",
        "resp_cpf",
        "company_name",
        "correntista",
        "business_type",
        "bank",
        "bank_ag",
        "bank_cc",
        "account_type",
        "genre",
        "birth",
        "phone",
        "auth",
        "email_verified_at",
        "password",
        "photo_url",
        "remember_token",
        "storage_limit",
        "storage_use",
        "registration_link_id",
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function ticket()
    {
        return $this->hasMany(Tickets::class, 'user_id');
    }
    public function comments()
    {
        return $this->hasMany(Commentary::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'user_id');
    }

    public function address()
    {
        return $this->hasOne(UserAddress::class, 'user_id');
    }

    public function blocked()
    {
        return $this->hasOne(UsersBlacklist::class, 'user_id');
    }

    public function userRank()
    {
        return $this->hasOne(RankingPoints::class, 'user_id');
    }

    public function ticket_items()
    {
        return $this->hasMany(TicketItems::class, 'user_id');
    }

    public function userBankaccount()
    {
        return $this->hasOne(UserBankaccount::class, 'user_id');
    }


    public function package()
    {
        return $this->hasMany(Package::class, 'user_subscription', 'user_id', 'package_id');
    }


    public function tenant()
    {
        return $this->hasOne(TenantUsers::class, 'id', 'tenant_id');
    }

    public function getPictureAttributes($value)
    {
        if ($value) {
            return asset('uploads/users/images/' . $value);
        } else {
            return asset('uploads/users/images/no-image.png');
        }
    }

    //public function coursesLinkeds()
    //{
    //    return $this->hasMany(CoursesLinked::class, 'user_id');
    //}

    public function roles()
    {
        return $this->hasMany(UserRole::class);
    }

}