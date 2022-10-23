<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{

    protected $connection = "plataform_mysql";
    protected $table = 'ead_subscription_package';
    protected $keyType = 'string';

    protected $fillable = [
        "id",
        "tenant_id",
        "title",
        "description",
        "period",
        "value",
        "trial",
        "product_id",
        "salesInSite",
        "typePaymentPlan",
        "plan_iugu_id",
        "form_of_payment",
        "qtd_parcelas",
        "pix",
        "lifetime",
        "boleto",
        "reimbursement",
        "limit_cobrancas",
        "status",
        "solicitation_date",
        "interval_type",
        "interval"
    ];


    public function courses()
    {
        return $this->belongsToMany(Courses::class, 'ead_class_module_subscription', 'course_id', 'package_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription\UserSubscription::class, 'package_id');
    }

    // public function registrationLinks()
    // {
    //     return $this->belongsToMany(RegistrationLink::class, env('DB_DATABASE').'.ead_package_registration_link',  'registration_link_id', 'package_id',);
    // }
}