<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class GenCuenta extends Model
{
    protected $connection = 'legacy';
    protected $table = 'gen_cuenta';
    protected $primaryKey = 'cc_cuenta';
    public $timestamps = false;

    protected $guarded = [];
}
