<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ClassesHistories extends Model
{
    protected $connection = "plataform_mysql";
    protected $table = 'ead_classes_histories';
    protected $keyType ='string';
    protected $fillable = [
        "id",
        "tenant_id",
        "module_id",
        "class_id",
        "user_id",
        "time",
        "to_total",
        "time_total",
        "finished",
        "rate_up"
    ];

    public function continueView(User $user)
    {
        if (!$user) {
            return [];
        }

        $lession = ClassesHistories::with('lession','module','myList')
            ->where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->where('time', '!=' , null)
            ->orderBy('time', 'DESC')
            ->orderBy('created_at')
            ->get();

            $lession = $lession->map(function ($object) {

                // Add the new property
                $object = $object->lession;

                // Return the new object
                return $object;

            });

        return $lession;
    }

    public function highlights($tenant)
    {

        $lession = ClassesHistories::select('class_id', DB::raw('AVG(ead_classes_histories.to_total) as media'))
            ->where('tenant_id', $tenant->id)
            ->with('lession','module')
            ->groupBy('class_id')
            ->orderBy('media', 'desc')
            ->limit(10)
            ->get();

            $lession = $lession->map(function ($object) {

                // Add the new property
                $object = $object->lession;

                // Return the new object
                return $object;

            });

        return $lession;

    }

    public function continueViewModules($user)
    {
        $modules = [];
        $continueWatches = ClassesHistories::where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->with('lession','module.course')
            ->where('finished', '!=', 1)
            ->orderBy('created_at', 'desc')
            ->get();

            $continueWatches = $continueWatches->map(function ($object) {

                // Add the new property
                $object = $object->module;

                // Return the new object
                return $object;

            });

        return $continueWatches;

    }

    public function lession()
    {
        return $this->hasOne(Classes::class, 'id', 'class_id');
    }

    public function module()
    {
        return $this->hasOne(Modules::class, 'id', 'module_id');
    }

    public function class()
    {
        return $this->hasOne(Classes::class, 'id', 'class_id');
    }

    public function myList()
    {
        return $this->hasOne(MyList::class, 'class_id', 'class_id');
    }
}