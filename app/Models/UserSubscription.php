<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Package;
use App\Models\User;

class UserSubscription extends Model
{
    protected $connection = "plataform_mysql";
    protected $table = 'user_subscription';
    protected $keyType ='string';

    protected $fillable = [
        "id",
        "tenant_id",
        "active",
        "user_id",
        "iugu_id",
        "package_id",
        "contract_id",
        "credit_brand",
        "last_digits",
        "contract_invoice",
        "payment_last_date",
        "expiration_date",
        "iugu_invoice_id",
        "iugu_payment_method_id",
        "iugu_qtd_installments",
        "iugu_invoice_status",
        'iugu_assinatura_status',
        'iugu_assinatura_id',
        "type_payment",
    ];

    public function moduleSubscription()
    {
        return $this->hasMany(ModuleClassSubscription::class, 'package_id','package_id');
    }
    public function subscriptionPackage(){
        return $this->belongsTo(SubscriptionPackage::class, 'package_id','id');
    }
    public function packageSubscription()
    {
        return $this->hasMany(SubscriptionPackage::class, 'id','package_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}