<?php


namespace TheMembers\Traits;
use Ramsey\Uuid\Uuid;
use App\Models\User;


trait UserTraits
{
    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Uuid::uuid4();
        });
    }

    public function initializeHasUuid()
    {
        $this->incrementing = false;
        $this->keyType = 'string';
    }
}