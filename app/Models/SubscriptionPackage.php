<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PlanCheckout;

class SubscriptionPackage extends Model
{

    protected $connection = "plataform_mysql";
    protected $table = 'ead_subscription_package';
    protected $keyType = 'string';

    protected $fillable = [
        "id",
        "tenant_id",
        "title",
        "description",
        "value",
        "period",
        "trial",
        "product_id",
        "salesInSite",
        "typePaymentPlan",
        "sugestionPurchaseIdPlan",
        "plan_iugu_id",
        "status",
        "solicitation_date",
        "interval",
        "interval_type"
    ];

    public function moduleSubscription()
    {
        return $this->hasMany(ModuleClassSubscription::class, 'package_id', 'id');
    }

    public function checkouts()
    {
        return $this->hasMany(PlanCheckout::class, 'package_id');
    }
}