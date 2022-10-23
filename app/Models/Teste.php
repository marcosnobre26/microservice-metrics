<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TenantUsers;

class Teste extends Model
{
    protected $connection = "mysql";
    protected $table = 'teste';

    protected $guarded = []; // YOLO

    protected $fillable = [
        "name",
    ];
}