<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanCheckout extends Model
{

protected $connection = "plataform_mysql";
protected $table = 'plans_checkouts';

    protected $fillable = [
        "tenant_id",
        "package_id",
        "name",
        "order_bump",
        "img_checkout_right",
        "img_checkout_top",
        "img_checkout_background",
        "background_color_checkout"
        
    ];

    public function package()
    {
        return $this->belongsTo(AppPlan::class);
    }

}