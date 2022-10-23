<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use App\Models\AppPlan;

class AppSubscriptionStorage extends Model
{
    use HasFactory;

    protected $fillable = [
        "tenant_id",
        "plan_id",
        "card_id",
        "price",
        "storage_limit",
        'next_due',
        'customer_iugu_id',
        'subscription_iugu_id',
        'identifier_app_subscriptions_storage',
        'payable_with',
        'status_pay_iugu',
        'status',

    ];

    // MUTATOR

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $value != null ? moneyBrasilAmerica($value) : null;
    }

    // RELACIONAMENTO
    public function plan()
    {
        return $this->hasOne(AppPlan::class, 'id', 'plan_id');
    }

    public function tenant()
    {
        return $this->hasOne(TenantUsers::class, 'id', 'tenant_id');
    }

    public function producer()
    {
        return $this->setConnection('mysql_slave')->hasOne(User::class, 'tenant_id')->where('auth', '=', 0);
    }

    public function card()
    {
        return $this->hasOne(AppCreditCard::class, 'id', 'card_id');
    }

    // auto uuid
    protected static function booted()
    {
        static::creating(fn (AppSubscriptionStorage $appSubscriptionStorage) => $appSubscriptionStorage->id = (string) Uuid::uuid4());
    }
}